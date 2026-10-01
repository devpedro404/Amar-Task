<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">My Tasks</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <form method="GET" action="{{ route('tasks.index') }}" class="flex gap-2">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search tasks..."
                            class="rounded-md border-gray-300 shadow-sm"
                        >
                        <button type="submit" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300">
                            Search
                        </button>
                    </form>

                    <a href="{{ route('tasks.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                        New task
                    </a>
                </div>

                <ul class="divide-y divide-gray-200">
                    @forelse ($tasks as $task)
                        <li class="flex items-start justify-between gap-4 py-4">
                            <div>
                                <p class="font-medium {{ $task->isCompleted() ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                    {{ $task->title }}
                                </p>
                                @if ($task->description)
                                    <p class="mt-1 text-sm text-gray-500">{{ $task->description }}</p>
                                @endif
                            </div>

                            <div class="flex shrink-0 items-center gap-3 text-sm">
                                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-green-700 hover:underline">
                                        {{ $task->isCompleted() ? 'Reopen' : 'Complete' }}
                                    </button>
                                </form>

                                <a href="{{ route('tasks.edit', $task) }}" class="text-blue-700 hover:underline">Edit</a>

                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:underline">Delete</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-gray-500">No tasks found.</li>
                    @endforelse
                </ul>

                <div class="mt-6">
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>