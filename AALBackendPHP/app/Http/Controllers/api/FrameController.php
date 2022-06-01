<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\Frame\CreateFrameRequest;
use App\Http\Resources\Emotion\EmotionResource;
use App\Http\Resources\Frame\FrameResource;
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
        return new FrameResource(Frame::all());
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
     * @return EmotionResource|\Illuminate\Http\JsonResponse
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
                        $classification->emotion()->associate(Emotion::find($aux[0]));
                        $classification->accuracy = $aux[1];
                        $classification->frame()->associate($frame);
                    }
                }

                $iteration->save();
                DB::commit();

                return new EmotionResource($iteration);
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
