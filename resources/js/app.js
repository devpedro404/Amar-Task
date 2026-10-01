import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import TaskList from './components/TaskList.vue';

window.Alpine = Alpine;

Alpine.start();

const taskListElement = document.getElementById('task-list');

if (taskListElement) {
    createApp(TaskList, {
        indexUrl: taskListElement.dataset.indexUrl,
        baseUrl: taskListElement.dataset.baseUrl,
        createUrl: taskListElement.dataset.createUrl,
    }).mount(taskListElement);
}
