<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">User details</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-gray-900">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-gray-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Registered at</dt>
                        <dd class="mt-1 text-gray-900">{{ $user->created_at->format('Y-m-d H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tasks</dt>
                        <dd class="mt-1 text-gray-900">{{ $completedTasksCount }} completed of {{ $tasksCount }}</dd>
                    </div>
                </dl>

                <div class="mt-8 flex items-center gap-4 text-sm">
                    <a href="{{ route('users.edit', $user) }}" class="text-blue-700 hover:underline">Edit</a>
                    <a href="{{ route('users.index') }}" class="text-gray-600 hover:underline">Back to users</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
