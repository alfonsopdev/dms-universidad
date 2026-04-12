<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $documentService) {}

    /**
     * Listar documentos activos
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::active()
            ->with(['type', 'unit', 'owner', 'currentVersion'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->type_id) {
            $query->where('document_type_id', $request->type_id);
        }
        if ($request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }

        $documents = $query->paginate($request->per_page ?? 15);

        return response()->json($documents);
    }

    /**
     * Crear nuevo documento
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'file'             => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:102400',
            'document_type_id' => 'nullable|exists:document_types,id',
            'unit_id'          => 'nullable|exists:units,id',
            'status'           => 'nullable|in:borrador,activo',
            'parent_document_id' => 'nullable|exists:documents,id',
        ]);

        $document = $this->documentService->create(
            $request->only(['name', 'document_type_id', 'unit_id', 'status', 'parent_document_id']),
            $request->file('file'),
            $request->user()->id
        );

        return response()->json($document, 201);
    }

    /**
     * Ver detalle de un documento
     */
    public function show(Document $document): JsonResponse
    {
        $document->load([
            'type', 'unit', 'owner', 'createdBy', 'updatedBy',
            'versions.createdBy', 'auditLogs.performer',
            'permissions.unit', 'permissions.user',
            'parent',
        ]);

        $document->is_favorited = $document->isFavoritedBy(auth()->id());

        AuditService::log($document->id, 'visualizado', auth()->id());

        return response()->json($document);
    }

    /**
     * Actualizar datos del documento
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        $request->validate([
            'name'   => 'sometimes|string|max:255',
            'status' => 'sometimes|in:borrador,activo,en_revision,aprobado,obsoleto,anulado',
            'unit_id'=> 'sometimes|nullable|exists:units,id',
        ]);

        $document->update([
            ...$request->only(['name', 'status', 'unit_id']),
            'updated_by' => $request->user()->id,
        ]);

        AuditService::log($document->id, 'actualizado', $request->user()->id);

        return response()->json($document->load(['type', 'unit', 'owner']));
    }

    /**
     * Mover a papelera
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        $this->documentService->moveToTrash($document, $request->user()->id);
        return response()->json(['message' => 'Documento movido a la papelera.']);
    }

    /**
     * Papelera — listar eliminados
     */
    public function trash(): JsonResponse
    {
        $documents = Document::trashed()
            ->with(['type', 'unit', 'owner'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return response()->json($documents);
    }

    /**
     * Restaurar desde papelera
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        $document = Document::trashed()->findOrFail($id);
        $this->documentService->restore($document, $request->user()->id);
        return response()->json(['message' => 'Documento restaurado.']);
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(Request $request, int $id): JsonResponse
    {
        $document = Document::trashed()->findOrFail($id);
        $this->documentService->forceDelete($document, $request->user()->id);
        return response()->json(['message' => 'Documento eliminado permanentemente.']);
    }

    /**
     * Favoritos — toggle
     */
    public function toggleFavorite(Request $request, Document $document): JsonResponse
    {
        $userId = $request->user()->id;
        $existing = $document->favorites()->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $isFav = false;
        } else {
            $document->favorites()->create(['user_id' => $userId]);
            $isFav = true;
        }

        AuditService::log($document->id, $isFav ? 'favorito_agregado' : 'favorito_removido', $userId);

        return response()->json(['is_favorited' => $isFav]);
    }

    /**
     * Listar favoritos del usuario
     */
    public function favorites(Request $request): JsonResponse
    {
        $documents = Document::active()
            ->whereHas('favorites', fn($q) => $q->where('user_id', $request->user()->id))
            ->with(['type', 'unit', 'owner', 'currentVersion'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($documents);
    }

    /**
     * Descargar archivo actual
     */
    public function download(Document $document): mixed
    {
        $version = $document->versions()->where('is_current', true)->firstOrFail();

        AuditService::log($document->id, 'descargado', auth()->id(), $version->id);

        return response()->download(
            storage_path("app/{$version->file_path}"),
            $version->file_original_name
        );
    }
}