<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\RegulationMechanism;
use App\Http\Resources\RegulationMechanism\RegulationMechanismResource;
use App\Http\Resources\RegulationMechanism\RegulationMechanismCollection;

class RegulationMechanismController extends Controller
{
    public function index()
    {
        $mechanisms = RegulationMechanism::all();
        return new RegulationMechanismCollection($mechanisms);
    }

    public function show($regulationMechanism)
    {
        abort(404);
    }

    public function create()
    {
        abort(404);
    }
    public function store($regulationMechanism)
    {
        abort(404);
    }

    public function edit($id)
    {
        abort(404);
    }
    public function update(Request $request, $id)
    {
        abort(404);
    }
    public function destroy($regulationMechanism)
    {
        abort(404);
    }
}
