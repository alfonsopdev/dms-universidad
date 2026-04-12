<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            DocumentType::where('is_active', true)->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code_prefix' => 'required|string|max:10|unique:document_types',
        ]);
        return response()->json(DocumentType::create($request->all()), 201);
    }

    public function update(Request $request, DocumentType $documentType): JsonResponse
    {
        $documentType->update($request->all());
        return response()->json($documentType);
    }

    public function destroy(DocumentType $documentType): JsonResponse
    {
        $documentType->update(['is_active' => false]);
        return response()->json(['message' => 'Tipo desactivado.']);
    }
}