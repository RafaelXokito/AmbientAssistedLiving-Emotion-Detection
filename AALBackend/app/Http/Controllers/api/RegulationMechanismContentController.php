<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\RegulationMechanismContent;
use App\Models\EmotionRegulationMechanism;
use App\Http\Resources\RegulationMechanismContent\RegulationMechanismContentResource;
use App\Http\Resources\RegulationMechanismContent\RegulationMechanismContentCollection;
use App\Http\Requests\RegulationMechanismContent\RegulationMechanismContentRequest;
use App\Enums\RegulationMechanismContentTypes;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RegulationMechanismContentController extends Controller
{
    public function index()
    {
        $contents = RegulationMechanismContent::all();
        return new RegulationMechanismContentCollection($contents);
    }

    public function show(RegulationMechanismContent $regulationMechanismContent)
    {
        return new RegulationMechanismContentResource($regulationMechanismContent);
    }

    public function create()
    {
        abort(404);
    }

    public function store(RegulationMechanismContentRequest $request)
    {
        try{
            DB::beginTransaction();
            $validated_data = $request->validated();

            $request->validate([
                'content_type' => [Rule::enum(RegulationMechanismContentTypes::class)],
            ]);

            $isContentTypeText = $validated_data['content_type'] == RegulationMechanismContentTypes::Text->value;
            $url = "";
            if($isContentTypeText)
            {
                $request->validate([
                    'text' => ['required'],
                ]);

                $textExistsForCurrentERM = RegulationMechanismContent::where("emotion_regulation_mechanism", $validated_data["emotion_regulation_mechanism"])
                ->where("content_type", $validated_data['content_type'])
                ->where("text", $validated_data['text'])->exists();
                
                if($textExistsForCurrentERM){
                    return response()->json(array(
                        'code'      =>  422,
                        'message'   =>  "This textual content already exists in the current emotion regulation mechanism"
                    ), 422);
                }

            }else{
                switch($validated_data['content_type']){
                    case RegulationMechanismContentTypes::Image->value:
                        $request->validate([
                            'file' => ['required', 'image', 'mimes:jpg,bmp,png'],
                       ]);
                    break;
                    case RegulationMechanismContentTypes::Video->value:
                        $request->validate([
                            'file' => ['required', 'file', 'mimetypes:video/mp4,video/mov,video/avi'],
                       ]);
                    break;
                    case RegulationMechanismContentTypes::Audio->value:
                        $request->validate([
                            'file' => ['required', 'file', 'mimetypes:audio/mpeg'],
                       ]);
                    break;
                }

                $filename = $request->file('file')->getClientOriginalName();
                $url = Storage::url('ERMContents/client_' . Auth::user()->userable_id . '/' . $request->content_type . '/' . $filename);

                $contentExistsForCurrentERM = RegulationMechanismContent::where("emotion_regulation_mechanism", $validated_data["emotion_regulation_mechanism"])
                ->where("content_type", $validated_data['content_type'])
                ->where("file_path", $url)->exists();

                if($contentExistsForCurrentERM){
                    return response()->json(array(
                        'code'      =>  422,
                        'message'   =>  "This " . $validated_data['content_type'] . " content already exists in the current emotion regulation mechanism"
                    ), 422);
                }

                $path = 'ERMContents/' . Auth::user()->userable_id . '/' . $request->content_type;
                $file = $request->file('file');
                $file->storeAs($path, $filename);

                $path = $request->file('file')->storeAs(
                    'public/ERMContents/client_' . Auth::user()->userable_id . '/' . $request->content_type,
                    $filename
                );

            }

            $regulationMechanismContent = new RegulationMechanismContent();
            $regulationMechanismContent->content_type = $validated_data['content_type'];
            
            if($isContentTypeText)
            {
                $regulationMechanismContent->text = $validated_data['text'];
            }else{
                $regulationMechanismContent->file_path = $url;
            }

            $emotionRegulationMechanism = EmotionRegulationMechanism::findOrFail($validated_data["emotion_regulation_mechanism"]);
           
            $regulationMechanismContent->emotionRegulationMechanism()->associate($emotionRegulationMechanism);
            $regulationMechanismContent->save();
            DB::commit();
            return new RegulationMechanismContentResource($regulationMechanismContent);
        }
        catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }
    }
    public function edit()
    {
        abort(404);
    }

    public function update()
    {
        abort(404);
    }


    public function destroy(RegulationMechanismContent $regulationMechanismContent)
    {
        $regulationMechanismContent->delete();

        return response()->json(array(
            'code'      =>  200,
            'message'   =>  "Regulation mechanism content was deleted"
        ), 200);
    }
}
