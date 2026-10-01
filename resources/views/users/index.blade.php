<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Users</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div
                    id="user-list"
                    data-index-url="{{ route('users.index') }}"
                    data-base-url="{{ url('users') }}"
                    data-create-url="{{ route('users.create') }}"
                ></div>
            </div>
        </div>
    </div>
</x-app-layout>
