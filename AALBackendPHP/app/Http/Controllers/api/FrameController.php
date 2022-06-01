<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Frame\ClassifyFrameRequest;
use App\Http\Requests\Frame\CreateFrameRequest;
use App\Http\Resources\Emotion\EmotionResource;
use App\Http\Resources\Frame\FrameCollection;
use App\Http\Resources\Frame\FrameResource;
use App\Http\Resources\Iteration\IterationResource;
use App\Models\Classification;
use App\Models\Client;
use App\Models\Emotion;
use App\Models\Frame;
use App\Models\Iteration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FrameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return FrameResource
     */
    public function index()
    {
        abort(404);
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
    public function store(CreateFrameRequest $request)
    {

        $iteration = new Iteration();
        $validated_data = $request->validated();

        try {
            DB::beginTransaction();

            if ($request->has("file")) {
                $iteration->emotion()->associate(Emotion::find(strtolower($validated_data["emotion"])));
                $iteration->macaddress = $validated_data["macAddress"];
                $iteration->client()->associate(Auth::user()->userable);

                $iteration->save();

                $files = $request->file('file');

                for ($i = 0; $i < count($files); $i++) {
                    $frame = new Frame();
                    $frame->name = $iteration->id . "_" . $i . '.jpg';
                    $frame->accuracy = $validated_data["accuraciesFrames"][$i];
                    $frame->path = basename(Storage::disk('local')->putFileAs('\\iterations\\'.Auth::user()->userable_id, $files[$i], $iteration->id . "_" . $i . '.jpg'));
                    $frame->iteration()->associate($iteration);
                    $frame->createdate = $validated_data["datesFrames"][$i];

                    $frame->save();

                    $classificationsAux = explode(";",$validated_data["preditionsFrames"][$i],count($files));
                    foreach ($classificationsAux as &$classificationAux) {

                        $classification = new Classification();
                        $aux = explode("#", $classificationAux, 2);
                        $classification->emotion()->associate(Emotion::find(strtolower($aux[0])));
                        $classification->accuracy = $aux[1];
                        $classification->frame()->associate($frame);

                        $classification->save();
                    }
                }

                $iteration->save();
                DB::commit();

                return new IterationResource($iteration);
            }

            return response()->json(array(
                'code'      =>  422,
                'message'   =>  "No files were selected"
            ), 422);

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
     * @return FrameResource
     */
    public function show(Frame $frame)
    {
        FrameResource::$format = "extended";
        return new FrameResource($frame);
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
     * Show the specified frames by given iteration.
     *
     * @param  Iteration  $iteration
     * @return FrameCollection
     */
    public function showFramesByIteration(Iteration $iteration)
    {
        FrameResource::$format = "extended";
        return new FrameCollection($iteration->frames());
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Frame  $frame
     * @return FrameResource
     */
    public function classifyFrame(ClassifyFrameRequest $request,Frame $frame)
    {
        $validated_data = $request->validated();

        $frame->emotion()->associate(Emotion::find(strtolower($validated_data["name"])));
        $frame->save();

        FrameResource::$format = "extended";
        return new FrameResource($frame);
    }

    /**
     * Get the graphData resource in storage.
     *
     * @param  Client  $client
     * @return FrameCollection
     */
    public function showGraphData(Client $client)
    {
        $frames = Frame::select('frames.*')->join('iterations', 'frames.iteration_id','=', 'iterations.id')
            ->where('iterations.client_id', '=', $client->id)->get();
        FrameResource::$format = "graph";
        return new FrameCollection($frames);
    }

    /**
     * Get the ClassificationGraphData resource in storage.
     *
     * @param  string  $pattern
     * @return FrameCollection|\Illuminate\Http\JsonResponse
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
                $pattern = "'yyyy-MM-DD'";
                break;
            case "YEARMONTH":
                $pattern = "'yyyy-MM'";
                break;
            case "YEAR":
                $pattern = "'yyyy'";
                break;
            case "MONTH":
                $pattern = "'MM'";
                break;
            case "WEEKDAY":
                $pattern = "'D'";
                break;
            default:
                $pattern = "'HH24'";
                break;
        }

        $query = Frame::select(DB::raw('count(*) as c'), DB::raw('DATE_FORMAT(updated_at,'.$pattern.') as d'))->whereNotNull('frames.emotion_name')->groupBy(DB::raw('DATE_FORMAT(updated_at,'.$pattern.')'));
        if(str_contains(strtolower(Auth::user()->userable_type), "client")) {
            $query = $query->join('iterations', 'frames.iteration_id','=', 'iterations.id')
                ->where('iterations.client_id', '=', Auth::user()->userable_id)->get();
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
