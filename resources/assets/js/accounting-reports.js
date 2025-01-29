import Vue from 'vue';
import Vuex from 'vuex'
import VCalendar from 'v-calendar'
Vue.use(Vuex)
Vue.use(VCalendar)
import store from './components/crm/Store';
import Datepicker from "vuejs-datepicker";
Vue.component('datepicker',Datepicker)
Vue.filter('formatDate',function(date){
    if (date) return moment(date).format('YYYY-MM-DD')
})
Vue.component(
    'compare-balance-sheet-by-fund',
    require('./components/accounting/reports/CompareBalanceSheetByFund.vue').default
);

Vue.component(
    'balance-sheet',
    require('./components/accounting/reports/BalanceSheet.vue').default
);

Vue.component(
    'income-statement',
    require('./components/accounting/reports/IncomeStatement.vue').default
);

Vue.component(
    'income-statement-by-month',
    require('./components/accounting/reports/IncomeStatementByMonth.vue').default
)

Vue.component(
    'income-statement-by-fund',
    require('./components/accounting/reports/IncomeStatementByFund.vue').default
)

const app = new Vue({
    el: '#main',
    store
});
