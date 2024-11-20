<?php

namespace App\Http\Controllers\api;

use App\Models\Iteration;
use App\Models\Notification;
use App\Models\Message;
use App\Models\Emotion;
use App\Models\EmotionRegulationMechanism;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $statistics = array();
        $emotions = Emotion::get();
        if (str_contains(strtolower(Auth::user()->userable_type), "client")){
            $notifications = Auth::user()->userable->notifications()->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
            if (sizeof($notifications) > 0)
                $statistics[] = (object)[
                    "name" => "Nº de notificações da última semana",
                    "value" => sizeof($notifications)];
            else
                $statistics[] = (object)[
                    "name" => "Nº de notificações da última semana",
                    "value" => "Sem notificações"];
            
                    $emotionNotifications = Auth::user()->userable->emotionNotifications;
                    
                    if (sizeof($emotionNotifications) > 0)
                        $statistics[] = (object)[
                            "name" => "Nº de configurações para notificações",
                            "value" => sizeof($emotionNotifications)." de ".sizeof($emotions)." emoções"];
                    else
                        $statistics[] = (object)[
                            "name" => "Nº de configurações para notificações",
                            "value" => "Sem configurações de notificações"];        

            $iterations = Auth::user()->userable->iterations;
            if (sizeof($iterations) > 0){
                $statistics[] = (object)[
                    "name" => "Nº de iterações da última semana",
                    "value" => sizeof(Auth::user()->userable->iterations()->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get())];
                $lastIterationDate = Auth::user()->userable->iterations()->orderBy('created_at', 'desc')->first()->created_at;
                $statistics[] = (object)[
                    "name" => "Data da última iteração",
                    "value" => date("d/m/Y H:i", $lastIterationDate)];
            }
            else
                $statistics[] = (object)[
                    "name" => "Nº de iterações da última semana",
                    "value" => "Sem iterações"];
            

            $emotionRegulationMechanisms = Auth::user()->userable->emotionRegulationMechanisms;
            if (sizeof($emotionRegulationMechanisms) > 0)
                $statistics[] = (object)[
                    "name" => "Nº de mecanismos de regulações de emoções",
                    "value" => sizeof($emotionRegulationMechanisms)." de ".sizeof($emotions)." emoções"];
            else
                $statistics[] = (object)[
                    "name" => "Nº de mecanismos de regulações de emoções",
                    "value" => "Sem mecanismos de regulações de emoções"];
            
            $contents =  EmotionRegulationMechanism::where('client_id', Auth::user()->userable_id)
            ->with('regulationMechanismsContents') // Preload the relationship
            ->get()
            ->sum(function ($mechanism) {
                return $mechanism->regulationMechanismsContents->count();
            });

            if ($contents > 0)
                $statistics[] = (object)[
                    "name" => "Nº de conteúdos para regulação de emoções",
                    "value" => $contents];
            else
                $statistics[] = (object)[
                    "name" => "Nº de conteúdos para regulação de emoções",
                    "value" => "Sem conteúdos para regulação de emoções"];

            $questionnaires = Auth::user()->userable->questionnaires;
            $nonCompletedquestionnaires = $questionnaires->where('points', '=', NULL);
            if (sizeof($nonCompletedquestionnaires) > 0)
                $statistics[] = (object)[
                    "name" => "Nº de questionários iniciados",
                    "value" => sizeof($nonCompletedquestionnaires)];
            else
                $statistics[] = (object)[
                    "name" => "Nº de questionários iniciados",
                    "value" => "Sem questionários iniciados"];
            
            $completedquestionnaires = $questionnaires->where('points', '!=', NULL);
            if (sizeof($completedquestionnaires) > 0)
                $statistics[] = (object)[
                    "name" => "Nº de questionários completados",
                    "value" => sizeof($completedquestionnaires)];
            else
                $statistics[] = (object)[
                    "name" => "Nº de questionários completados",
                    "value" => "Sem questionários completados"];

            $messages = Message::where('client_id', '=', Auth::user()->userable_id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            $messagesCount = $messages->count();
            if ($messagesCount > 0){
                $statistics[] = (object)[
                    "name" => "Nº de mensagens da última semana",
                    "value" => $messagesCount];
                $lastMessageDate = $messages->orderBy('created_at', 'desc')->first()->created_at;
                $statistics[] = (object)[
                    "name" => "Data da última mensagem",
                    "value" => date("d/m/Y H:i", $lastMessageDate)];
            }
            else
                $statistics[] = (object)[
                    "name" => "Nº de mensagens da última semana",
                    "value" => "Sem mensagens"];


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
