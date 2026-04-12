<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Crear documento con su primera versión
     */
    public function create(array $data, UploadedFile $file, int $userId): Document
    {
        // Generar código único
        $data['code'] = $this->generateCode($data['document_type_id'] ?? null);
        $data['owner_id']    = $userId;
        $data['created_by']  = $userId;
        $data['status']      = 'borrador';
        $data['current_version'] = '1.0';

        $document = Document::create($data);

        // Guardar primera versión
        $this->createVersion($document, $file, '1.0', 'Versión inicial', $userId);

        // Registrar auditoría
        AuditService::log($document->id, 'creado', $userId);

        return $document->load(['type', 'unit', 'owner', 'currentVersion']);
    }

    /**
     * Crear nueva versión de un documento existente
     */
    public function addVersion(
        Document $document,
        UploadedFile $file,
        string $description,
        int $userId
    ): DocumentVersion {
        // Calcular siguiente versión
        $nextVersion = $this->nextVersionNumber($document->current_version);

        // Marcar versión anterior como no actual
        $document->versions()->update(['is_current' => false]);

        // Crear nueva versión
        $version = $this->createVersion($document, $file, $nextVersion, $description, $userId);

        // Actualizar documento
        $document->update([
            'current_version' => $nextVersion,
            'updated_by'      => $userId,
        ]);

        // Auditoría
        AuditService::log(
            $document->id,
            'versionado',
            $userId,
            $version->id,
            ['version' => $nextVersion, 'description' => $description]
        );

        return $version;
    }

    /**
     * Mover a papelera (soft delete)
     */
    public function moveToTrash(Document $document, int $userId): void
    {
        $document->update([
            'is_deleted' => true,
            'deleted_at' => now(),
            'updated_by' => $userId,
        ]);

        AuditService::log($document->id, 'eliminado', $userId);
    }

    /**
     * Restaurar desde papelera
     */
    public function restore(Document $document, int $userId): void
    {
        $document->update([
            'is_deleted' => false,
            'deleted_at' => null,
            'updated_by' => $userId,
        ]);

        AuditService::log($document->id, 'restaurado', $userId);
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(Document $document, int $userId): void
    {
        AuditService::log($document->id, 'eliminado_permanente', $userId);

        // Eliminar archivos físicos
        foreach ($document->versions as $version) {
            Storage::disk('local')->delete($version->file_path);
        }

        $document->delete();
    }

    /**
     * Crear versión y guardar archivo
     */
    private function createVersion(
        Document $document,
        UploadedFile $file,
        string $versionNumber,
        string $description,
        int $userId
    ): DocumentVersion {
        $path = $file->store("documents/{$document->id}", 'local');

        return DocumentVersion::create([
            'document_id'       => $document->id,
            'version_number'    => $versionNumber,
            'file_path'         => $path,
            'file_original_name'=> $file->getClientOriginalName(),
            'file_hash'         => hash_file('sha256', $file->getRealPath()),
            'file_size'         => $file->getSize(),
            'file_type'         => strtolower($file->getClientOriginalExtension()),
            'change_description'=> $description,
            'created_by'        => $userId,
            'is_current'        => true,
        ]);
    }

    /**
     * Generar código único para el documento
     */
    private function generateCode(?int $typeId): string
    {
        $prefix = 'DOC';
        if ($typeId) {
            $type = \App\Models\DocumentType::find($typeId);
            if ($type) $prefix = $type->code_prefix;
        }
        $year  = now()->year;
        $count = Document::whereYear('created_at', $year)->count() + 1;
        return sprintf('%s-%04d-%s', $prefix, $count, $year);
    }

    /**
     * Calcular siguiente número de versión
     * 1.0 → 2.0, 2.0 → 3.0, 2.0.1 → 2.0.2
     */
    private function nextVersionNumber(string $current): string
    {
        $parts = explode('.', $current);
        $parts[count($parts) - 1]++;
        return implode('.', $parts);
    }
}