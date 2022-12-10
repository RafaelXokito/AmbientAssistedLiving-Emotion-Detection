<?php

namespace App\Http\Controllers\api;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    /**
     * Get the ClassificationGraphData resource in storage.
     *
     * @param  string  $pattern
     * @return ContentCollection|\Illuminate\Http\JsonResponse
     */
    public function showClassificationGraphData(Request $request)
    {
        $pattern = "HOURS";

        if ($request->has("pattern"))
            $pattern = $request["pattern"];

        if (!strcmp($pattern,"YEARMONTHDAY") &&
            !strcmp($pattern, "YEARMONTH") &&
            !strcmp($pattern, "YEAR") &&
            !strcmp($pattern, "MONTH") &&
            !strcmp($pattern, "WEEKDAY") &&
            !strcmp($pattern, "HOURS"))
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  "[Error] -  pattern is invalid"
            ), 400);
        switch ($pattern){
            case "YEARMONTHDAY":
                $pattern = "'%Y-%M-%D'";
                break;
            case "YEARMONTH":
                $pattern = "'%Y-%M'";
                break;
            case "YEAR":
                $pattern = "'%Y'";
                break;
            case "MONTH":
                $pattern = "'%M'";
                break;
            case "WEEKDAY":
                $pattern = "'%W'";
                break;
            default:
                $pattern = "'%H'";
                break;
        }

       $query = Content::select(DB::raw('count(*) as c'), DB::raw('DATE_FORMAT(contents.updated_at,'.$pattern.') as d'))
       ->whereNotNull('contents.emotion_name')
       ->groupBy(DB::raw('DATE_FORMAT(contents.updated_at,'.$pattern.')'));

       if(str_contains(strtolower(Auth::user()->userable_type), "client")) {
            $query = $query->join('iterations', 'contents.iteration_id','=', 'iterations.id')
                ->where('iterations.client_id', '=', Auth::user()->userable_id)->get();
        }else
            $query = $query->get();

        return response()->json([
            'data' => $query,
        ]);;
    }
}
