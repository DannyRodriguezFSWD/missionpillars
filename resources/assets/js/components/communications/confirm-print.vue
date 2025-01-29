<template>
    <div>
        <div class="row justify-content-center">
            <div class="col-sm-12">
                <h5>This print communication will include</h5>
                <ul>
                    <li v-if="!communication.print_max_contacts">all contacts</li>
                    <li v-else>up to {{ communication.print_max_contacts }} contacts</li>
                    <li v-if="communication.lists && communication.lists.datatable_state_id">
                        from <b><a :href="saved_search_route+'/'+communication.lists.datatable_state_id" target="_blank">{{ communication.lists.name }}</a></b> saved search
                    </li>
                    <li v-else-if="communication.lists">
                        from <b>{{ communication.lists.name }}</b>
                        <em>( contacts
                            <span v-if="communication.lists.in_tags.length">
                                tagged with
                                <b v-for="(tag, i) in communication.lists.in_tags">
                                    <span v-if="i>0">, </span><span v-if="tag.folder_id != 1">{{tag.folder.name}}\</span>{{tag.name}}
                                </b>
                            </span>
                            <span v-if="communication.lists.in_tags.length && communication.lists.not_in_tags.length"> and </span>
                            <span v-if="communication.lists.not_in_tags.length">
                                not tagged with
                                <b v-for="(tag, i) in communication.lists.not_in_tags">
                                    <span v-if="i>0">, </span><span v-if="tag.folder_id != 1">{{tag.folder.name}}\</span>{{tag.name}}
                                </b>
                            </span>)
                        </em>
                    </li>
                    <li v-if="communication.print_exclude_recent_ndays">that have not been emailed in this list recently <em>(within the last {{communication.print_exclude_recent_ndays}} days)</em></li>
                    <li v-if="communication.print_exclude_emailed">that have not received this email</li>
                    <li v-if="communication.print_exclude_printed">that have not received this communication as print</li>
                    <li v-if="communication.print_include_tags.length">
                        <span v-if="communication.print_include_tags.length">
                            <span v-if="communication.lists && communication.lists.in_tags.length"><em>AND</em> also </span>tagged with
                        </span>
                        <b v-for="(tag, i) in communication.print_include_tags">
                            <span v-if="i>0">, </span><span v-if="tag.folder_id != 1">{{tag.folder.name}}\</span>{{tag.name}}
                        </b>
                    </li>

                    <li v-if="communication.print_exclude_tags.length">
                        that are not tagged with
                        <b v-for="(tag, i) in communication.print_exclude_tags">
                            <span v-if="i>0">, </span><span v-if="tag.folder_id != 1">{{tag.folder.name}}\</span>{{tag.name}}
                        </b>
                    </li>

                    <!-- TRANSACTIONS -->
                    <li v-if="communication.include_transactions">
                        that have tax deductible transactions between <b>{{ moment(communication.transaction_start_date).format('M/D/YYYY') }}</b> and <b>{{ moment(communication.transaction_end_date).format('M/D/YYYY') }}</b> <span v-if="communication.exclude_acknowledged_transactions || communication.transaction_tags.length || communication.excluded_transaction_tags.length"> that </span>
                        <ul>
                            <li v-if="communication.exclude_acknowledged_transactions">are NOT acknowleged</li>
                            <li v-if="communication.transaction_tags.length">
                                are tagged with
                                <b v-for="(tag, i) in communication.transaction_tags">
                                    <span v-if="tag.folder_id != 1">{{tag.folder.name}}\</span>{{tag.name}}
                                </b>
                            </li>
                            <li v-if="communication.excluded_transaction_tags.length">
                                are NOT tagged with
                                <b v-for="(tag, i) in communication.excluded_transaction_tags">
                                    <span v-if="tag.folder_id != 1">{{tag.folder.name}}\</span>{{tag.name}}
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
                <h5 class="card-title">Contacts who <strong>will</strong> receive this print communication ({{ summary.totalIncluded }} total)</h5>
              </div>
              <div style="max-height: 15em; overflow: hidden auto" @scroll="onScroll">
                <table class="table table-sm table-striped">
                  <tbody>
                  <tr v-for="contact in summary.contacts.data">
                    <td>
                      {{ `${contact.full_name_reverse}` }}
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
                <h5 style="font-size: larger">Contacts who <strong>will not</strong> receive this print communication ({{ summary.totalNotIncluded }} total)</h5>
              </div>
              <div class="table-responsive" id="excluded_contacts" v-if="summary.contacts_not_included" @scroll="onScroll">
                <table class="table table-sm table-striped">
                  <tbody>
                  <tr v-for="contact in summary.contacts_not_included.data">
                    <td>
                      {{ `${contact.full_name_reverse}` }}
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
        <div class="row mt-3">
          <div class="col text-center">
            <button type="button" id="btn_print" name="btn_print" class="btn btn-lg btn-success" @click="createPDF">
              <i class="fa fa-file-pdf-o"></i> Download PDF
            </button>
          </div>
        </div>
    </div>

</template>

<script>
import moment from 'moment'
import fileDownload from 'js-file-download';

export default {
    props: ['print_summary_route','track_print_route','saved_search_route'],
    data() {
        return {
            summary: {},
            communication: communication,
            excluded_loading: false,
        }
    },
    methods: {
        moment, // adds moment as a method
        createPDF() {
          this.$store.dispatch('setIsLoadingState',true)
            axios.post(this.track_print_route)
            .then(response => {
                return axios.get(`/crm/print-mail/${this.communication.id}/pdf`,{
                  responseType: 'blob'
              })
            }).then((blob) => {
              const filename = blob.headers["content-disposition"].split('filename=')[1].replace(/\"/ig,"")
              fileDownload(blob.data,filename);
            }).then(() => {
              window.location.href = this.$attrs.view_print_route
            })
            .catch(error => {
                console.log(error)
            })
        },
        excludedReason(contact) {
            if (contact.reasons_not_included.unsubscribed) return "Contact has unsubscribed from this list"
            if (!communication.print_include_non_addressed && !contact.mailing_addresses_count) return "Contact has an undeliverable or missing mailing address"
            if (contact.reasons_not_included.saved_search_excluded) return "Not included in the list's saved search";
            if (contact.reasons_not_included.list_not_included) return "Not included by the list's tag filter"
            if (contact.reasons_not_included.list_excluded) return "Excluded by list's tag filter"
            if (contact.reasons_not_included.print_not_included) return "Not included in this print communication's tag filter"
            if (contact.reasons_not_included.print_excluded) return "Excluded by this print communication's tag filter"
            if (contact.reasons_not_included.recently_printed) return "Included recently in print communication for this list"
            if (contact.reasons_not_included.sent_this_email) return "Already received communication as email"
            if (contact.reasons_not_included.sent_this_print) return "Already included in prior print communication"
            if (this.communication.include_transactions) {
                if (!contact.transactions_count) return "0 transactions during the specified date range"
                if (this.communication.exclude_acknowledged_transactions && !contact.transactions_not_acknowledged_count) return "0 unacknowledged transactions"
                if (this.communication.transaction_tags.length && !contact.transactions_tagged_count) return "0 transactions with the specified transaction tags"
                if (this.communication.excluded_transaction_tags.length && !contact.excluded_transactions_tagged_count) return "0 transactions without excluded transaction tags"
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
    },
    activated() {
        this.$store.dispatch('setIsLoadingState', true)
        var self = this
        axios.get(this.print_summary_route)
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
