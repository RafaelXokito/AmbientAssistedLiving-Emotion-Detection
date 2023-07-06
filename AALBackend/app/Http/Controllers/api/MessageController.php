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

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return MessageCollection
     */
    public function index()
    {
        $messages = Message::where("client_id", Auth::user()->userable->id)
        ->select('*')
        ->orderBy('created_at', 'desc')
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

            return new MessageResource($message);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
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
