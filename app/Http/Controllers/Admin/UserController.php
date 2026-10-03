<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\SecurityEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a searchable list of every account.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::enum(Role::class)],
        ]);

        $users = User::query()
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereLike('name', "%{$search}%")
                        ->orWhereLike('email', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::cases(),
        ]);
    }

    /**
     * Show the form for editing an account.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::cases(),
            'events' => $user->securityEvents()->latest('id')->limit(10)->get(),
        ]);
    }

    /**
     * Update an account's details and role.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $role = Role::from($validated['role']);

        if ($role !== $user->role) {
            $this->authorize('changeRole', $user);
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $changed = array_keys($user->getDirty());
        $previousRole = $user->role;
        $user->role = $role;
        $user->save();

        if ($changed !== []) {
            SecurityEvent::record(SecurityEventType::UserUpdatedByAdmin, $user, [
                'admin_id' => $request->user()->id,
                'changed' => $changed,
            ]);
        }

        if ($role !== $previousRole) {
            SecurityEvent::record(SecurityEventType::RoleChangedByAdmin, $user, [
                'admin_id' => $request->user()->id,
                'from' => $previousRole->value,
                'to' => $role->value,
            ]);
        }

        if ($user->wasChanged('email')) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('admin.users.edit', $user)->with('status', 'user-updated');
    }

    /**
     * Delete an account.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        SecurityEvent::record(SecurityEventType::UserDeletedByAdmin, $user, [
            'admin_id' => $request->user()->id,
            'email' => $user->email,
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'user-deleted');
    }
}
