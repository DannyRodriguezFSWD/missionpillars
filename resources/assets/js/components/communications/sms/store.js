import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    //strict: true,
    state: {
        notifications: {
            email: {
                address: '',
                valid: false
            },
            phone: {
                number: '',
                valid: false
            },
            contacts: {
                list: '',
                valid: false
            }
        },
        type: false,
        target: false,
        show: false,
        hide: false,
        task: 'mass-sms',
        loading: false,
        baseUrl: '',
        areaCode: '',
        phoneNumber: 'false',
        hasPhoneNumber: 'false',
        step: 1,
        captionNextButton: 'Next',
        captionPrevButton: 'Previous',
        modal: {
            show: false,
            header: 'Alert',
            body: ''
        },
        phoneNumbers: [],
        smsPhoneNumbers: [],
        lists: [],
        treeData:[],
        folders: [],
        data: {
            id: 0,
            page: 1,
            pages: 1,
            type: 'sms',
            audience:{
                send_all: true,
                send_number_of_messages: 1,
                do_not_send_within_number_of_days: 5
            },
            message: {
                test: false,
                test_phone_number: '',
                sms_phone_number_id: 0,
                list_id: 0,
                datatable_state_id: 0,
                content: ''
            },
            tags: {
                include: [],
                exclude: []
            },
            actions: [
                {
                    input: "message_sent",
                    text: "Message sent",
                    tag: 0
                },
                {
                    input: "message_delivered",
                    text: "Message delivered",
                    tag: 0
                },
                {
                    input: "message_undelivered",
                    text: "Message undelivered",
                    tag: 0
                },
                {
                    input: "message_failed",
                    text: "Message failed",
                    tag: 0
                }
            ]
        },
        summary: {}

    },
    mutations: {//sync
        SHOW_MODAL: (state, modal) => {
            state.modal = modal;
        },
        WINDOW_LOCATION_HREF: (state, value) => {
            window.location.href = state.baseUrl+value;
        },
        IS_LOADING: (state, loading) => {
            state.loading = loading;
        },
        NEXT: (state, value) => {
            if(value == null){
                state.step++;
            }
            else{
                state.step = value;
            }
        },
        PREV: (state, value) => {
            if(value == null){
                state.step--;
            }
            else{
                state.step = value;
            }
        },
        INIT_ACTION: (state, settings) => {
            state.baseUrl = settings.base;
            // JUST IN CASE, remove any trailing folder in the path
            state.baseUrl = state.baseUrl.replace(/crm\/.*/,'crm/')
            state.phoneNumber = settings.phone;
            if(settings.hasSmsPhoneNumber == 'true'){
                state.hasPhoneNumber = 'true';
                state.data.message.sms_phone_number_id = settings.defaultSmsPhoneNumberId;
            }
            else{
                state.type = settings.type;
                state.target = settings.target;
                state.show = settings.show;
                state.hide = settings.hide;
            }
        },
        ON_CHANGE_TASK_MODEL: (state, task) => {
            state.task = task;
        },
        ON_CHANGE_PHONE_NUMBER_MODEL: (state, phone) => {
            state.phoneNumber = phone;
        },
        ON_CHANGE_PHONE_AREA_CODE_MODEL: (state, code) => {
            state.areaCode = code;
        },
        STATE_NOTIFICATIONS: (state, value) => {
            state.notifications = value;
        },
        STATE_CAPTION_NEXT_BUTTON: (state, value) => {
            state.captionNextButton = value;
        },
        STATE_CAPTION_PREV_BUTTON: (state, value) => {
            state.captionPrevButton = value;
        },
        STATE_PHONE_NUMBERS: (state, phones) => {
            state.phoneNumbers = phones;
        },
        STATE_HAS_PHONE_NUMBER: (state, value) => {
            state.hasPhoneNumber = value;
        },
        STATE_SMS_PHONE_NUMBERS: (state, smsPhoneNumbers) => {
            state.smsPhoneNumbers = smsPhoneNumbers;
        },
        STATE_LISTS: (state, lists) => {
            // console.log('STATE_LISTS', lists, state.data.message.list_id );
            state.lists = lists;
            // not sure if this should be set here
            // state.data.message.list_id = 0;
        },
        STATE_ACTIONS: (state, actions) => {
            state.actions = actions;
        },
        STATE_TAGS: (state, tags) => {
            state.data.tags = tags;
        },
        STATE_TREE_DATA: (state, data) => {
            state.treeData = data;
        },
        STATE_FOLDERS: (state, data) => {
            state.folders = data;
        },
        STATE_SUMMARY: (state, data) => {
            state.summary = data;
        },
        DATA_MESSAGE_TEST_MODE: (state, value) => {
            state.data.message.test = value;
        },
        DATA_MESSAGE_SMS_PHONE_NUMBER_ID: (state, value) => {
            state.data.message.sms_phone_number_id = value;
        },
        DATA_MESSAGE_LIST_ID: (state, value) => {
            state.data.message.list_id = value;
        },
        DATA_MESSAGE_DATATABLE_STATE_ID: (state, value) => {
            state.data.message.datatable_state_id = value;
        },
        DATA_MESSAGE_CONTENT: (state, value) => {
            state.data.message.content = value;
        },
        DATA_MESSAGE_TEST_PHONE_NUMBER: (state, value) => {
            state.data.message.test_phone_number = value;
        },
        DATA_MESSAGE_REMOVE_STOP_TO_UNSUBSCRIBE: (state, value) => {
            state.data.message.remove_stop_to_unsubscribe = value;
        },
        DATA_MESSAGE_TIME_SCHEDULED: (state, value) => {
            state.data.message.time_scheduled = value;
        },
        DATA_AUDIENCE_ALL: (state, value) =>{
            state.data.audience.send_all = value;
        },
        DATA_AUDIENCE_HOW_MANY_CONTACTS: (state, value) => {
            state.data.audience.send_number_of_messages = value;
        },
        DATA_AUDIENCE_DAYS_SINCE_LAST_TIME: (state, value) => {
            state.data.audience.do_not_send_within_number_of_days = value;
        },
        DATA_PAGE: (state, value) => {
            if(!value){
                state.data.page++;
            }
            else{
                state.data.page = value;
            }
        },
        DATA_PAGES: (state, value) => {
            state.data.pages = value;
        },
        DATA_ID: (state, value) => {
            state.data.id = value;
        },
        DATA_INIT: (state, value) => {
            state.data = value;
        }
    },
    actions: {//async
        showModalAction: ({commit}, modal) => {
            commit('SHOW_MODAL', modal);
        },
        nextAction: ({commit, dispatch,state}, payload) => {
            if(state.hasPhoneNumber == 'false'){
                //get database tags, lists and sms phones
                dispatch('loadSMSPhoneNumbersAction', payload);
                dispatch('loadListsAction', payload);
                dispatch('getTreeData', payload);
                commit('NEXT', 1);
                return
            }
            else if(payload.phoneNumber != 'false' && payload.step == 1 && state.hasPhoneNumber != 'false'){
                //get database tags, lists and sms phones
                dispatch('loadSMSPhoneNumbersAction', payload);
                dispatch('loadListsAction', payload);
                dispatch('getTreeData', payload);
                commit('NEXT', 2);
                return;

            }
            else if(payload.step == 2){
                if(payload.data.message.content.trim() == ''){
                    Swal.fire('Message cannot be empty','Write the message to send','info')
                    return false;
                }
                else{
                    //save message
                    dispatch('setInDatabase', payload).then(response => {
                        commit('NEXT', 3);
                    });
                }
            }
            else if(payload.step == 3){
                dispatch('setInDatabase', payload).then(response => {
                    commit('STATE_CAPTION_NEXT_BUTTON', 'Finish and send SMS');
                    commit('NEXT', 4);
                });
                return;
            }
            else if(payload.step == 4){
                if (payload.summary.in.length === 0) {
                    Swal.fire('Error', 'No contacts match your filters, unable to send this text to anyone', 'error')
                    return false;
                }
                
                let url = payload.baseUrl + 'communications/sms/send';
                commit('IS_LOADING', true);
                axios.post(url, {data: payload.data}).then((response) => {
                    commit('IS_LOADING', false);
                    commit('WINDOW_LOCATION_HREF', 'communications/sms/'+payload.data.id+'?created=true')
                    return;
                }).catch(error => {
                    console.log(error)
                });;

                return;
            }
        },
        prevAction: ({commit}, payload) => {
            let value = null;
            commit('DATA_PAGE', 1);
            commit('STATE_CAPTION_NEXT_BUTTON', 'Next');
            commit('PREV', value);
        },
        initAction: (context, settings) => {
            context.commit('INIT_ACTION', settings);
        },
        loadSMSPhoneNumbersAction: ({commit, state}) => {
            let url = '/crm/settings/sms';
            commit('IS_LOADING', true);
            axios.get(url).then((response) => {
                commit('IS_LOADING', false);
                commit('STATE_SMS_PHONE_NUMBERS', response.data);
            });
        },
        loadListsAction: ({commit}, payload) => {
            //if(payload.phoneNumber != 'false'){
                // let url = payload.baseUrl + 'communications/sms/../lists';
                let url = '/crm/lists';
                commit('IS_LOADING', true);
                axios.get(url,{params: {list_id: payload.data.message.list_id}}).then((response) => {
                    commit('IS_LOADING', false);
                    commit('STATE_LISTS', response.data);
                });
            //}
        },
        searchAvailablePhoneNumbersAction: ({commit}, payload) => {
            if(payload.code.trim() == ''){
                Swal.fire('Please input area code','','info')
            }
            else{
                let url = payload.baseUrl + 'communications/sms/phonenumber/showavailable';
                let params = { params: {code: payload.code} };
                commit('IS_LOADING', true);

                axios.get(url, params).then((response) => {
                    commit('IS_LOADING', false);
                    if(response.data.length <= 0){
                        Swal.fire('No available phone numbers','There are no available phone numbers for this area code','info')
                        return;
                    }
                    commit('STATE_PHONE_NUMBERS', response.data);
                });
            }
        },
        sendTestMessageAction: ({commit}, payload) => {
            let url = payload.baseUrl + 'communications/sms/test';
            commit('IS_LOADING', true);
            commit('DATA_MESSAGE_TEST_MODE', true);
            axios.post(url, payload.data.message).then((response) => {
                commit('IS_LOADING', false);
                commit('DATA_MESSAGE_TEST_MODE', false);
                Swal.fire("Success!", 'Message sent!', "success")
            }).catch(err => {
                commit('IS_LOADING', false);
                let err_message = `${err.response.data.message} 
                Please contact <a href="https://support.continuetogive.com/Tickets/Create" target="_blank">customer support <i class="fa fa-external-link-square"></i></a> 
                <p>Or email us at <a href="mailto:customerservice@continuetogive.com">customerservice@continuetogive.com</a></p>`
                Swal.fire("Oops something wrong happened.", err_message, "error")
            });
        },
        getTreeData: ({commit}, payload) => {
            // let url = payload.baseUrl + 'communications/sms/folders';
            let url = '/crm/folders';
            commit('IS_LOADING', true);
            axios.get(url).then((response) => {
                commit('IS_LOADING', false);
                commit('STATE_TREE_DATA', response.data.tree);
                commit('STATE_FOLDERS', response.data.folders);
            });
        },
        buyPhoneNumberAction: ({commit,dispatch}, data) => {
            let standAloneComponent = data.standAloneComponent === 'true'
            let payload = data.payload;
            let notifications = data.notifications;

            if(payload.areaCode.trim() == ''){
                Swal.fire('Please input area code','','info')
                return;
            }

            if(payload.phoneNumber == 'false'){
                Swal.fire('You have to select a phone number first','','info')
                return;
            }
            
            if (data.name == '') {
                Swal.fire('You have to add a phone label first', '', 'info')
                return;
            }
            
            if (notifications.contacts.length === 0) {
                Swal.fire('You have to select at least one contact first', '', 'info')
                return;
            }
            
            let url = payload.baseUrl + 'communications/sms/phonenumber/buy';
            let params = {
                params: {
                    phone: payload.phoneNumber.phoneNumber,
                    name: data.name,
                    contacts: notifications.contacts
                }
            };
            commit('IS_LOADING', true);

            axios.get(url, params).then((response) => {
                commit('IS_LOADING', false);
                    commit('STATE_HAS_PHONE_NUMBER', true);
                    Swal.fire("Success!",payload.phoneNumber.phoneNumber+' was added to your account successfully.',"success")

                    if (!standAloneComponent) window.location.href = '/crm/communications/sms/create';

                    if(payload.type == 'modal'){
                        $(payload.target).modal('hide');
                        $(payload.hide).hide();
                        $(payload.show).show();
                    }

                    if(payload.type == 'redirect'){
                        window.location.href = payload.target;
                    }
            }).catch(({response}) => {
                commit('IS_LOADING', false);
                let err_message = `${response.data.message} 
                Please contact <a href="https://support.continuetogive.com/Tickets/Create" target="_blank">customer support <i class="fa fa-external-link-square"></i></a> 
                <p>Or email us at <a href="mailto:customerservice@continuetogive.com">customerservice@continuetogive.com</a></p>`
                Swal.fire("Oops something wrong happened.",err_message,"error")

            });

        },
        responseReaction: ({commit}, payload) => {
            if(payload.type == 'modal'){
                $(payload.target).modal('hide');
            }

            if(payload.type == 'redirect'){
                window.location.href = payload.target;
            }

            if(payload.hide){
                $(payload.hide).hide();
            }

            if(payload.show){
                $(payload.show).show();
            }
        },
        getSummary: ({commit}, payload) => {
            return new Promise((resolve, reject) => {
                let url = payload.baseUrl + 'communications/sms/previewsummary';
                commit('IS_LOADING', true);
                axios.post(url, {data: payload.data}).then((response) => {
                    commit('IS_LOADING', false);
                    commit('STATE_SUMMARY', response.data);

                    resolve(response.data)
                }).catch(error => {
                    console.log(error)
                });
            })
        },
        setInDatabase: ({commit}, payload) => {
            return new Promise((resolve, reject) => {
                let url = payload.baseUrl + 'communications/sms/store';
                commit('IS_LOADING', true);
                axios.post(url, {data: payload.data}).then((response) => {
                    commit('IS_LOADING', false);
                    commit('DATA_ID', response.data.id);
                    resolve(response.data)
                }).catch(error => {
                    console.log(error)
                });
            })
        },
        setStateFromSMSContent: ({commit}, smscontent) => {
            // console.log('setStateFromSMSContent');
            commit('DATA_MESSAGE_SMS_PHONE_NUMBER_ID', smscontent.sms_phone_number_id ? smscontent.sms_phone_number_id : 0);
            commit('DATA_MESSAGE_LIST_ID', smscontent.list_id ? smscontent.list_id : 0 );
            commit('DATA_MESSAGE_DATATABLE_STATE_ID', 
                smscontent.list_id & smscontent.list.datatable_state_id 
                ? smscontent.list.datatable_state_id : 0 );
            commit('DATA_MESSAGE_CONTENT', smscontent.content ? smscontent.content : '' );
            commit('DATA_AUDIENCE_ALL', smscontent.send_to_all ? smscontent.send_to_all : false );
            commit('DATA_AUDIENCE_HOW_MANY_CONTACTS', smscontent.send_number_of_messages ? smscontent.send_number_of_messages : 1 );
            commit('DATA_AUDIENCE_DAYS_SINCE_LAST_TIME', smscontent.do_not_send_within_number_of_days ? smscontent.do_not_send_within_number_of_days : 5 );
        },
    },
    getters: {
        selectedTask: state => {
            return state.task;
        },
        getMessage: state => {
            return state.data.message;
        }
    },
});
