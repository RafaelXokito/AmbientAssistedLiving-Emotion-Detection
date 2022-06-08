<?php

namespace App\Http\Controllers\api;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Log\LogResource;
use App\Http\Resources\Log\LogCollection;
use App\Http\Requests\Log\CreateLogRequest;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new LogCollection(Log::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateLogRequest $createLogRequest)
    {
        $log = new Log();

        $validated_data = $createLogRequest->validated();
        try{
            $log->macaddress = $validated_data["macAddress"];
            $log->content = $validated_data["content"];
            $log->process = $validated_data["process"];
            $log->client()->associate(Auth::user()->userable);
            $log->save();
            DB::commit();
            return new LogResource($log);
        }catch(\Throwable $th){
            DB::rollback();
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
     * @return \Illuminate\Http\Response
     */
    public function show($log)
    {
        $log = Log::findOrFail($log);
        return new LogResource($log);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }
}
