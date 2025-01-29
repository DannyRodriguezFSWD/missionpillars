<template>
    <div class="sms-composer">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Select Phone Number</label>
                    <select id="sms-phone-number" class="form-control mr-4" v-model="sms_phone_number_id" v-on:change="onChangeSMSPhoneNumber()">
                        <option v-for="smsPhoneNumber in smsPhoneNumbers" :value="smsPhoneNumber.id">{{ smsPhoneNumber.name }}</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="btn-group btn-group pull-right">
                    <button type="button" class="btn btn-secondary" v-on:click="onClickSMSButton()">
                        <i class="icons icon-speech"></i>
                        Send Test Message
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>List</label>
                    <select class="form-control" v-model="list_id" v-on:change="onChangeList()">
                        <option v-for="list in lists" :value="list.id">{{ list.name }}</option>
                    </select>
                </div>
            </div>
            
            <div class="col-sm-6">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="dropdown">
                        <button class="btn btn-secondary bg-white dropdown-toggle pull-right" type="button" id="dropdownMergeCodesButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Merge Codes
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMergeCodesButton">
                            <a class="dropdown-item" href="#" v-for="code in mergeCodes" @click="addMergeCode(code)">{{ code.name }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <textarea name="sms_content_input" placeholder="Compose message" class="form-control" rows="5" v-model="content" v-on:keyup="onChangeContent()" :maxlength="sms_char_limit" @keyup="countChar($event)"></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remove_stop_to_unsubscribe" @change="onChangeRemoveStop" v-model="remove_stop_to_unsubscribe">
                    <label class="form-check-label cursor-pointer" for="remove_stop_to_unsubscribe">
                        Remove "STOP to unsubscribe" <i class="fa fa-question-circle-o text-info" data-toggle="tooltip" data-placement="right" title='Remove the "STOP to unsubscribe" message that we include at the bottom of the text. But replying "STOP" will still unsubscribe them.'></i>
                    </label>
                    
                    <span class="pull-right small text-muted"><span data-char-limit="sms_content_input">0</span>/{{ sms_char_limit }} characters</span>
                </div>
            </div>
        </div>

        <CRMModal v-if="showModal" @close="showModal = false">
            <h3 slot="header">Enter receiver phone number</h3>
            <div slot="body" class="form-group">
                <input type="text" class="form-control" v-model="testPhoneNumber" v-on:keyup="onChangeTestPhoneNumber()" placeholder="example: +19511234567"/>
                <p v-if="showMessage" class="text-danger">{{ message }}</p>
            </div>
            <div class="row" slot="footer">
                <div class="col-sm-6">
                    <button class="modal-default-button btn btn-secondary" @click="showModal = false">
                        Cancel
                    </button>
                </div>
                <div class="col-sm-6">
                    <button class="modal-default-button btn btn-primary text-nowrap" v-on:click="onClickSendSMSButton()">
                        Send SMS
                    </button>
                </div>
            </div>
        </CRMModal>
    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';
    import CRMModal from '../../crm-modal.vue';

    export default {
        name: 'CRMCommunicationsSMSComposer',
        props: {
            sms_char_limit: String
        },
        data: function(){
            return {
                sms_phone_number_id: 0,
                list_id: 0,
                content: '',
                showModal: false,
                testPhoneNumber: '',
                showMessage: false,
                message: 'Enter a phone number to send a test SMS',
                remove_stop_to_unsubscribe: false,
                mergeCodes: mergeTags
            };
        },
        components: {
            CRMModal
        },
        mounted() {
            //this.loadListsAction(this.$store.state);
            this.content = this.$store.state.data.message.content;
            this.sms_phone_number_id = this.$store.state.data.message.sms_phone_number_id;
            this.list_id = this.$store.state.data.message.list_id;
            this.remove_stop_to_unsubscribe = this.$store.state.data.message.remove_stop_to_unsubscribe;
            $('[data-toggle="tooltip"]').tooltip();
        },
        computed: {
            ...mapState([
                'smsPhoneNumbers',
                'lists'
            ])
        },
        methods: {
            ...mapMutations([
                'SHOW_MODAL',
                'DATA_MESSAGE_SMS_PHONE_NUMBER_ID',
                'DATA_MESSAGE_LIST_ID',
                'DATA_MESSAGE_CONTENT',
                'DATA_MESSAGE_TEST',
                'DATA_MESSAGE_TEST_PHONE_NUMBER',
                'DATA_MESSAGE_REMOVE_STOP_TO_UNSUBSCRIBE'
            ]),
            ...mapActions([
                'sendTestSMSAction',
                'loadSMSPhoneNumbersAction',
                'loadListsAction',
                'sendTestMessageAction',
                'setStateFromSMSContent',
            ]),
            onChangeContent: function(){
                this.DATA_MESSAGE_CONTENT(this.content);
            },
            onChangeSMSPhoneNumber: function () {
                this.DATA_MESSAGE_SMS_PHONE_NUMBER_ID(this.sms_phone_number_id);
            },
            onChangeList: function(){
                this.DATA_MESSAGE_LIST_ID(this.list_id);
            },
            onChangeTestPhoneNumber: function(){
                this.DATA_MESSAGE_TEST_PHONE_NUMBER(this.testPhoneNumber);
            },
            onClickSMSButton: function(){

                if(this.content.trim() == ''){
                    Swal.fire('Write the message to send', '', 'error')
                    return false;
                }
                this.showModal = true;

            },
            onClickSendSMSButton: function(){
                this.showMessage = false;
                if(this.testPhoneNumber.trim() == ''){
                    this.showMessage = true;
                    return false;
                }

                var phone = this.testPhoneNumber.trim();
                var prefix = phone.substring(0, 1);
                if( prefix != '+1' ){
                    phone = '+1'+phone;
                    this.DATA_MESSAGE_TEST_PHONE_NUMBER(phone);
                }

                this.showModal = false;
                this.sendTestMessageAction(this.$store.state);
            },
            onChangeRemoveStop: function () {
                this.DATA_MESSAGE_REMOVE_STOP_TO_UNSUBSCRIBE(this.remove_stop_to_unsubscribe)
            },
            addMergeCode: function (code) {
                this.content = this.content + code.code;
                this.onChangeContent();
            },
            countChar: function (event) {
                countChar(event.target, this.sms_char_limit);
            }
        }
    }
</script>
