<?php

namespace App\Http\Controllers\api;

use App\Http\Resources\Iteration\IterationCollection;
use App\Http\Resources\Iteration\IterationResource;
use App\Models\Iteration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IterationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return IterationCollection
     */
    public function index()
    {
        return new IterationCollection(Auth::user()->iterations);
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort(404);
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
