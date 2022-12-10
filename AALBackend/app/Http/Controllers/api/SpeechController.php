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
        $speeches = Speech::join('contents', 'contents.childable_id', '=', 'speeches.id')
        ->join('iterations', 'iterations.id', '=', 'contents.iteration_id')
        ->where("contents.childable_type", "App\\Models\\Speech")
        ->where("iterations.client_id", Auth::user()->userable->id)
        ->select('speeches.*')
        ->simplePaginate(30);

        return new SpeechCollection($speeches);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return SpeechResource
     */
    public function show(Speech $speech)
    {
        SpeechResource::$format = "extended";
        return new SpeechResource($speech);
    }

    /**
     * Show the specified speeches by given iteration.
     *
     * @param  Iteration  $iteration
     * @return SpeechCollection
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
        SpeechResource::$format = "extended";
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

            SpeechResource::$format = "extended";
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
     * @param  Speech  $speech
     * @return SpeechResource
     */
    public function classifySpeech(ClassifyContentRequest $request, Speech $speech)
    {
        $validated_data = $request->validated();
        $content = $speech->content;
        $content->emotion()->associate(Emotion::find(strtolower($validated_data["name"])));
        $content->save();
        return new SpeechResource($speech);
    }

    /**
     * Display the last resource.
     *
     * @return SpeechResource
     */
    public function last()
    {
        $last_speech = Speech::join('contents', 'contents.childable_id', '=', 'speeches.id')
            ->join('iterations', 'iterations.id', '=', 'contents.iteration_id')
            ->where("contents.childable_type", "App\\Models\\Speech")
            ->where("iterations.client_id", Auth::user()->userable->id)
            ->select('speeches.*')
            ->get()->last();
        if ($last_speech != null) {
            SpeechResource::$format = "extended";
            return new SpeechResource($last_speech);
        }
        return response()->json(array(
            'code'      =>  422,
            'message'   =>  "No files were inserted"
        ), 422);
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
