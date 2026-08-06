<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('users.index', [
            'users' => User::query()->orderByDesc('is_admin')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        User::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_admin' => ['nullable', 'boolean'],
        ]));

        return redirect()
            ->route('users.index')
            ->with([
                'status' => 'Pouzivatel bol vytvoreny.',
                'status_type' => 'success',
                'status_title' => 'Pouzivatel vytvoreny',
            ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $isAdmin = (bool) ($data['is_admin'] ?? false);

        if ($request->user()->is($user)) {
            $isAdmin = true;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->is_admin = $isAdmin;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with([
                'status' => 'Pouzivatel bol aktualizovany.',
                'status_type' => 'success',
                'status_title' => 'Pouzivatel upraveny',
            ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        abort_if($request->user()->is($user), 422, 'Nemozes zmazat vlastny ucet.');

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with([
                'status' => 'Pouzivatel bol odstraneny.',
                'status_type' => 'warning',
                'status_title' => 'Pouzivatel zmazany',
            ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403);
    }
}