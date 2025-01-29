import Vue from 'vue'
import Multiselect from "vue-multiselect";

import store from './components/crm/Store';

Vue.component('contact-search-filter',require('./components/ContactSearchFilter').default);
Vue.component('contact-search-add-remove-tags',require('./components/ContactSearchAddRemoveTag').default);

const app = new Vue({
    el: '#advance_contact_search',
    store,
});
