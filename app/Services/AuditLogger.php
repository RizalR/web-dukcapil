<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function __construct(private readonly Request $request) {}

    /** @param array<string, mixed> $properties */
    public function record(string $action, string $description, ?Model $auditable = null, array $properties = []): AuditLog
    {
        return $this->recordForUser($this->request->user(), $action, $description, $auditable, $properties);
    }

    /** @param array<string, mixed> $properties */
    public function recordForUser(?User $user, string $action, string $description, ?Model $auditable = null, array $properties = []): AuditLog
    {
        return AuditLog::query()->create([
            'user_id' => $user?->id,
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
