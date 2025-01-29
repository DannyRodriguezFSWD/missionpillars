import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)
import store from './components/crm/Store';

import loading from './components/Loading.vue'
import V2LazyList from 'v2-lazy-list'
Vue.use(V2LazyList)

Vue.component(
    'communication-configure-email',
    require('./components/communications/configure-email.vue').default
);

Vue.component(
    'communication-configure-confirm-email',
    require('./components/communications/confirm-email.vue').default
);

Vue.component(
    'communication-configure-finish-email',
    require('./components/communications/finish-email.vue').default
);

Vue.component(
    'communication-configure-print',
    require('./components/communications/configure-print.vue').default
);

Vue.component(
    'communication-configure-confirm-print',
    require('./components/communications/confirm-print.vue').default
);

Vue.component(
    'communication-configure-finish-print',
    require('./components/communications/finish-print.vue').default
);

const app = new Vue({
    el: '#configurecommunications',
    store,
    components: {
        loading
    },
    data: {
        currentTab: defaultTab,
        tabLabels: {
            email: 'Email Filters and Tags',
            'confirm-email': 'Confirm Email',
            'finish-email': 'Finished',
            print: 'Print Filters and Tags',
            'confirm-print': 'Confirm Print',
            'finish-print': 'Finished',
        },
        tabLinks: tabLinks
    },
    computed: {
        communicationType() {
            if (this.currentTab.indexOf('print') >= 0)
                return 'print'
            else if (this.currentTab.indexOf('email') >= 0)
                return 'email'

            return null
        },
        currentComponent() {
            return "communication-configure-" + this.currentTab
        },
        isEmail() { return this.communicationType == "email" },
        isPrint() { return this.communicationType == "print" },
        tabs() {
            switch (this.communicationType) {
                case 'print':
                    return ['print','confirm-print']
                    break;
                case 'email':
                    return ['email','confirm-email']
                    break;
                default:
                    return []
            }
        },
    },
    methods: {
        handleTab(tab, event) {
            event.preventDefault()
            this.currentTab = tab
        },
        onConfigSaved(data) {
            this.currentTab = 'confirm-'+data.communication_type
            // history.pushState(null,'',location.href.replace(/configure.*/,'configure/confirm'))
            window.scrollTo(0,0);
        },
        onConfigConfirmed(data) {
            if (data.communication_type === 'email') {
                window.location.href = data.component.$attrs.view_email_route
                return false
            }
            this.currentTab = 'finish-'+data.communication_type
            // history.pushState(null,'',location.href.replace(/configure.*/,'configure/finish'))
            window.scrollTo(0,0);
        },
        changeTab(tab) {
            this.currentTab = tab
        },
        hideLink(tab) {
            if (tab === this.currentTab) return true
            if (['finish-email','finish-print',].includes(tab)) return true
            if (['email','print'].includes(this.currentTab)) return true
            return false;
        },
    },
    mounted() {
    }
});

// window.onpopstate = function() { history.go(0) }
