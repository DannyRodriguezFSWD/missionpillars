<template>
    <div class="crm-communications-viewport">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-6">
                    <button v-show="step > 2" type="button" class="btn btn-secondary" v-on:click="prev()" v-if="step > 1">
                            <span class="icon-arrow-left"></span>
                            {{ captionPrevButton }}
                        </button>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button v-show="step == 4" type="button" class="btn btn-success" v-on:click="pickDateAndTime">
                            <i class="fa fa-calendar"></i> Schedule and send later
                        </button>
                    
                        <button v-show="(step == 1 ? hasPhoneNumber !== 'false' : true)" type="button" class="btn btn-primary" v-on:click="next()">
                            {{ captionNextButton }}
                            <span class="icon-arrow-right"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <CRMCommunicationsBuyPhoneNumber v-if="step == 1"
                :current-contacts="currentContacts"
                :data-target="dataTarget"
                :url="url"
                :phone="phoneData"
                :id="id"
                ></CRMCommunicationsBuyPhoneNumber>
                <CRMCommunicationsSMSComposer v-if="step == 2" :sms_char_limit="sms_char_limit" />
                <CRMCommunicationsSMSSettings v-if="step == 3" />
                <CRMCommunicationsSummary v-if="step == 4"/>
            </div>

            <div class="card-footer">
                &nbsp;
            </div>
        </div>

        <div id="overlay" v-if="loading">
            <div class="spinner">
                <div class="rect1"></div>
                <div class="rect2"></div>
                <div class="rect3"></div>
                <div class="rect4"></div>
                <div class="rect5"></div>
            </div>
        </div>

        <CRMModal v-if="modal.show && step != 1">
            <h3 slot="header">{{ modal.header }}</h3>
            <div slot="body">{{ modal.body }}</div>
            <div slot="footer">
                <button class="modal-default-button btn btn-secondary" v-on:click="showModalAction({ show: false })">
                Close
                </button>
            </div>
        </CRMModal>
        
        <div class="modal fade" tabindex="-1" id="pickDateAndTimeModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Pick Date & Time</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row" v-if="clientTimezone">
                            <div class="col-12 text-center">
                                <p>Timezone: <b>{{ clientTimezone }}</b></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group justify-content-center">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Date</span>
                                        </div>
                                        <input v-model="date_scheduled" name="date_scheduled" id="date_scheduled" class="form-control" type="date" :min="new Date().toISOString().split('T')[0]" style="max-width: 235px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group justify-content-center">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Time</span>
                                        </div>
                                        <input v-model="hour_scheduled" name="hour_scheduled" id="hour_scheduled" class="form-control" type="number" min="1" max="12" style="max-width: 70px;">
                                        <div class="input-group-append">
                                            <span class="input-group-text">:</span>
                                        </div>
                                        <input v-model="minute_scheduled" name="minute_scheduled" id="minute_scheduled" class="form-control" type="number" min="0" max="59" style="max-width: 70px;">
                                        <select v-model="am_pm_scheduled" name="am_pm_scheduled" id="am_pm_scheduled" class="form-control" style="max-width: 70px; background-color: #f0f3f5;">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" @click="scheduleSend">Schedule Send</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';
    import CRMCommunicationsBuyPhoneNumber from './crm-buy-phone-number.vue';
    import CRMCommunicationsSMSComposer from './crm-sms-composer.vue';
    import CRMCommunicationsSMSSettings from './crm-sms-settings.vue';
    import CRMCommunicationsSummary from './crm-communications-summary.vue';
    import CRMModal from '../../crm-modal.vue';

    export default {
        name: 'CRMCommunicationsViewport',
        props: {
            hasSmsPhoneNumber: String,
            defaultSmsPhoneNumberId: String,
            smsPhoneNumber: String, 
            baseUrl: String,
            dataType: String,
            dataTarget: String,
            standAloneComponent: String,
            currentContacts: String,
            phoneData: String,
            url: String,
            id: String,
            sms: {},
            sms_char_limit: String
         },
        data: function(){
            return {
                clientTimezone: null,
                dateAndTimeError: null,
                date_scheduled: null,
                hour_scheduled: '12',
                minute_scheduled: '0',
                am_pm_scheduled: 'PM',
                isScheduled: false,
                time_scheduled: null
            }
        },
        components: {
            CRMCommunicationsBuyPhoneNumber,
            CRMCommunicationsSMSComposer,
            CRMCommunicationsSMSSettings,
            CRMCommunicationsSummary,
            CRMModal
        },
        mounted: function(){
            if (this.sms && this.sms.id) this.setStateFromSMSContent(this.sms);
            this.init(this.baseUrl, this.smsPhoneNumber, this.hasSmsPhoneNumber, this.defaultSmsPhoneNumberId);
            if (this.hasPhoneNumber) this.next();
            this.setClientTimezone();
            this.time_scheduled = this.$store.state.data.message.time_scheduled;
        },
        computed: {
            ...mapState([
                'step',
                'loading',
                'task',
                'modal',
                'phoneNumber',
                'captionNextButton',
                'hasPhoneNumber',
                'captionPrevButton'
            ])
        },
        methods: {
            ...mapMutations([
                'DATA_MESSAGE_TIME_SCHEDULED'
            ]),
            ...mapActions([
                'nextAction',
                'prevAction',
                'showModalAction',
                'initAction',
                'setStateFromSMSContent',
            ]),
            prev: function(){
                this.prevAction(this.$store.state);
            },
            next: function(){
                this.time_scheduled = this.isScheduled ? this.getFullDateAndTime() : null;
                console.log(this.isScheduled);
                console.log(this.time_scheduled);
                
                
                this.DATA_MESSAGE_TIME_SCHEDULED(this.time_scheduled);
                this.isScheduled = false;
                this.nextAction(this.$store.state);
            },
            init: function(b, p, hasSmsPhoneNumber, defaultSmsPhoneNumberId){
                let settings = {
                    base: b,
                    phone: p,
                    hasSmsPhoneNumber: hasSmsPhoneNumber,
                    defaultSmsPhoneNumberId: defaultSmsPhoneNumberId
                };
                this.initAction(settings);
            },
            setClientTimezone() {
                if (Intl) {
                    if (Intl.DateTimeFormat()) {
                        if (Intl.DateTimeFormat().resolvedOptions()) {
                            if (Intl.DateTimeFormat().resolvedOptions().timeZone) {
                                this.clientTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                            }
                        }
                    }
                }
            },
            pickDateAndTime: function () {
                $('#pickDateAndTimeModal').modal('show');
            },
            scheduleSend() {
                this.validateDateAndTime();
                if (this.dateAndTimeError) {
                    return Swal.fire(this.dateAndTimeError, '', 'error');
                }

                this.isScheduled = true;
                this.next();
            },
            validateDateAndTime() {
                if (!this.date_scheduled || !this.hour_scheduled || !this.minute_scheduled || !this.am_pm_scheduled) {
                    this.dateAndTimeError = 'Please enter a correct date and time'
                    return false;
                }

                if (this.hour_scheduled && (this.hour_scheduled > 12 || this.hour_scheduled < 1)) {
                    this.dateAndTimeError = 'Please enter a correct hour'
                    return false;
                }
                
                if (this.minute_scheduled && (this.minute_scheduled > 59 || this.minute_scheduled < 0)) {
                    this.dateAndTimeError = 'Please enter a correct minute'
                    return false;
                }

                var now = new Date();
                var date = new Date(this.getFullDateAndTime());

                if (now.getTime() > date.getTime()) {
                    this.dateAndTimeError = 'Please enter a future date'
                    return false;
                }

                this.dateAndTimeError = null;
            },
            getFullDateAndTime() {
                var hour = 12;

                if (this.am_pm_scheduled === 'AM') {
                    if (this.hour_scheduled == 12) {
                        hour = '00';
                    } else {
                        hour = this.hour_scheduled;
                    }
                } else {
                    if (this.hour_scheduled == 12) {
                        hour = this.hour_scheduled;
                    } else {
                        hour = parseInt(this.hour_scheduled) + 12;
                    }
                }

                if (hour.length === 1) {
                    hour = '0' + hour;
                }

                var minute = this.minute_scheduled.length === 2 ? this.minute_scheduled : '0' + this.minute_scheduled;

                return this.date_scheduled + ' ' + hour + ':' + minute;
            },
        }
    }
</script>
<style scoped>
    #overlay{ display: block; }
</style>
