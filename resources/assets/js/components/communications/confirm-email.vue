<template>
  <div>
    <div class="row justify-content-center">
      <div class="col-sm-12">
        <h5>This email will include</h5>
        <ul>
          <li v-if="!communication.send_number_of_emails">all contacts</li>
          <li v-else>up to {{ communication.send_number_of_emails }} contacts</li>
          <li v-if="communication.lists && communication.lists.datatable_state_id">
            from <b><a :href="saved_search_route+'/'+communication.lists.datatable_state_id"
                       target="_blank">{{ communication.lists.name }}</a></b> saved search
          </li>
          <li v-else-if="communication.lists">
            from <b>{{ communication.lists.name }}</b>
            <em>( contacts
              <span v-if="communication.lists.in_tags.length">
                                tagged with
                                <b v-for="(tag, i) in communication.lists.in_tags">
                                    <span v-if="i>0">, </span><span
                                    v-if="tag.folder_id != 1">{{ tag.folder.name }}\</span>{{ tag.name }}
                                </b>
                            </span>
              <span v-if="communication.lists.in_tags.length && communication.lists.not_in_tags.length"> and </span>
              <span v-if="communication.lists.not_in_tags.length">
                                not tagged with
                                <b v-for="(tag, i) in communication.lists.not_in_tags">
                                    <span v-if="i>0">, </span><span
                                    v-if="tag.folder_id != 1">{{ tag.folder.name }}\</span>{{ tag.name }}
                                </b>
                            </span>)
            </em>
          </li>
          <li v-if="communication.do_not_send_within_number_of_days">that have not been emailed in this list recently
            <em>(within the last {{ communication.do_not_send_within_number_of_days }} days)</em></li>
          <li v-if="communication.do_not_send_to_previous_receivers">that have not received this email</li>
          <li v-if="communication.email_exclude_printed">that have not received this communication as print</li>
          <li v-if="communication.include_tags.length">
                        <span v-if="communication.include_tags.length">
                            <em>AND</em> also tagged with
                        </span>
            <b v-for="(tag, i) in communication.include_tags">
              <span v-if="i>0">, </span><span v-if="tag.folder_id != 1">{{ tag.folder.name }}\</span>{{ tag.name }}
            </b>
          </li>

          <li v-if="communication.exclude_tags.length">
            that are not tagged with
            <b v-for="(tag, i) in communication.exclude_tags">
              <span v-if="i>0">, </span><span v-if="tag.folder_id != 1">{{ tag.folder.name }}\</span>{{ tag.name }}
            </b>
          </li>

          <!-- TRANSACTIONS -->
          <li v-if="communication.include_transactions">
            that have tax deductible transactions between
            <b>{{ moment(communication.transaction_start_date).format('M/D/YYYY') }}</b> and
            <b>{{ moment(communication.transaction_end_date).format('M/D/YYYY') }}</b> <span
              v-if="communication.exclude_acknowledged_transactions || communication.transaction_tags.length || communication.excluded_transaction_tags.length"> that </span>
            <ul>
              <li v-if="communication.exclude_acknowledged_transactions">are NOT acknowleged</li>
              <li v-if="communication.transaction_tags.length">
                are tagged with
                <b v-for="(tag, i) in communication.transaction_tags">
                  <span v-if="tag.folder_id != 1">{{ tag.folder.name }}\</span>{{ tag.name }}
                </b>
              </li>
              <li v-if="communication.excluded_transaction_tags.length">
                are NOT tagged with
                <b v-for="(tag, i) in communication.excluded_transaction_tags">
                  <span v-if="tag.folder_id != 1">{{ tag.folder.name }}\</span>{{ tag.name }}
                </b>
              </li>
            </ul>
          </li>

        </ul>
      </div>
    </div>
    <div class="col-12">
      <div class="row" id="summary_wrapper">
        <div class="col-md-4 card p-0">
          <div class="card-header bg-white">
            <h5 class="card-title" style="font-size: larger">Contacts who <strong>will</strong> receive this email ({{ summary.totalIncluded }} total)</h5>
          </div>
          <div style="max-height: 15em; overflow: hidden auto"@scroll="onScroll">
            <table class="table table-sm table-striped">
              <tbody>
              <tr v-for="contact in summary.contacts.data">
                <td>
                  {{ `${contact.full_name}` }}
                  <span class="text-smaller">
                            ({{ formatEmail(contact.email_1) }})
                        </span>
                </td>
              </tr>
              <tr v-show="excluded_loading">
                <td colspan="2"><em>Loading.... <i class="fa fa-gear fa-spin"></i></em></td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="ml-md-4 col-md-7 card p-0">
          <div class="card-header bg-white">
            <h5 class="card-title">Contacts who <span class="font-weight-bold">will not</span> receive this email ({{ summary.totalNotIncluded }} total)</h5>
          </div>
          <div class="table-responsive" id="excluded_contacts" v-if="summary.contacts_not_included" @scroll="onScroll">
            <table class="table table-sm table-striped">
              <tbody>
              <tr v-for="contact in summary.contacts_not_included.data">
                <td>
                  {{ `${contact.full_name}` }}
                  <span class="text-smaller">
                            ({{ formatEmail(contact.email_1) }})
                        </span>
                </td>
                <td><em>{{ excludedReason(contact) }}</em></td>
              </tr>
              <tr v-show="excluded_loading">
                <td colspan="2"><em>Loading.... <i class="fa fa-gear fa-spin"></i></em></td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="row card-body">
      <div class="col text-center">
        <button id="btn_email" type="button" name="btn_email" class="btn btn-lg btn-success mr-2" @click="sendEmail">
            <i class="fa fa-envelope"></i> Send Email
        </button>
        
        <button type="button" class="btn btn-lg btn-primary" @click="pickDateAndTime">
            <i class="fa fa-calendar"></i> Schedule Send
        </button>
      </div>
    </div>
    
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
                                    <input v-model="communication.date_scheduled" name="date_scheduled" id="date_scheduled" class="form-control" type="date" :min="new Date().toISOString().split('T')[0]" style="max-width: 235px;">
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
                                    <input v-model="communication.hour_scheduled" name="hour_scheduled" id="hour_scheduled" class="form-control" type="number" min="1" max="12" style="max-width: 70px;">
                                    <div class="input-group-append">
                                        <span class="input-group-text">:</span>
                                    </div>
                                    <input v-model="communication.minute_scheduled" name="minute_scheduled" id="minute_scheduled" class="form-control" type="number" min="0" max="59" style="max-width: 70px;">
                                    <select v-model="communication.am_pm_scheduled" name="am_pm_scheduled" id="am_pm_scheduled" class="form-control" style="max-width: 70px; background-color: #f0f3f5;">
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
import moment from 'moment'

