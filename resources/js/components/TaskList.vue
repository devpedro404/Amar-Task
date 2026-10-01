<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    indexUrl: { type: String, required: true },
    baseUrl: { type: String, required: true },
    createUrl: { type: String, required: true },
});

const tasks = ref([]);
const search = ref('');
const page = ref(1);
const lastPage = ref(1);
const total = ref(0);
const loading = ref(false);
const message = ref('');
const error = ref('');

let debounceTimer = null;
let latestRequest = 0;

async function loadTasks() {
    const requestId = ++latestRequest;
    loading.value = true;
    error.value = '';

    try {
        const { data } = await axios.get(props.indexUrl, {
            params: { search: search.value, page: page.value },
        });

        if (requestId !== latestRequest) {
            return;
        }

        tasks.value = data.data;
        page.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } catch (e) {
        error.value = 'Could not load tasks. Please try again.';
    } finally {
        if (requestId === latestRequest) {
            loading.value = false;
        }
    }
}

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        page.value = 1;
        loadTasks();
    }, 300);
});

function goToPage(targetPage) {
    if (targetPage < 1 || targetPage > lastPage.value) {
        return;
    }

    page.value = targetPage;
    loadTasks();
}

async function toggle(task) {
    error.value = '';

    try {
        const { data } = await axios.patch(`${props.baseUrl}/${task.id}/toggle`);
        task.completed = data.completed;
    } catch (e) {
        error.value = 'Could not update the task.';
    }
}

async function remove(task) {
    if (!confirm('Delete this task?')) {
        return;
    }

    error.value = '';

    try {
        await axios.delete(`${props.baseUrl}/${task.id}`);
        message.value = 'Task deleted successfully.';

        if (tasks.value.length === 1 && page.value > 1) {
            page.value -= 1;
        }

        await loadTasks();
    } catch (e) {
        error.value = 'Could not delete the task.';
    }
}

onMounted(loadTasks);
</script>

<template>
    <div>
        <div v-if="message" class="mb-4 rounded-md bg-green-100 p-4 text-sm text-green-800">
            {{ message }}
        </div>
        <div v-if="error" class="mb-4 rounded-md bg-red-100 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <input
                v-model="search"
                type="text"
                placeholder="Search tasks..."
                class="rounded-md border-gray-300 shadow-sm"
            >

            <a
                :href="createUrl"
                class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
            >
                New task
            </a>
        </div>

        <ul class="divide-y divide-gray-200">
            <li
                v-for="task in tasks"
                :key="task.id"
                class="flex items-start justify-between gap-4 py-4"
            >
                <div>
                    <p
                        class="font-medium"
                        :class="task.completed ? 'text-gray-400 line-through' : 'text-gray-900'"
                    >
                        {{ task.title }}
                    </p>
                    <p v-if="task.description" class="mt-1 text-sm text-gray-500">
                        {{ task.description }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-3 text-sm">
                    <button type="button" class="text-green-700 hover:underline" @click="toggle(task)">
                        {{ task.completed ? 'Reopen' : 'Complete' }}
                    </button>
                    <a :href="task.edit_url" class="text-blue-700 hover:underline">Edit</a>
                    <button type="button" class="text-red-700 hover:underline" @click="remove(task)">
                        Delete
                    </button>
                </div>
            </li>

            <li v-if="loading && tasks.length === 0" class="py-6 text-center text-gray-500">
                Loading...
            </li>
            <li v-else-if="!loading && tasks.length === 0" class="py-6 text-center text-gray-500">
                No tasks found.
            </li>
        </ul>

        <div v-if="lastPage > 1" class="mt-6 flex items-center justify-between text-sm text-gray-600">
            <button
                type="button"
                class="rounded-md bg-gray-200 px-3 py-1 font-semibold text-gray-800 hover:bg-gray-300 disabled:opacity-50"
                :disabled="page <= 1"
                @click="goToPage(page - 1)"
            >
                Previous
            </button>

            <span>Page {{ page }} of {{ lastPage }} ({{ total }} tasks)</span>

            <button
                type="button"
                class="rounded-md bg-gray-200 px-3 py-1 font-semibold text-gray-800 hover:bg-gray-300 disabled:opacity-50"
                :disabled="page >= lastPage"
                @click="goToPage(page + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>
