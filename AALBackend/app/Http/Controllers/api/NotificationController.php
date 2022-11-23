<?php

namespace App\Http\Controllers\api;

use App\Models\Emotion;
use App\Models\Notification;
use http\Exception\InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\Notification\NotificationResource;
use App\Http\Resources\Notification\NotificationCollection;
use App\Http\Requests\Notification\CreateNotificationRequest;
use App\Mail\NewNotification;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return NotificationCollection|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has("is-short") && $request["is-short"] == "yes")
            return new NotificationCollection(Notification::orderBy('created_at', 'DESC')->where('client_id', '=', Auth::user()->userable->id)->take(5)->get());
        return new NotificationCollection(Notification::orderBy('created_at', 'DESC')->where('client_id', '=', Auth::user()->userable->id)->get());
    }

    public function top()
    {
        $topNotification = Auth::user()->userable->notifications()->orderBy('created_at', 'desc')->take(10)->get();
        return new NotificationResource($topNotification);
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
            $notification->content = "The elder in your care has been showing ".$validated_data["emotion_name"]." emotions continuously.\n\nThe '".$validated_data["emotion_name"]."' values were higher than the defined limit of '".$validated_data["accuracy"]."' and these feelings have lasted over the specified duration of ".$validated_data["duration"]." seconds.\n\nPlease make sure to contact your elder and check on his/her health!\n\nYou can access more details in http://aalemotion.dei.estg.ipleiria.pt/inbox";
            $notification->accuracy = $validated_data["accuracy"];
            $notification->duration = $validated_data["duration"];
            $notification->notificationseen = false;

            $notification->save();

            $file = $createNotificationRequest->file('file');
            $notification->path = basename(Storage::disk('local')->putFileAs('\\notifications\\'.Auth::user()->userable_id, $file, $notification->id . '.jpg'));

            $notification->save();

            DB::commit();

            $newNotification = new NotificationResource($notification);

            if (Auth::user()->notifiable) {
                Mail::raw($notification->content, function($message) use($notification)
                {
                    $message->from(env("MAIL_USERNAME"),'Smart Emotion - AAL');
                    $message->to(Auth::user()->email);
                    $message->subject($notification->title);
                });

                $basic  = new \Vonage\Client\Credentials\Basic(getenv("VONAGE_KEY"), getenv("VONAGE_SECRET"));
                $client = new \Vonage\Client($basic);

                $response = $client->sms()->send(
                    new \Vonage\SMS\Message\SMS("+351".Auth::user()->userable->contact, "AALEmotion", $notification->content)
                );

                $message = $response->current();

                if ($message->getStatus() != 0) {
                    throw new InvalidArgumentException(getenv("VONAGE_KEY"));
                }
            }

            return $newNotification;
        }catch(\Throwable $th){
            DB::rollback();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }

    public function showFoto(Notification $notification)
    {
        $path = storage_path('app/notifications/'.Auth::user()->userable_id.'/'.$notification->path);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        return $base64;
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

        $notification->notificationseen = true;
        $notification->save();

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
