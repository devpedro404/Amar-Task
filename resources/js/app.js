import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import TaskList from './components/TaskList.vue';
import UserList from './components/UserList.vue';

window.Alpine = Alpine;

Alpine.start();

function mountList(elementId, component) {
    const element = document.getElementById(elementId);

    if (!element) {
        return;
    }

    createApp(component, {
        indexUrl: element.dataset.indexUrl,
        baseUrl: element.dataset.baseUrl,
        createUrl: element.dataset.createUrl,
    }).mount(element);
}

mountList('task-list', TaskList);
mountList('user-list', UserList);
