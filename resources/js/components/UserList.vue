<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    indexUrl: { type: String, required: true },
    baseUrl: { type: String, required: true },
    createUrl: { type: String, required: true },
});

const users = ref([]);
const search = ref('');
const page = ref(1);
const lastPage = ref(1);
const total = ref(0);
const loading = ref(false);
const message = ref('');
const error = ref('');

let debounceTimer = null;
let latestRequest = 0;

async function loadUsers() {
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

        users.value = data.data;
        page.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } catch (e) {
        error.value = 'Could not load users. Please try again.';
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
        loadUsers();
    }, 300);
});

function goToPage(targetPage) {
    if (targetPage < 1 || targetPage > lastPage.value) {
        return;
    }

    page.value = targetPage;
    loadUsers();
}

async function remove(user) {
    if (!confirm('Delete this user and all of their tasks?')) {
        return;
    }

    error.value = '';

    try {
        await axios.delete(`${props.baseUrl}/${user.id}`);
        message.value = 'User deleted successfully.';

        if (users.value.length === 1 && page.value > 1) {
            page.value -= 1;
        }

        await loadUsers();
    } catch (e) {
        error.value = 'Could not delete the user.';
    }
}

onMounted(loadUsers);
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
                placeholder="Search users..."
                class="rounded-md border-gray-300 shadow-sm"
            >

            <a
                :href="createUrl"
                class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
            >
                New user
            </a>
        </div>

        <ul class="divide-y divide-gray-200">
            <li
                v-for="user in users"
                :key="user.id"
                class="flex items-center justify-between gap-4 py-4"
            >
                <div>
                    <p class="font-medium text-gray-900">
                        <a :href="user.show_url" class="hover:underline">{{ user.name }}</a>
                        <span
                            v-if="user.is_current"
                            class="ml-2 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600"
                        >
                            You
                        </span>
                    </p>
                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                </div>

                <div class="flex shrink-0 items-center gap-3 text-sm">
                    <a :href="user.edit_url" class="text-blue-700 hover:underline">Edit</a>
                    <button
                        v-if="!user.is_current"
                        type="button"
                        class="text-red-700 hover:underline"
                        @click="remove(user)"
                    >
                        Delete
                    </button>
                </div>
            </li>

            <li v-if="loading && users.length === 0" class="py-6 text-center text-gray-500">
                Loading...
            </li>
            <li v-else-if="!loading && users.length === 0" class="py-6 text-center text-gray-500">
                No users found.
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

            <span>Page {{ page }} of {{ lastPage }} ({{ total }} users)</span>

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
