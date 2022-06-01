<?php

namespace App\Http\Controllers\api;

use App\Models\Iteration;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $statistics = array();
        if (str_contains(strtolower(Auth::user()->userable_type), "client")){
            $notifications = Auth::user()->userable->notifications;
            if (sizeof($notifications) > 0)
                $statistics[] = (object)[
                    "name" => "Total of notifications",
                    "value" => sizeof($notifications)];
            else
                $statistics[] = (object)[
                    "name" => "Total of notifications",
                    "value" => "No notifications"];

            $listPair = DB::table("notifications")
                ->select('notifications.emotion_name as value', DB::raw("count('notifications.emotion_name') as subValue"))
                ->where('notifications.client_id', '=', Auth::user()->userable_id)
                ->groupBy('notifications.emotion_name')
                ->orderBy("subValue", 'desc')
                ->first();
            if ($listPair != null)
                $statistics[] = (object)[
                    "name" => "Emotion with the most notifications",
                    "value" => $listPair["value"],
                    "subValue" => $listPair["subValue"]];
            else
                $statistics[] = (object)[
                    "name" => "Emotion with the most notifications",
                    "value" => "No notifications"];

            $iterations = Auth::user()->userable->iterations;
            if (sizeof($iterations) > 0)
                $statistics[] = (object)[
                    "name" => "Total of iterations",
                    "value" => sizeof($iterations)];
            else
                $statistics[] = (object)[
                    "name" => "Total of iterations",
                    "value" => "No iterations"];

            $listPair2 = DB::table("emotionsnotifications")
                ->select('notifications.emotion_name as value', DB::raw("count('notifications.emotion_name') as subValue"))
                ->join('notifications', 'emotionsnotifications.emotion_name', 'notifications.emotion_name')
                ->where('emotionsnotifications.client_id', '=', Auth::user()->userable_id)
                ->groupBy('notifications.emotion_name')
                ->orderBy("subValue", 'desc')
                ->first();
            if ($listPair2 != null)
                $statistics[] = (object)[
                    "name" => "Emotion with the least notifications configured",
                    "value" => $listPair2["value"],
                    "subValue" => $listPair2["subValue"]];
            else
                $statistics[] = (object)[
                    "name" => "Emotion with the least notifications configured",
                    "value" => "No notifications"];

            return $statistics;
        }

        $notifications = Notification::all();
        if (sizeof($notifications) > 0)
            $statistics[] = (object)[
                "name" => "Total of notifications",
                "value" => sizeof($notifications)];
        else
            $statistics[] = (object)[
                "name" => "Total of notifications",
                "value" => "No notifications"];

        $listPair = DB::table("notifications")
            ->select('notifications.emotion_name as value', DB::raw("count('notifications.emotion_name') as subValue"))
            ->groupBy('notifications.emotion_name')
            ->orderBy("subValue", 'desc')
            ->first();
        if ($listPair != null)
            $statistics[] = (object)[
                "name" => "Emotion with the most notifications",
                "value" => $listPair["value"],
                "subValue" => $listPair["subValue"]];
        else
            $statistics[] = (object)[
                "name" => "Emotion with the most notifications",
                "value" => "No notifications"];

        $iterations = Iteration::all();
        if (sizeof($iterations) > 0)
            $statistics[] = (object)[
                "name" => "Total of iterations",
                "value" => sizeof($iterations)];
        else
            $statistics[] = (object)[
                "name" => "Total of iterations",
                "value" => "No iterations"];

        $listPair2 = DB::table("emotionsnotifications")
            ->select('notifications.emotion_name as value', DB::raw("count('notifications.emotion_name') as subValue"))
            ->join('notifications', 'emotionsnotifications.emotion_name', 'notifications.emotion_name')
            ->groupBy('notifications.emotion_name')
            ->orderBy("subValue", 'desc')
            ->first();
        if ($listPair2 != null)
            $statistics[] = (object)[
                "name" => "Emotion with the least notifications configured",
                "value" => $listPair2["value"],
                "subValue" => $listPair2["subValue"]];
        else
            $statistics[] = (object)[
                "name" => "Emotion with the least notifications configured",
                "value" => "No notifications"];

        return $statistics;
    }
}
