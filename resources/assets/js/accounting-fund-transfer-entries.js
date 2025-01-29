import Vue from 'vue';
import Vuex from 'vuex'
import store from './components/crm/Store';
Vue.use(Vuex)

Vue.component(
    'accounting-journal-entry-table',
    require('./components/accounting/JournalEntryTable.vue').default
)

Vue.component(
    'accounting-fund-transfer-entries',
    require('./components/accounting/FundTransfers.vue').default
)

const app = new Vue({
    el: '#main',
    store
});
