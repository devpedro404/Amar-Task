<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\CreateTask;
use App\Actions\Tasks\ToggleTaskCompletion;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('tasks.index');
        }

        $tasks = $request->user()
            ->tasks()
            ->search($request->input('search'))
            ->latest()
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json([
            'data' => $tasks->getCollection()->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'completed' => $task->isCompleted(),
                'edit_url' => route('tasks.edit', $task),
            ])->values(),
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
            'total' => $tasks->total(),
        ]);
    }

    public function create(): View
    {
        return view('tasks.create');
    }

    public function store(StoreTaskRequest $request, CreateTask $createTask): RedirectResponse
    {
        $createTask->execute($request->user(), $request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task created successfully.');
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', ['task' => $task]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task updated successfully.');
    }

    public function toggle(Request $request, Task $task, ToggleTaskCompletion $toggleTaskCompletion): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $task);

        $toggleTaskCompletion->execute($task);

        if ($request->wantsJson()) {
            return response()->json(['completed' => $task->isCompleted()]);
        }

        return back();
    }

    public function destroy(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task deleted successfully.');
    }
}
