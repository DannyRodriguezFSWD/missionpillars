// Import required CSS


import Vue from 'vue'
import Vuex from 'vuex'
import store from './components/crm/Store'
Vue.use(Vuex)

// Import components
import MpVueTable from './components/MpVueTable/MpVueTable'
import Loading from './components/Loading.vue'
import TasksList from './components/Tasks/TasksList.vue'
import TaskEditModal from './components/Tasks/TaskEditModal.vue'

// Register components
Vue.component('mp-vue-table', MpVueTable)
Vue.component('loading', Loading)
Vue.component('tasks-list', TasksList)
Vue.component('task-edit-modal', TaskEditModal)

const app = new Vue({
    el: '#crm-tasks',
    store
}) 