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
                    "name" => "Nº de notificações",
                    "value" => sizeof($notifications)];
            else
                $statistics[] = (object)[
                    "name" => "Nº de notificações",
                    "value" => "Sem notificações"];

            $listPair = DB::table("notifications")
                ->select('notifications.emotion_name as value', DB::raw("count('notifications.emotion_name') as subValue"))
                ->where('notifications.client_id', '=', Auth::user()->userable_id)
                ->groupBy('notifications.emotion_name')
                ->orderBy("subValue", 'desc')
                ->first();
            if ($listPair != null)
                $statistics[] = (object)[
                    "name" => "Emoção com mais notificações",
                    "value" => $listPair->value,
                    "subValue" => $listPair->subValue];
            else
                $statistics[] = (object)[
                    "name" => "Emoção com mais notificações",
                    "value" => "Sem notificações"];

            $iterations = Auth::user()->userable->iterations;
            if (sizeof($iterations) > 0)
                $statistics[] = (object)[
                    "name" => "Nº de iterações",
                    "value" => sizeof($iterations)];
            else
                $statistics[] = (object)[
                    "name" => "Nº de iterações",
                    "value" => "Sem iterações"];

            $listPair2 = DB::table("emotionsnotifications")
                ->select('notifications.emotion_name as value', DB::raw("count('notifications.emotion_name') as subValue"))
                ->join('notifications', 'emotionsnotifications.emotion_name', 'notifications.emotion_name')
                ->where('emotionsnotifications.client_id', '=', Auth::user()->userable_id)
                ->groupBy('notifications.emotion_name')
                ->orderBy("subValue", 'desc')
                ->first();
            if ($listPair2 != null)
                $statistics[] = (object)[
                    "name" => "Emoção com menos notificações configuradas",
                    "value" => $listPair2->value,
                    "subValue" => $listPair2->subValue];
            else
                $statistics[] = (object)[
                    "name" => "Emoção com menos notificações configuradas",
                    "value" => "Sem notificações"];

            return $statistics;
        }

        $notifications = Notification::all();
        if (sizeof($notifications) > 0)
            $statistics[] = (object)[
                "name" => "Nº de notificações",
                "value" => sizeof($notifications)];
        else
            $statistics[] = (object)[
                "name" => "Nº de notificações",
                "value" => "Sem notificações"];

        $listPair = DB::table("notifications")
            ->select('notifications.emotion_name as value', DB::raw("count('notifications.emotion_name') as subValue"))
            ->groupBy('notifications.emotion_name')
            ->orderBy("subValue", 'desc')
            ->first();
        if ($listPair != null)
            $statistics[] = (object)[
                "name" => "Emoção com mais notificações",
                "value" => $listPair->value,
                "subValue" => $listPair->subValue];
        else
            $statistics[] = (object)[
                "name" => "Emoção com mais notificações",
                "value" => "Sem notificações"];

        $iterations = Iteration::all();
        if (sizeof($iterations) > 0)
            $statistics[] = (object)[
                "name" => "Nº de iterações",
                "value" => sizeof($iterations)];
        else
            $statistics[] = (object)[
                "name" => "Nº de iterações",
                "value" => "Sem iterações"];

        $listPair2 = DB::table("emotionsnotifications")
            ->select('notifications.emotion_name as value', DB::raw("count('notifications.emotion_name') as subValue"))
            ->join('notifications', 'emotionsnotifications.emotion_name', 'notifications.emotion_name')
            ->groupBy('notifications.emotion_name')
            ->orderBy("subValue", 'desc')
            ->first();
        if ($listPair2 != null)
            $statistics[] = (object)[
                "name" => "Emoção com menos notificações configuradas",
                "value" => $listPair2->value,
                "subValue" => $listPair2->subValue];
        else
            $statistics[] = (object)[
                "name" => "Emoção com menos notificações configuradas",
                "value" => "Sem notificações"];

        return $statistics;
    }
}
