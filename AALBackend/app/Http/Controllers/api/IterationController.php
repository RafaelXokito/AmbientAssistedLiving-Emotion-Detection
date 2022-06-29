<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Frame\CreateFrameRequest;
use App\Http\Requests\Iteration\CreateIterationRequest;
use App\Http\Resources\Frame\FrameCollection;
use App\Http\Resources\Frame\FrameResource;
use App\Http\Resources\Iteration\IterationCollection;
use App\Http\Resources\Iteration\IterationResource;
use App\Models\Client;
use App\Models\Emotion;
use App\Models\Frame;
use App\Models\Iteration;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IterationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return IterationCollection
     */
    public function index()
    {
        return new IterationCollection(Auth::user()->userable->iterations()->orderBy('created_at', 'desc')->get());
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
     * @return IterationResource|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(CreateIterationRequest $request)
    {
        $iteration = new Iteration();
        $validated_data = $request->validated();
        try {
            DB::beginTransaction();
            $iteration->emotion()->associate(Emotion::findOrFail(strtolower($validated_data["emotion"])));
            $iteration->macaddress = $validated_data["macAddress"];
            $iteration->client()->associate(Auth::user()->userable);

            $iteration->usage_id = Str::uuid();

            $iteration->save();
            DB::commit();

            IterationResource::$format = "show_usage_id";
            return new IterationResource($iteration);

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
     * @param  Iteration  $iteration
     * @return IterationResource
     */
    public function show(Iteration $iteration)
    {
        return new IterationResource($iteration);
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
     * Get the ClassificationGraphData resource in storage.
     *
     * @param  string  $pattern
     * @return FrameCollection|\Illuminate\Http\JsonResponse
     */
    public function showGraphData(Request $request)
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

        $date = Carbon::now()->subDays(7);

        $query = Iteration::select(DB::raw('count(*) as c'), DB::raw('DATE_FORMAT(iterations.created_at,'.$pattern.') as d'))
            ->groupBy(DB::raw('DATE_FORMAT(iterations.created_at,'.$pattern.')'));
        if(str_contains(strtolower(Auth::user()->userable_type), "client")) {
            $query = $query->where('iterations.client_id', '=', Auth::user()->userable_id)->get();
        }else
            $query = $query->get();

        return $query;
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
