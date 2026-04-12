<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnitController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Unit::where('is_active', true)->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:units',
        ]);
        return response()->json(Unit::create($request->all()), 201);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $unit->update($request->all());
        return response()->json($unit);
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $unit->update(['is_active' => false]);
        return response()->json(['message' => 'Unidad desactivada.']);
    }
}