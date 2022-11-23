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
use Carbon\Carbon;
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
        $validated_data = $request->validated();
        $iteration = Iteration::findOrFail($validated_data["iteration_id"]);
        if ($iteration->usage_id != $validated_data["iteration_usage_id"] && !Carbon::createFromTimestamp($iteration->created_at)->addMinutes(30)->gt(Carbon::now()))
            return response()->json(array(
                'code'      =>  422,
                'message'   =>  "Iteration Usage Id doesnt match or expired!"
            ), 422);


        try {
            DB::beginTransaction();

            if ($request->has("file")) {

                $files = $request->file('file');

                for ($i = 0; $i < count($files); $i++) {
                    $frame = new Frame();
                    $frame->name = $iteration->id . "_" . $i . '.jpg';
                    $frame->accuracy = $validated_data["accuraciesFrames"][$i];
                    $frame->path = basename(Storage::disk('local')->putFileAs('\\iterations\\'.Auth::user()->userable_id, $files[$i], $iteration->id . "_" . $i . '.jpg'));
                    $frame->iteration()->associate($iteration);
                    $frame->createdate = $validated_data["datesFrames"][$i];

                    $frame->save();

                    $classificationsAux = explode(";",$validated_data["preditionsFrames"][$i]);
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
     * Display the last resource.
     *
     * @return FrameResource
     */
    public function last()
    {
        $lastIteration = Auth::user()->userable->iterations()->orderBy('created_at', 'desc')->get()->first();
        FrameResource::$format = "extended";
        return new FrameResource($lastIteration->frames->last());
    }

    public function showFoto(Frame $frame)
    {
        $path = storage_path('app/iterations/'.Auth::user()->userable_id.'/'.$frame->path);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        return $base64;
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
        return new FrameCollection($iteration->frames);
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
        $date = Carbon::now()->subDays(7);

        $frames = Frame::select('frames.*')->join('iterations', 'frames.iteration_id','=', 'iterations.id')
            ->where('iterations.created_at', '>=', $date)
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

        $query = Frame::select(DB::raw('count(*) as c'), DB::raw('DATE_FORMAT(frames.updated_at,'.$pattern.') as d'))->whereNotNull('frames.emotion_name')->groupBy(DB::raw('DATE_FORMAT(frames.updated_at,'.$pattern.')'));
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
