@php($user = $user ?? null)

<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $user?->name) }}"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $user?->email) }}"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
            Password
            @if ($user)
                <span class="font-normal text-gray-500">(leave blank to keep the current one)</span>
            @endif
        </label>
        <input
            id="password"
            name="password"
            type="password"
            @if (! $user) required @endif
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >
        @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            Save
        </button>
        <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
    </div>
</div>