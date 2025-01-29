require('vue-flash-message/src/FlashMessage.css');
import Vue from 'vue';
import VueFlashMessage from 'vue-flash-message';
import Vuex from 'vuex'
import store from './components/crm/Store';

Vue.use(VueFlashMessage)
Vue.use(Vuex)

Vue.component(
    'accounting-journal-entries',
    require('./components/accounting/JournalEntries.vue').default
);

const app = new Vue({
    el: '#main',
    store
});
