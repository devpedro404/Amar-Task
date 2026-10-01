@php($task = $task ?? null)

<div class="space-y-4">
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $task?->title) }}"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >
        @error('title')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >{{ old('description', $task?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            Save
        </button>
        <a href="{{ route('tasks.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
    </div>
</div>