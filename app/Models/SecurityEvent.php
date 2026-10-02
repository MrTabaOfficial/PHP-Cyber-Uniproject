<?php

namespace App\Models;

use App\Enums\SecurityEventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SecurityEvent extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => SecurityEventType::class,
        'metadata' => 'array',
    ];

    /**
     * Record an event together with the origin of the current request.
     *
     * @param  array<string, mixed>  $metadata
     */
    public static function record(SecurityEventType $type, ?User $user = null, array $metadata = []): self
    {
        return static::create([
            'user_id' => $user?->getKey(),
            'type' => $type,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 250, ''),
            'metadata' => $metadata ?: null,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Events older than the configured retention period are pruned daily.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(config('security.audit_log.retention_days')));
    }
}
