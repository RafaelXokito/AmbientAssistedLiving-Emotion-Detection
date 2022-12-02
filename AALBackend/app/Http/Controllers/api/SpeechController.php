<?php

namespace App\Http\Controllers\api;

use Carbon\Carbon;
use App\Models\Speech;
use App\Models\Content;
use App\Models\Emotion;
use App\Models\Iteration;
use Illuminate\Http\Request;
use App\Models\Classification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Speech\SpeechResource;
use App\Http\Resources\Speech\SpeechCollection;
use App\Http\Requests\Speech\CreateSpeechRequest;
use App\Http\Resources\Iteration\IterationResource;
use App\Http\Requests\Content\ClassifyContentRequest;

class SpeechController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return SpeechResource
     */
    public function index()
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return FrameResource
     */
    public function show(Speech $speech)
    {
        return new SpeechResource($speech);
    }

    /**
     * Show the specified frames by given iteration.
     *
     * @param  Iteration  $iteration
     * @return FrameCollection
     */
    public function showSpeechesByIteration(Iteration $iteration)
    {
        $contentsSpeeches = $iteration->contents->filter(function ($content, $key) {
            return $content->childable_type == "App\\Models\\Speech";
        });
        $speeches = [];
        foreach ($contentsSpeeches as $content) {
            array_push($speeches,$content->childable);
        }
        return new SpeechCollection($speeches);
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
    public function store(CreateSpeechRequest $request)
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

            for ($i = 0; $i < count($validated_data["textsSpeeches"]); $i++) {

                $speech = new Speech();
                $speech->text =  $validated_data["textsSpeeches"][$i];
                $speech->save();
                $speech->content()->create([
                    'accuracy' => $validated_data["accuraciesSpeeches"][$i],
                    'createdate' => $validated_data["datesSpeeches"][$i],
                    'iteration_id' => $iteration->id
                ]);
                $speech->save();
                $classificationsAux = explode(";", $validated_data["preditionsSpeeches"][$i]);
                foreach ($classificationsAux as &$classificationAux) {

                    $classification = new Classification();
                    $aux = explode("#", $classificationAux, 2);
                    $classification->emotion()->associate(Emotion::find(strtolower($aux[0])));
                    $classification->accuracy = $aux[1];
                    $classification->content()->associate($speech->content->id);
                    $classification->save();
                }
            }
            $iteration->save();

            DB::commit();

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Frame  $frame
     * @return FrameResource
     */
    public function classifySpeech(ClassifyContentRequest $request, Speech $speech)
    {
        $validated_data = $request->validated();
        $content = Content::findorFail($speech->content->id);
        $content->emotion()->associate(Emotion::find(strtolower($validated_data["name"])));
        $content->save();
        return new SpeechResource($speech);
    }

    /**
     * Display the last resource.
     *
     * @return FrameResource
     */
    public function last()
    {
        $lastIteration = Auth::user()->userable->iterations()->orderBy('created_at', 'desc')->get()->first();
        if($lastIteration->contents->where("childable_type", "App\\Models\\Speech")->count()>0){
            return new SpeechResource($lastIteration->contents->where("childable_type", "App\\Models\\Speech")->last()->childable);
        }else{
            return response()->json(array(
                'code'      =>  422,
                'message'   =>  "No speeches in the latest iteration"
            ), 422);
        }
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

//FIX THE REQUEST AND RESOURCES!
