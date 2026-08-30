<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
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

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.email' => 'required|email|max:255',
        'form.password' => 'nullable|string|min:8',
        'form.role' => 'required|in:owner,manager,accountant,staff',
    ];

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
            $data['password'] = Hash::make($this->form['password']);
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            $user->syncRoles([$this->form['role']]);
            app(AuditService::class)->record('user.updated', 'User', $user->id, $data);
            session()->flash('message', 'User updated.');
        } else {
            $user = User::create($data + ['password' => Hash::make($this->form['password'] ?? 'password123')]);
            $user->syncRoles([$this->form['role']]);
            app(AuditService::class)->record('user.created', 'User', $user->id, $data);
            session()->flash('message', 'User created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
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
        ])->layout('layouts.app');
    }
}
