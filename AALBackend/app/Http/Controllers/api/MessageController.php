<?php

namespace App\Http\Controllers\api;

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
use Illuminate\Support\Facades\Storage;

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
        $message = new Message();
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();

            $message->isChatbot = $validated_data["isChatbot"];
            $message->body = $validated_data["body"];
            $message->client()->associate(Auth::user()->userable);
            $message->save();
            DB::commit();
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
            array_push($finalMessages,$message);
            foreach ($responseArray as $responseChatbot) {
                $msg = new Message();
                $msg->isChatbot = true;
                $msg->body = $responseChatbot["text"];
                $msg->client()->associate(Auth::user()->userable);
                $decodedObject = json_decode($responseChatbot["text"]);
                if($decodedObject == null){
                    $msg->save();
                }else{
                    $erm = $this->fetchERM($decodedObject->emotion);
                    $ermResponse = $this->calculateRM($erm);
                    $msgERM = new Message();
                    $msgERM->isChatbot = true;
                    $msgERM->body = $ermResponse;
                    $msgERM->client()->associate(Auth::user()->userable);
                    array_push($finalMessages,$msgERM);
                }
                array_push($finalMessages,$msg);
            }

            return new MessageCollection($finalMessages);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
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

    public function calculateRM($mechanism){
        switch ($mechanism) {
            case "joke":
                $path = storage_path('app/regulation_mechanisms/jokes.json');
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $contents = file_get_contents($path);
                // If you want to decode the JSON
                $jsonData = json_decode($contents, true);
                $randomKey = array_rand($jsonData);
                // Access the random entry using the random key
                $response = json_encode($jsonData[$randomKey]);
                break;
        }
        return $response;
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
