<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentVersionController extends Controller
{
    public function __construct(private DocumentService $documentService) {}

    /**
     * Agregar nueva versión a un documento
     */
    public function store(Request $request, Document $document): JsonResponse
    {
        $request->validate([
            'file'        => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:102400',
            'description' => 'required|string|max:500',
        ]);

        $version = $this->documentService->addVersion(
            $document,
            $request->file('file'),
            $request->description,
            $request->user()->id
        );

        return response()->json($version->load('createdBy'), 201);
    }

    /**
     * Listar versiones de un documento
     */
    public function index(Document $document): JsonResponse
    {
        $versions = $document->versions()
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($versions);
    }
}