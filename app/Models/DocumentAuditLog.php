<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAuditLog extends Model
{
    protected $fillable = [
        'document_id', 'version_id', 'performed_by',
        'action', 'ip_address', 'details',
    ];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }
}