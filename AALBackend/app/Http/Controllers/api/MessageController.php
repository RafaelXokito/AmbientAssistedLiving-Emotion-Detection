<?php

namespace App\Http\Controllers\api;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Utils;
use App\Models\Message;
use App\Models\Client as ClientM;
use App\Models\ResponseQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\Message\MessageCollection;
use App\Http\Resources\EmotionNotification\EmotionNotificationCollection;
use App\Http\Requests\Message\CreateMessageRequest;
use App\Models\EmotionRegulationMechanism;
use App\Models\Iteration;
use App\Models\Emotion;
use App\Models\Classification;
use App\Models\GeriatricQuestionnaire;
use App\Models\OxfordHappinessQuestionnaire;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Enums\RegulationMechanismContentTypes;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return MessageCollection
     */
    public function index()
    {       
        $order = request()->query('order') == "desc" ? "desc" : "asc";
        $limit = request()->query('limit') ? (int) request()->query('limit') : 0;
        if($limit > 0){
            $messages = Message::where("client_id", Auth::user()->userable->id)
            ->select('*')
            ->where('body', 'NOT LIKE', 'start_geriatric_form')
            ->where('body', 'NOT LIKE', 'start_oxford_happiness_form')
            ->orderBy('created_at', $order)
            ->take($limit)
            ->get();
        } else {
            $messages = Message::where("client_id", Auth::user()->userable->id)
            ->select('*')
            ->where('body', 'NOT LIKE', 'start_geriatric_form')
            ->where('body', 'NOT LIKE', 'start_oxford_happiness_form')
            ->orderBy('created_at', $order)
            ->get();
        }
        return new MessageCollection($messages);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return IterationResource|\Illuminate\Http\JsonResponse
     */
    public function store(CreateMessageRequest $request)
    {
        $clientMessage = null;
        $finalMessages = [];
        $validated_data = $request->validated();
        try {
            DB::beginTransaction();
            $clientInput = "";
            if(array_key_exists("custom", $validated_data)){
                $clientInput = $validated_data["custom"];
            }
            else if(array_key_exists("body", $validated_data)){
                // Save the client' message
                $clientInput = $validated_data["body"];
                $clientMessage = new Message();
                $clientMessage->isChatbot = $validated_data["isChatbot"];
                $clientMessage->body = $clientInput;
                $clientMessage->content_type = RegulationMechanismContentTypes::Text->value;
                $clientMessage->client()->associate(Auth::user()->userable);
                $clientMessage->save();
                array_push($finalMessages, $clientMessage);
            }
            
            //-------------- Send to Rasa --------------
            $responseArray = $this->sendToRasa($clientInput);
            
            $chatbotMessagesHasShortQuestion = false;
            $questionnaireType = "";
            $ermIsPossible = true;
            $emotion = null;
            $accuracy = 0;
            // Rasa return a custom json: "custom": { "ERM": "false" } or { "IS_QUESTION": "true", "ERM": "false" }, if ERM cannot be done
            // If property not present then its okay to do ERM
            foreach ($responseArray as $responseChatbot) {
                if(array_key_exists("text", $responseChatbot)){
                    $msg = new Message();
                    $msg->isChatbot = true;
                    $msg->body = $responseChatbot["text"];
                    $msg->content_type = RegulationMechanismContentTypes::Text->value;
                    $msg->client()->associate(Auth::user()->userable);
                    $msg->save();
                    array_push($finalMessages,$msg);
                }
                if(!array_key_exists("custom", $responseChatbot)){
                    continue;
                }
                $response = $responseChatbot["custom"];
                if(array_key_exists("ERM", $response) && 
                   $response["ERM"] == "false"){ 
                    $ermIsPossible = false;
                }
                if(array_key_exists("IS_SHORT_QUESTION", $response) && 
                   $response["IS_SHORT_QUESTION"] == "true"){ 
                    $chatbotMessagesHasShortQuestion = true;
                    $questionnaireType = $response["questionnaire"];
                }else{
                    if(array_key_exists("questionnaire", $response)){
                        $this->handleQuestionnaire($response);
                    }
                    if(array_key_exists("emotion", $response)){
                        $emotion = $response["emotion"];
                        $this->handleIteration($emotion, $clientMessage, $response);
                        $accuracy = $response["accuracy"];
                    }
                }                
            }

            if($ermIsPossible == true){
                // ERM
                $finalMessages = $this->calculateRM($emotion, $finalMessages, $accuracy);
            }
            DB::commit();
            // temporary volatile message to indicate that a short question is present
            if($chatbotMessagesHasShortQuestion == true){
                $tempMsg = new Message();
                $tempMsg->isChatbot = true;
                $msg->content_type = RegulationMechanismContentTypes::Text->value;
                $tempMsg->body = "#IS_SHORT_QUESTION#" . $questionnaireType;
                $tempMsg->client()->associate(Auth::user()->userable);
                array_push($finalMessages,$tempMsg);
            }         
            return new MessageCollection($finalMessages);
        } catch (\Throwable $th) {
            DB::rollBack();
            $messageTh = $th->getMessage();
            if (str_contains($messageTh, 'chatbot')) {
                $messageTh = "O agente conversacional MIMO está indisponível";
            }
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $messageTh
            ), 400);
        }
    }
    public function fetchQuestionnaire($type, $data, $isQuestion){
        $newQuestionnaire = false;
        $questionnaire = null;
        // Fetch questionnaire by type
        switch($type){
            case "GeriatricQuestionnaire":
                $questionnaire = GeriatricQuestionnaire::orderBy('created_at', 'desc')
                ->first();

                break;
            case "OxfordHappinessQuestionnaire": 
                $questionnaire = OxfordHappinessQuestionnaire::orderBy('created_at', 'desc')
                ->first();
                break;
        }

        // if questionnaire is null or has points then means its completed and a new one is created
        if($questionnaire == null || $questionnaire->questionnaire->points != NULL){
            $newQuestionnaire = true;
        }
        // if the questionnaire doesnt have points and the current chatbot response is a response to a question
        // then fetch the last answered question, if the question number is bigger than the question of the current response of the parameters
        // then a new questionnaire must be created
        else if ($questionnaire != null && $questionnaire->questionnaire->points == NULL && $isQuestion){
            $responseLastQuestion = ResponseQuestionnaire::where("questionnaire_id", "=", $questionnaire->questionnaire->id)
            ->orderBy('question', 'desc')
            ->first();
            if($responseLastQuestion->question > $data["question"]){
                $newQuestionnaire = true;
            }
        }
        // Creates new questionnaire
        if($newQuestionnaire){
            switch($type){
                case "GeriatricQuestionnaire":
                    $questionnaire = new GeriatricQuestionnaire();
                    break;
                case "OxfordHappinessQuestionnaire": 
                    $questionnaire = new OxfordHappinessQuestionnaire();
                    break;  
            }
            $questionnaire->save();
            $questionnaire->questionnaire()->create([
                'client_id' => Auth::user()->userable->id
            ]);
        }
        return $questionnaire;
    }

    public function handleQuestionnaire($data){
        $isQuestion = array_key_exists("question", $data);
        $questionnaire = $this->fetchQuestionnaire($data["questionnaire"], $data, $isQuestion);
        // Register's the response
        if($isQuestion){
            $response = new ResponseQuestionnaire();
            $response->is_why = $data["is_why"];
            $response->response = $data["response"];
            $response->question = $data["question"];
            $response->questionnaire()->associate($questionnaire->questionnaire->id);
            $response->save();
        }
        // Register's the points
        else if(array_key_exists("points", $data)){
            $questionnaire->questionnaire()->update([
                'points' => $data["points"]
            ]);
        }
        $questionnaire->save();
    }

    public function sendToRasa($clientInput){
        $client = new Client();
        $headers = [
        'Content-Type' => 'application/json; charset=utf-8',
        ];
    
        $emotionsSettings = ClientM::findOrFail(Auth::user()->userable_id)->emotionNotifications->toArray();

        $body = json_encode([
            "sender" => Auth::user()->userable_id,
            "message" => $clientInput,
            "metadata" => [
                "emotionsSettings" => $emotionsSettings
            ]
        ], JSON_UNESCAPED_UNICODE);

        $request = new GuzzleRequest('POST', 'http://chatbot:5005/webhooks/rest/webhook', $headers, Utils::streamFor($body));
        $response = $client->send($request);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_UNESCAPED_UNICODE); 
    }
    
    public function handleIteration($emotion, $clientMessage, $metadata){

        $iteration = Iteration::where('emotion_name', "=", $emotion)
                    ->orderBy('created_at', 'desc')
                    ->first();
        // If there's no iteration or if the creation date plus 30 minutes is greater than the current date and time 
        if($iteration == null || !Carbon::createFromTimestamp($iteration->created_at)->addMinutes(30)->gt(Carbon::now())){
            $iteration = new Iteration();
            $iteration->emotion()->associate(Emotion::findOrFail($emotion));
            $iteration->macaddress = "N/A";
            $iteration->usage_id = Str::uuid();
            $iteration->type = "best";
            $iteration->client()->associate(Auth::user()->userable);
            $iteration->save();
        }
        // Creates the content (parent) of the client message
        $clientMessage->content()->create([
            'emotion_name' => $iteration->emotion_name,
            'accuracy' => $metadata["accuracy"],
            'createdate' => Carbon::now(),
            'iteration_id' => $iteration->id
        ]);
        // handles the client message SA predictions
        $predictions = explode(";",$metadata["predictions"]);
        foreach ($predictions as &$prediction) {

            $classification = new Classification();
            $aux = explode("#", $prediction, 2);
            $classification->emotion()->associate(Emotion::find(strtolower($aux[0])));
            $classification->accuracy = $aux[1];
            $classification->content()->associate($clientMessage->content->id);
            $classification->save();
        }
    }

    public function calculateRM($emotion, $messages, $accuracy){
        
        $erms = EmotionRegulationMechanism::where("client_id", Auth::user()->userable->id)
        ->where("emotion", $emotion)
        ->where("threshold", "<=", $accuracy)
        ->whereHas('regulationMechanismsContents')
        ->get();
        
        if($erms->count() == 0){
            return $messages;
        }

        $erm = $erms->random();
        $content = $erm->regulationMechanismsContents->random();

        $msgAnswer = new Message();
        if($content->content_type->value == RegulationMechanismContentTypes::Text->value){
            $msgAnswer->body = $content->text;
        }else{
            $msgAnswer->body = $content->file_path;
        }
        $msgAnswer->content_type = $content->content_type->value;
        $msgAnswer->isChatbot = true;
        $msgAnswer->body = $content;
        $msgAnswer->client()->associate(Auth::user()->userable);
        $msgAnswer->save();
        array_push($messages, $msgJoke);
        return $messages;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(404);
    }
}
