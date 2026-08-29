<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function __construct(private readonly Request $request) {}

    /** @param array<string, mixed> $properties */
    public function record(string $action, string $description, ?Model $auditable = null, array $properties = []): AuditLog
    {
        return AuditLog::query()->create([
            'user_id' => $this->request->user()?->id,
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'properties' => $properties,
            'ip_address' => $this->request->ip(),
            'user_agent' => str($this->request->userAgent())->limit(500)->toString(),
        ]);
    }
}
