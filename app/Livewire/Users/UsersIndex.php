<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'password' => '',
        'role' => 'staff',
    ];

    protected function rules(): array
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.email' => 'required|email|max:255|unique:users,email,'.($this->editingId ?: 'NULL'),
            'form.password' => $this->editingId ? 'nullable|string|min:8' : 'required|string|min:8',
            'form.role' => 'required|in:owner,manager,accountant,staff',
        ];
    }

    public function openCreate(): void
    {
        $this->authorize('create', User::class);
        $this->reset('editingId', 'form');
        $this->form['role'] = 'staff';
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        $this->editingId = $id;
        $this->form = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'password' => '',
            'role' => $user->roles->first()?->name ?? 'staff',
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->form['name'],
            'email' => $this->form['email'],
            'phone' => $this->form['phone'] ?? null,
        ];

        if ($this->form['password']) {
            $data['password'] = $this->form['password'];
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $this->authorize('update', $user);
            $user->update($data);
            $user->syncRoles([$this->form['role']]);
            app(AuditService::class)->record('user.updated', 'User', $user->id, $data);
            session()->flash('message', 'User updated.');
        } else {
            $this->authorize('create', User::class);
            $user = User::create($data + ['password' => $this->form['password']]);
            $user->syncRoles([$this->form['role']]);
            app(AuditService::class)->record('user.created', 'User', $user->id, $data);
            session()->flash('message', 'User created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function delete(string $id): void
    {
        $user = User::findOrFail($id);

        if ((int) $user->id === (int) auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $this->authorize('delete', $user);

        app(AuditService::class)->record('user.deleted', 'User', $user->id, null, $user->only(['name', 'email']));
        $user->delete();

        session()->flash('message', 'User deleted.');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
            )
            ->with('roles')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.users.index', [
            'users' => $users,
            'roles' => array_keys(User::ROLES),
        ])->layout('layouts.app');
    }
}
