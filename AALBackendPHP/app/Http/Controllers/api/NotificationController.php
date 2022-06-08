<?php

namespace App\Http\Controllers\api;

use App\Models\Emotion;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\Notification\NotificationResource;
use App\Http\Resources\Notification\NotificationCollection;
use App\Http\Requests\Notification\CreateNotificationRequest;
use App\Mail\NewNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new NotificationCollection(Notification::orderBy('created_at', 'DESC')->get());
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
     * @return \Illuminate\Http\Response
     */
    public function store(CreateNotificationRequest $createNotificationRequest)
    {
        $notification = new Notification();
        $validated_data = $createNotificationRequest->validated();

        try{
            DB::beginTransaction();
            $notification->emotion()->associate(Emotion::find($validated_data["emotion_name"]));
            $notification->client()->associate(Auth::user()->userable);
            $notification->title = "Emotion '".$validated_data["emotion_name"]."' was detected continuosly!";
            $notification->content = "The elder in your care has been showing ".$validated_data["emotion_name"]." emotions continuously.\n\nThe '".$validated_data["emotion_name"]."' values were higher than the defined limit of '".$validated_data["accuracy"]."' and these feelings have lasted over the specified duration of ".$validated_data["duration"]." seconds.\n\nPlease make sure to contact your elder and check on his/her health!";
            $notification->accuracy = $validated_data["accuracy"];
            $notification->duration = $validated_data["duration"];
            $notification->notificationseen = false;
            $notification->save();

            DB::commit();
            $newNotification = new NotificationResource($notification);

             Mail::raw($notification->content, function($message) use($notification)
            {
                    $message->from("hello@example.com",'Smart Emotion - AAL');
                    $message->to(Auth::user()->email);
                    $message->subject($notification->title);
            });

            return $newNotification;
        }catch(\Throwable $th){
            DB::rollback();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  $notification
     * @return NotificationResource
     */
    public function show($notification)
    {
        $notification = Notification::findOrFail($notification);
        return new NotificationResource($notification);
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
        //
    }

 /**
     * Patch - update notification visibility
     *
     * @param  $notification
     * @return NotificationResource
     */
    public function patch($notification){

        $notification = Notification::findOrFail($notification);
        try {
            DB::beginTransaction();
            $notification->notificationseen = true;
            $notification->save();
            DB::commit();
        }catch(\Throwable $th){
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }

        return new NotificationResource($notification);

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
