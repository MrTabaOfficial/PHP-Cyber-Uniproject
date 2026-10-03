<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SecurityEventType;
use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display the security events of every account.
     */
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::enum(SecurityEventType::class)],
        ]);

        $events = SecurityEvent::query()
            ->with('user')
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereLike('ip_address', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->whereLike('email', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-log.index', [
            'events' => $events,
            'filters' => $filters,
            'types' => SecurityEventType::cases(),
        ]);
    }
}
