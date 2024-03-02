<?php

namespace App\Http\Controllers\api;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Utils;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\Message\MessageCollection;
use App\Http\Requests\Message\CreateMessageRequest;
use App\Models\EmotionRegulationMechanism;
use App\Models\Iteration;
use App\Models\Emotion;
use App\Models\Classification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $messages = Message::where("client_id", Auth::user()->userable->id)
        ->select('*')
        ->orderBy('created_at', $order)
        ->get();
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
        $clientMessage = new Message();
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();

            // Save the client' message
            $clientMessage->isChatbot = $validated_data["isChatbot"];
            $clientMessage->body = $validated_data["body"];
            $clientMessage->client()->associate(Auth::user()->userable);
            $clientMessage->save();
            //-------------- Send to Rasa --------------
            $client = new Client();
            $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
            ];
            
            $body = json_encode([
                "sender" => Auth::user()->email,
                "message" => $validated_data["body"]
            ],
            JSON_UNESCAPED_UNICODE);
            $request = new GuzzleRequest('POST', 'http://chatbot:5005/webhooks/rest/webhook', $headers, Utils::streamFor($body));
            $response = $client->send($request);
            $responseArray = json_decode($response->getBody()->getContents(), true, 512, JSON_UNESCAPED_UNICODE);
            
            $finalMessages = [];
            array_push($finalMessages,$clientMessage);
            $ermIsPossible = true;
            // Rasa return a custom json: "custom": { "ERM": "false" }, if ERM cannot be done
            // If property not present then its okay to do ERM
            foreach ($responseArray as $responseChatbot) {
                if(array_key_exists("custom", $responseChatbot) && 
                   array_key_exists("ERM", $responseChatbot["custom"]) && 
                   $responseChatbot["custom"]["ERM"] == "false"){ 
                    $ermIsPossible = false;
                }
            }
            // Save chatbot messages and handle ERM
            foreach ($responseArray as $responseChatbot) {
                if(array_key_exists("text", $responseChatbot)){
                    $msg = new Message();
                    $msg->isChatbot = true;
                    $msg->body = $responseChatbot["text"];
                    $msg->client()->associate(Auth::user()->userable);
                    $msg->save();
                    array_push($finalMessages,$msg);
                }else if($ermIsPossible == true && array_key_exists("emotion", $responseChatbot["custom"])){ 
                    // If we have a prediction with emotions
                    $emotion = $responseChatbot["custom"]["emotion"];
                    $erm = $this->fetchERM($emotion);
                    $finalMessages = $this->calculateRM($erm, $finalMessages);

                    // handle iteration
                    // Handle iteration
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
                    $clientMessage->save();
                    // Creates the content (parent) of the client message
                    $clientMessage->content()->create([
                        'emotion_name' => $iteration->emotion_name,
                        'accuracy' => $responseChatbot["custom"]["accuracy"],
                        'createdate' => Carbon::now(),
                        'iteration_id' => $iteration->id
                    ]);
                    // handles the client message SA predictions
                    $predictions = explode(";",$responseChatbot["custom"]["predictions"]);
                    foreach ($predictions as &$prediction) {

                        $classification = new Classification();
                        $aux = explode("#", $prediction, 2);
                        $classification->emotion()->associate(Emotion::find(strtolower($aux[0])));
                        $classification->accuracy = $aux[1];
                        $classification->content()->associate($clientMessage->content->id);
                        $classification->save();
                    }
                }
            }
            DB::commit();
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

    public function fetchERM($emotion){
        $mechanism = EmotionRegulationMechanism::where("client_id", Auth::user()->userable->id)
        ->where("emotion", $emotion)
        ->first();
        if($mechanism == null){
            $mechanism = EmotionRegulationMechanism::where("is_default", '1')
            ->where("emotion", $emotion)
            ->first();
        }
        return $mechanism->regulation_mechanism;
    }

    public function calculateRM($mechanism, $messages){
        switch ($mechanism) {
            case "joke":
                $path = storage_path('app/regulation_mechanisms/jokes.json');
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $contents = file_get_contents($path);
                // If you want to decode the JSON
                $jsonData = json_decode($contents, true);
                $randomKey = array_rand($jsonData);
                // Access the random entry using the random key
                $rm = $jsonData[$randomKey];
                $msgJoke = new Message();
                $msgJoke->isChatbot = true;
                $msgJoke->body = $rm['joke'];
                $msgJoke->client()->associate(Auth::user()->userable);
                $msgJoke->save();
                $msgAnswer = new Message();
                $msgAnswer->isChatbot = true;
                $msgAnswer->body = $rm['answer'];
                $msgAnswer->client()->associate(Auth::user()->userable);
                $msgAnswer->save();
                array_push($messages, $msgJoke);
                array_push($messages, $msgAnswer);
                break;
        }
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
