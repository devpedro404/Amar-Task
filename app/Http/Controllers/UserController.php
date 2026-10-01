<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('users.index');
        }

        $users = User::query()
            ->search($request->input('search'))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20);

        return response()->json([
            'data' => $users->getCollection()->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_current' => $user->id === $request->user()->id,
                'show_url' => route('users.show', $user),
                'edit_url' => route('users.edit', $user),
            ])->values(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'total' => $users->total(),
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function show(User $user): View
    {
        return view('users.show', [
            'user' => $user,
            'tasksCount' => $user->tasks()->count(),
            'completedTasksCount' => $user->tasks()->whereNotNull('completed_at')->count(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'User deleted successfully.');
    }
}