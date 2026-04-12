<?php

namespace App\Services;

use App\Models\DocumentAuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public static function log(
        int $documentId,
        string $action,
        int $performedBy,
        ?int $versionId = null,
        ?array $details = null,
        ?string $ip = null
    ): void {
        DocumentAuditLog::create([
            'document_id'  => $documentId,
            'version_id'   => $versionId,
            'performed_by' => $performedBy,
            'action'       => $action,
            'ip_address'   => $ip,
            'details'      => $details,
        ]);
    }
}