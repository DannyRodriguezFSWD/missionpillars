<template>
    <div class="audience">
        
                <div class="row">
                    <div class="col-sm-12">
                        <h5>How many people from the list should we send this particular message to?</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-1" style="padding-top: 10px;">
                        <input v-model="inputs.send_all.value" type="checkbox" @change="onChangeCheckbox()"> 
                        <label for="all">All or </label>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group ">
                            <input min="0" v-model="inputs.send_number_of_messages.value" :disabled="inputs.send_number_of_messages.disabled" type="number" class="form-control text-center" @input="onChangeNumberOfEmails()">
                        </div>
                    </div>
                    <div class="col-sm-1" style="padding-top: 10px;">contacts</div>
                </div>
                <p>&nbsp;</p> <p>&nbsp;</p> 
                <div class="row">
                    <div class="col-md-12">
                        <h5>Number of days since the last time receiving a message within this list</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group ">
                            <input min="0" v-model="inputs.do_not_send_within_number_of_days.value" type="number" class="form-control text-center" @input="onChangeDoNotSendWithin()">
                        </div>
                    </div>
                    <div class="col-sm-1" style="padding-top: 10px;">days</div>
                </div>
        
    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';
    export default {
        name: 'CRMCommunicationsAudience',
        data: function(){
            return {
                selected : 0,
                showModal: false,
                testPhoneNumber: "",
                message: "Enter a valid phone number",
                showMessage: false,
                loading: false,
                inputs: {
                    send_number_of_messages: {
                        value: 0,
                        disabled: false
                    },
                    do_not_send_within_number_of_days: {
                        value: 5,
                        disabled: false
                    },
                    send_all: {
                        value: true,
                        disabled: false
                    }
                }
            };
        },
        mounted() {
            this.inputs.send_all.value = this.data.audience.send_all;
            this.inputs.do_not_send_within_number_of_days.value = this.data.audience.do_not_send_within_number_of_days;
            this.inputs.send_number_of_messages.value = this.data.audience.send_number_of_messages;
            if (this.data.audience.send_all) {
                this.inputs.send_number_of_messages.disabled = true;
            }
        },
        computed: {
            ...mapState([
                'data'
            ])
        },
        methods: {
            ...mapMutations([
                'SHOW_MODAL',
                'DATA_AUDIENCE_ALL',
                'DATA_AUDIENCE_HOW_MANY_CONTACTS',
                'DATA_AUDIENCE_DAYS_SINCE_LAST_TIME'
            ]),
            ...mapActions([
                'sendTestSMSAction',
                'loadListsAction',
                'sendTestMessageAction'
            ]),
            onChangeCheckbox: function(){
                this.DATA_AUDIENCE_ALL(this.inputs.send_all.value);
                if(this.inputs.send_all.value){
                    this.inputs.send_number_of_messages.disabled = true;
                }
                else{
                    this.inputs.send_number_of_messages.disabled = false;
                }
            },
            onChangeNumberOfEmails: function(){
                this.DATA_AUDIENCE_HOW_MANY_CONTACTS(this.inputs.send_number_of_messages.value);
            },
            onChangeDoNotSendWithin: function(){
                this.DATA_AUDIENCE_DAYS_SINCE_LAST_TIME(this.inputs.do_not_send_within_number_of_days.value);
            }
        }
    }
</script>
