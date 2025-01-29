import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

import store from './components/crm/Store';
import LiquorTree from 'liquor-tree'
Vue.use(LiquorTree)

import 'beautify-scrollbar/dist/index.css';
import V2LazyList from 'v2-lazy-list';
Vue.use(V2LazyList);



Vue.component(
    'crm-reports-components',
    require('./components/crm/reports/crm_reports_components.vue').default
);

const app = new Vue({
    el: '#crm-reports-viewport',
    store
});