export default {
    props: ['email_summary_route','send_email_route','saved_search_route'],
    data() {
        return {
            summary: {
                contacts: [],
                contacts_not_included: []
            },
            communication: communication,
            excluded_loading: false,
            dateAndTimeError: null,
            isScheduled: false,
            clientTimezone: null
        }
    },
    mounted() {
        this.communication.hour_scheduled = '12';
        this.communication.minute_scheduled = '0';
        this.communication.am_pm_scheduled = 'PM';
        
        this.setClientTimezone();
    },
    methods: {
        moment, // add moment as a method
        sendEmail() {
            this.isScheduled = false;
            this.doSend();
        },
        doSend() {
            $('#overlay').fadeIn();
            var data = {
                isScheduled: this.isScheduled,
                time_scheduled: this.isScheduled ? this.getFullDateAndTime() : null
            }
            
            axios.post(this.send_email_route, data)
            .then(response => {
                $('#overlay').fadeOut();
                this.$emit('communication-finished', {
                    communication_type: 'email',
                    component: this
                })
            })
            .catch(error => {
                $('#overlay').fadeOut();
                console.log(error)
            })
        },
        excludedReason(contact) {
            if (contact.reasons_not_included.unsubscribed_from_all) return "Contact has unsubscribed from all emails";
            if (contact.reasons_not_included.unsubscribed) return "Contact has unsubscribed from this list";
            if (!contact.email_1) return "No email";
            if (contact.reasons_not_included.saved_search_excluded) return "Not included in the list's saved search";
            if (contact.reasons_not_included.list_not_included) return "Not included by the list's tag filter";
            if (contact.reasons_not_included.list_excluded) return "Excluded by list's tag filter";
            if (contact.reasons_not_included.email_not_included) return "Not included in this email's tag filter";
            if (contact.reasons_not_included.email_excluded) return "Excluded by this email's tag filter";
            if (contact.reasons_not_included.recently_emailed) return "Emailed recently on this list";
            if (contact.reasons_not_included.sent_this_email) return "Already received this email";
            if (contact.reasons_not_included.sent_this_print) return "Already included in print communication";
            if (this.communication.include_transactions) {
                if (!contact.transactions_count) return "0 transactions during the specified date range";
                if (this.communication.exclude_acknowledged_transactions && !contact.transactions_not_acknowledged_count) return "0 unacknowledged transactions";
                if (this.communication.transaction_tags.length && !contact.transactions_tagged_count) return "0 transactions with the specified transaction tags";
                if (this.communication.excluded_transaction_tags.length && !contact.excluded_transactions_tagged_count) return "0 transactions without excluded transaction tags";
            }
        },
        lazyLoad(url) {
            if (!url) return
            this.excluded_loading = true;
            var self = this
            axios.get(url)
            .then(response => {
                // console.log(response.data)
                response.data.contacts.data = self.summary.contacts.data.concat(response.data.contacts.data)
                response.data.contacts_not_included.data = self.summary.contacts_not_included.data.concat(response.data.contacts_not_included.data)
                self.summary.contacts = response.data.contacts
                self.summary.contacts_not_included = response.data.contacts_not_included
                self.summary.totalIncluded = response.data.totalIncluded
                self.summary.totalNotIncluded = response.data.totalNotIncluded
                self.excluded_loading = false;
            })
        },
        onScroll(event) {
            if (this.excluded_loading) return
            var scrollTop = event.target.scrollTop
            var height = event.target.offsetHeight
            var maxHeight = event.target.scrollHeight
            if (scrollTop + height > maxHeight - 10) {
                var url = event.target.id === 'excluded_contacts' ? this.summary.contacts_not_included.next_page_url : this.summary.contacts.next_page_url;
                this.lazyLoad(url)
            }
        },
        formatEmail(email) {
            if (typeof email != 'string') return '';
            return MPHelper.limit_middle(email, 24);
        },
        pickDateAndTime() {
            $('#pickDateAndTimeModal').modal('show');
        },
        scheduleSend() {
            this.validateDateAndTime();
            if (this.dateAndTimeError) {
                return Swal.fire(this.dateAndTimeError, '', 'error');
            }
            
            this.isScheduled = true;
            this.doSend();
        },
        validateDateAndTime() {
            if (!this.communication.date_scheduled || !this.communication.hour_scheduled || !this.communication.minute_scheduled || !this.communication.am_pm_scheduled) {
                this.dateAndTimeError = 'Please enter a correct date and time'
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
            
            if (this.communication.am_pm_scheduled === 'AM') {
                if (this.communication.hour_scheduled == 12) {
                    hour = '00';
                } else {
                    hour = this.communication.hour_scheduled;
                }
            } else {
                if (this.communication.hour_scheduled == 12) {
                    hour = this.communication.hour_scheduled;
                } else {
                    hour = parseInt(this.communication.hour_scheduled) + 12;
                }
            }
            
            if (hour.length === 1) {
                hour = '0' + hour;
            }
            
            var minute = this.communication.minute_scheduled.length === 2 ? this.communication.minute_scheduled : '0' + this.communication.minute_scheduled;
            
            return this.communication.date_scheduled + ' ' + hour + ':' + minute;
        },
        hourChange() {
            if (this.communication.hour_scheduled && this.communication.hour_scheduled > 12) {
                this.communication.hour_scheduled = 12;
            } else if (this.communication.hour_scheduled && this.communication.hour_scheduled < 1) {
                this.communication.hour_scheduled = 1;
            }
        },
        minuteChange() {
            if (this.communication.minute_scheduled && this.communication.minute_scheduled > 59) {
                this.communication.minute_scheduled = 59;
            } else if (this.communication.minute_scheduled && this.communication.minute_scheduled < 0) {
                this.communication.minute_scheduled = '00';
            }
        },
        setClientTimezone() {
            if (this.communication.timezone) {
                this.clientTimezone = this.communication.timezone;
            } else if (Intl) {
                if (Intl.DateTimeFormat()) {
                    if (Intl.DateTimeFormat().resolvedOptions()) {
                        if (Intl.DateTimeFormat().resolvedOptions().timeZone) {
                            this.clientTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                        }
                    }
                }
            }
        }
    },
    activated() {
        this.$store.dispatch('setIsLoadingState', true)
        var self = this
        axios.get(this.email_summary_route)
        .then(response => {
            self.summary = response.data
            self.$store.dispatch('setIsLoadingState', false)
        })
        .catch(error => {
            console.log('error',error)
            this.$store.dispatch('setIsLoadingState', false)
        })

    }
}
</script>

<style scoped lang="css">
#communication_options label {
    display: block;
    min-height: 2.5em;
}
#communication_options label input[type=number] {
    width: 8ch;
}
#excluded_contacts {
    overflow: auto;
    height: 15em;
    white-space: nowrap;
}
</style>
<style>
.v2-lazy-list-wrap {
    border: unset;
    overflow-x: hidden;
    overflow-y: scroll;
}
.v2-lazy-list {
}
.lazy-list-item {
    border: none !important;
}
</style>
