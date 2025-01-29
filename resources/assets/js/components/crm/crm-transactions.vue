<template>
    <div class="crm-transactions">
        <div class="card-deck mb-3">
            <div class="card">
                <div class="card-body p-0 d-flex align-items-center">
                    <i v-bind:class="{ 'bg-info': style_class == 'info', 'bg-danger': style_class == 'danger', 'bg-warning': style_class == 'warning', 'bg-success': style_class == 'success' }" class="fa fa-dollar p-4 font-2xl mr-3">&nbsp;</i>
                    <div>
                        <div class="text-value-sm">{{ total }}</div>
                        <div class="text-muted text-uppercase font-weight-bold small">
                            transactions totaling
                        </div>
                        <div class="text-value-sm">${{ formatMoney(sum) }}</div>
                    </div>
                </div>
            </div>
            <div v-if="sum_completed && sum != sum_completed" class="card">
                <div class="card-body p-0 d-flex align-items-center">
                    <i class="bg-success fa fa-dollar p-4 font-2xl mr-3">&nbsp;</i>
                    <div>
                        <div class="text-value-sm">{{ total_completed }}</div>
                        <div class="text-muted text-uppercase font-weight-bold small">
                            completed transactions totaling
                        </div>
                        <div class="text-value-sm">${{ formatMoney(sum_completed) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body" v-if="display == 'transactions'">
                <div class="btn-group btn-group" role="group" aria-label="...">
                    <button v-if="canCreate && !contact_id && pledge_status != 'complete'" type="button" class="btn btn-primary" data-toggle="modal" data-target="#crm-transactions-modal" data-backdrop="static" data-keyboard="false">
                        <i class="icon icon-plus"></i>
                        Record transaction
                    </button>

                    <button v-if="canCreate && create_pledge && contact_id && pledge_status != 'complete'" type="button" class="btn btn-primary" data-toggle="modal" data-target="#crm-transactions-modal" data-backdrop="static" data-keyboard="false">
                        <i class="icon icon-plus"></i>
                        Record transaction
                    </button>

                    <button v-if="pledge_id==0 && canView" class="btn btn-primary" data-toggle="modal" data-target="#advanced-search-modal" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-search"></i>
                        Advanced Search
                    </button>
                    <button v-if="canView"  @click="$refs.export_form.submit()" class="btn btn-primary" type="submit">
                      <i class="fa fa-file-excel-o"></i>
                      Export
                    </button>
                    <button v-if="canCreate"  class="btn btn-primary" type="submit" @click="uploadImport">
                      <i class="fa fa-upload"></i>
                      Import
                    </button>
                </div>
            </div>
          <form class="d-none" v-if="pledge_id==0" ref="export_form" method="POST" :action="'/crm/transactions/'+new Date().getTime()+'/export'" accept-charset="UTF-8">
            <input name="_token" type="hidden" :value="token.content">
            <input name="export_params" type="hidden" :value="JSON.stringify(search)">
          </form>
            <!-- transactions table -->
            <div class="card-body mb-0 pt-0">
              <div class="table-responsive">
                <mp-vue-table
                    ref="vuetable"
                    :api-url="endpoint"
                    :fields="fields"
                    data-path="splits.data"
                    pagination-path="splits"
                    :append-params="append_params"
                    @vuetable:pagination-data="onPaginationData"
                    @vuetable:loading="onTableLoading"
                    @vuetable:loaded="onTableLoaded"
                    @vuetable:row-clicked="onClickOverTransaction"
                    @vuetable:load-success="onDataChangeDidFinish">

                <span slot="amount" slot-scope="props" class="badge badge-pill p-2" v-bind:class="props.rowData.transaction.parent_transaction_id ? 'badge-success' : 'badge-primary'" v-tooltip v-bind:title="props.rowData.transaction.parent_transaction_id ? 'Soft Credit' : false">
                  ${{ formatMoney(props.rowData.amount) }}
                </span>
                  <small class="text-nowrap" slot="purpose" slot-scope="props" v-if="props.rowData.purpose">{{ props.rowData.purpose.name }}</small>
                  <span class="text-nowrap" slot="campaign" slot-scope="props">
                  <small v-if="props.rowData.campaign">{{ props.rowData.campaign.name }}</small>
                </span>
                  <span class="text-nowrap" slot="contact" slot-scope="props">
                  <small>
                      <a :href="contactLink(props.rowData.transaction.contact_id)" @click.stop="" v-if="contactLink(props.rowData.transaction.contact_id)">{{ arrayGet(props.rowData.transaction.contact, 'full_name', 'Deleted Contact') }}</a>
                      <span v-else>{{ arrayGet(props.rowData.transaction.contact, 'full_name', 'Deleted Contact') }}</span>
                  </small>
                </span>
                  <span class="text-nowrap" slot="date" slot-scope="props">
                  <small>{{ parseLocalDateTime(props.rowData.transaction.transaction_initiated_at) }}</small>
                </span>
                <span slot="is_recurring" slot-scope="props">
                    <span v-if="props.rowData.is_recurring == 0">
                        One Time
                    </span>
                    <span v-if="props.rowData.is_recurring == 1">
                        Recurring
                    </span>
                </span>
                  <span slot="status" slot-scope="props">
                  <span v-if="props.rowData.transaction.status == 'pending'" class="badge badge-warning badge-pill p-2">
                      {{ props.rowData.transaction.status }}
                  </span>
                  <span v-if="props.rowData.transaction.status == 'failed'" class="badge badge-danger badge-pill p-2">
                      {{ props.rowData.transaction.status }}
                  </span>
                  <span v-if="props.rowData.transaction.status == 'complete'" class="badge badge-success badge-pill p-2">
                      {{ props.rowData.transaction.status }}
                  </span>
                  <span v-if="props.rowData.transaction.status == 'refunded'" class="badge badge-info badge-pill p-2">
                      {{ props.rowData.transaction.status }}
                  </span>
                </span>

                </mp-vue-table>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="vuetable-pagination text-center">
                    <VueTablePagination ref="pagination"
                                        @vuetable-pagination:change-page="changePage"
                    ></VueTablePagination>
                    <vuetable-pagination-info ref="paginationInfo" info-class="pagination-info">
                    </vuetable-pagination-info>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div class="card-footer">&nbsp;</div>
        </div>

        <CRMTransaction ref="crmtransaction"
            @transactionStored="refreshTable"
            @transactionDeleted="refreshTable"
            :endpoint="endpoint"
            :campaigns="campaigns"
            :purposes="purposesActive"
            :highlighted="highlighted"
            :master_id="master_id"
            :pledge_status="pledge_status"
            :create_pledge="create_pledge"
            :contact_id="contact_id"
            :contact_name="contact_name"
            :permissions="permissions"
            :purpose_id = "defaultPledgeID"
            :campaign_id = "defaultCampaignID"
        >
        </CRMTransaction>

        <!-- advanced search -->
        <div class="modal fade" id="advanced-search-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-primary" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Search</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: calc(100vh - 235px); overflow-y: auto;}">
                        <div class="form-group" v-if="!contact_id">
                            <label for="keyword">Contact's Name</label>
                            <input v-model="search.keyword" class="form-control" placeholder="Contact's Name" autocomplete="Off" name="keyword" type="text" id="keyword">
                        </div>
                        <div class="form-group" v-if="!contact_id">
                            <label for="email">Contact's email</label>
                            <input v-model="search.email" class="form-control" placeholder="Contact's Email" autocomplete="Off" name="email" type="email" id="email">
                        </div>
                        <div class="form-group purposes-group-container">
                            <label for="purpose">Purpose</label>
                            <Multiselect :multiple="true" :options="purposes" :show-labels="false" @input="purposesChange"
                                            group-values="children" group-label="groupName" :group-select="true"
                                            track-by="name" label="name" :close-on-select="true"
                                            placeholder="Select Purposes"
                                            v-model="charts">
                            </Multiselect>
                        </div>
                        <div class="form-group">
                            <label for="campaign">Fundraiser</label>
                            <Multiselect :multiple="true" :options="campaigns" :show-labels="false" @input="campaignsChange"
                                            :close-on-select="true"
                                            placeholder="Select Fundraisers"
                                            v-model="campaigns2">
                            </Multiselect>
                        </div>
                        <div class="form-group">
                          <human-date-range :prop-start.sync="search.startObj" :prop-end.sync="search.endObj" label="Transaction Date"></human-date-range>
                        </div>
                        <div class="form-group">
                            <label for="payment_category">Online / Offline</label>
                            <select v-model="search.online_offline" class="form-control" id="online_offline" name="online_offline">
                                <option value="all">All</option>
                                <option value="online">Online Transactions</option>
                                <option value="offline">Offline Transactions</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="channel">Channel</label>
                            <Multiselect :multiple="true" :options="channels" :show-labels="false" @input="channelsChange"
                                            :close-on-select="true"
                                            placeholder="Select Channel"
                                            v-model="channels2">
                            </Multiselect>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select v-model="search.status" class="form-control" id="status" name="status"><option value="all">All</option><option value="complete">Complete</option><option value="pending">Pending</option><option value="failed">Failed</option><option value="refunded">Refunded</option></select>
                        </div>
                        <div class="form-group">
                            <label for="payment_category">Payment Category</label>
                            <select v-model="search.payment_category" class="form-control" id="payment_category" name="payment_category">
                                <option value="all">All</option>
                                <option value="check">Check</option>
                                <option value="cash">Cash</option>
                                <option value="ach">ACH</option>
                                <option value="cc">Credit Card</option>
                                <option value="cashapp">Cashapp</option>
                                <option value="venmo">Venmo</option>
                                <option value="paypal">Paypal</option>
                                <option value="facebook">Facebook</option>
                                <option value="goods">Goods</option>
                                <option value="other">Other</option>
                                <option value="unknown">Unknown</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tags">Tags</label>
                            <select class="form-control d-none" name="tags" multiple v-model="search.tags">
                                <option v-for="option in folders" :value="option.id">{{ option.name }}</option>
                            </select>
                            <TagsMultiSelect :multiple="true" :options="folders" @input="tagsChange" :show-labels="false"
                                            placeholder="Tags"
                                            v-model="tags">
                            </TagsMultiSelect>
                        </div>
                        <div class="form-group">
                            <human-date-range :prop-start.sync="search.startCreatedAtObj" :prop-end.sync="search.endCreatedAtObj" label="Entered Date"></human-date-range>
                        </div>
                        <div class="form-group">
                            <human-date-range :prop-start.sync="search.startDepositDateObj" :prop-end.sync="search.endDepositDateObj" label="Deposit Date"></human-date-range>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary" id="submit" @click="onClickSearch()" data-dismiss="modal">Search</button>
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>

        <ImportTransactions import_success="importTransaction"></ImportTransactions>

        <loading v-if="getIsLoadingState"></loading>

    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    import loading from '../Loading.vue'
    import CRMModal from '../crm-modal.vue'
    import Datepicker from 'vuejs-datepicker'
    import HumanDateRange from '../HumanDateRange/HumanDateRange'
    import moment, { locale, utc } from 'moment'
    import VueTable from 'vuetable-2';
    import VueTablePagination from "../MpVueTable/MpVueTablePagination";
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
    import CRMTransaction from './popups/crm-transaction'
    import mpVueTable from '../MpVueTable/MpVueTable';
    import Multiselect from '../mp/multiselect';
    import TagsMultiSelect from '../mp/tags-multiselect';
    import ImportTransactions from './import-transactions'
    
    export default {
        name: 'CRMTransactions',
      components: {
        Datepicker,
        loading,
        CRMModal,
        CRMTransaction,
        VueTable,
        VueTablePagination,
        VuetablePaginationInfo,
        mpVueTable,
        HumanDateRange,
        Multiselect,
        TagsMultiSelect,
        ImportTransactions
      },
        directives: {
            'tooltip': function (el, binding) {
                $(el).tooltip({
                    title: binding.value,
                    placement: binding.arg,
                    trigger: 'hover'          
                });
            }
        },
        props: {
            endpoint: String,
            display: String,
            link_purposes_and_accounts: Number,
            contact_id : Number,
            contacts_link : String,
            pledge_id : Number,
            master_id : Number,
            pledge_status: String,
            create_pledge : Number,
            contact_name: {
                type: String,
                default: ""
            },
            permissions: "",
            pledge: {},
            folders: Array
        },
        data() {
            return {
                fields:[
                  {
                    name: '__slot:amount',
                    sortField: 'amount',
                    title:'Amount',
                  },
                  {
                    name: '__slot:purpose',
                    sortField: 'for',
                    title:'Purpose',
                  },
                  {
                    name: '__slot:campaign',
                    sortField: 'campaign',
                    title:'Fundraiser',
                  },
                  {
                    name: '__slot:contact',
                    sortField: 'contact',
                    title: 'Contact',
                  },
                  {
                    name: '__slot:date',
                    sortField: 'date',
                    title: 'Date',
                  },
                  {
                    name: '__slot:is_recurring',
                    sortField: 'is_recurring',
                    title: 'Type',
                  },
                  {
                    name: '__slot:status',
                    sortField: 'status',
                    title: 'Status',
                  },
                ],
                token: document.head.querySelector('meta[name="csrf-token"]'),
                transactions: [],
                campaigns: {},
                purposes: [],
                purposesActive: [],
                highlighted: {
                    dates: [new Date()]
                },
                sum: 0,
                total: 0,
                sum_completed: 0,
                total_completed: 0,
                style_class: 'info',
                selected_date: null,
                tags: null,
                charts: [],
                campaigns2: [],
                channels: [
                    {
                        id: 'face_to_face',
                        name: 'Face to face'
                    },
                    {
                        id: 'mail',
                        name: 'Mail'
                    },
                    {
                        id: 'ncf',
                        name: 'Appreciated Stock Through NCF'
                    },
                    {
                        id: 'event',
                        name: 'Event'
                    },
                    {
                        id: 'other',
                        name: 'Other'
                    },
                    {
                        id: 'unknown',
                        name: 'Unknown'
                    },
                    {
                        id: 'ctg_direct',
                        name: 'CTG - Direct'
                    },
                    {
                        id: 'ctg_embed',
                        name: 'CTG - Website Embedded Form'
                    },
                    {
                        id: 'ctg_text_link',
                        name: 'CTG - Text For Link'
                    },
                    {
                        id: 'ctg_text_give',
                        name: 'CTG - Text To Give'
                    },
                    {
                        id: 'website',
                        name: '(deprecated) Website'
                    }
                ],
                channels2: [],
                search: {
                    active: false,
                    keyword: null,
                    email: null,
                    chart: [],
                    campaign: [],
                    startObj: null,
                    endObj: null,
                    start: null,
                    end: null,
                    status: 'all',
                    payment_category: 'all',
                    contact_id: this.contact_id,
                    pledge_id: this.pledge_id,
                    master_id: this.master_id,
                    create_pledge: this.create_pledge,
                    online_offline: 'all',
                    channel: [],
                    tags: [],
                    startCreatedAtObj: null,
                    endCreatedAtObj: null,
                    startCreatedAt: null,
                    endCreatedAt: null,
                    startDepositDateObj: null,
                    endDepositDateObj: null,
                    startDepositDate: null,
                    endDepositDate: null
                },
            }
        },
        mounted() {
            window.crm_transactions_modal = new coreui.Modal(document.querySelector("#crm-transactions-modal"))
            if (typeof doCreate === "boolean" && doCreate == true) $('#crm-transactions-modal').modal('show');
        },
        computed: {
            ...mapGetters([
                'getIsLoadingState',
            ]),
            ...mapState([

            ]),
            canView() {
                return this.permissions['transaction-view']
            },
            canCreate() {
                return this.permissions['transaction-create']
            },
            defaultPledgeID() {
                return this.pledge && this.pledge.splits ? this.pledge.splits[0].purpose_id : null
            },
            defaultCampaignID() {
                return this.pledge && this.pledge.splits ? this.pledge.splits[0].campaign_id : null
            },
            append_params(){
              return this.search
            }
        },
        methods: {
            onTableLoading(){
              this.$store.dispatch('setIsLoadingState', true);
            },
            onTableLoaded(){
              this.$store.dispatch('setIsLoadingState', false);
            },
            onPaginationData (paginationData) {
              this.$refs.pagination.setPaginationData(paginationData)
              this.$refs.paginationInfo.setPaginationData(paginationData)
            },
            contactLink(id) {
                if (!this.contacts_link || !id) return null
                return this.contacts_link + "/" + id
            },
            customFormatter(date) {
                return moment(date).format('YYYY-MM-DD');
            },
            formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") {
                try {
                    decimalCount = Math.abs(decimalCount);
                    decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

                    const negativeSign = amount < 0 ? "-" : "";

                    let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
                    let j = (i.length > 3) ? i.length % 3 : 0;

                    return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
                } catch (e) {
                    console.log('formatMoney error', e)
                }
            },
            ...mapActions([
                'post',
                'get',
                'put',
                'destroy',
                'setHelperOrganizationPurposeState'
            ]),
            changePage(page){
              this.$refs.vuetable.changePage(page)
            },
            onDataChangeDidFinish(result){
                this.setHelperOrganizationPurposeState(result.data.organization_purpose)
                this.campaigns = result.data.campaigns
                this.purposes = result.data.charts
                this.purposesActive = result.data.chartsActive
                this.sum = result.data.sum
                this.sum_completed = result.data.sum_completed
                this.total = result.data.total
                this.total_completed = result.data.total_completed
                this.style_class = result.data.class

                this.transactions = result.data.splits.data
                this.search.order = result.data.order

            },
            parseLocalDateTime(date_time, raw){
                var bits = date_time.split(/\D/);
                if(bits.length  == 6){
                    var local_date = new Date(Date.UTC(bits[0], --bits[1], bits[2], bits[3], bits[4], bits[5]));
                }
                else{
                    let db_date = new Date(date_time)
                    let local_date = new Date(Date.UTC(db_date.getFullYear(), db_date.getMonth(), db_date.getDate(), db_date.getHours(), db_date.getMinutes(), db_date.getSeconds()))
                }
                if(raw){
                    return local_date
                }
                return moment(local_date).format('MM/DD/YYYY hh:mm a')
            },
            onClickOverTransaction(data){
                this.action = 'edit' // this doesn't appear to be used
                //this.transaction = this.setTransaction(this.transactions[index])
                let transaction = this.transactions.find(tran => tran.id == data.id);
                if (this.permissions['transaction-view'] && transaction.transaction.parent_transaction_id) {
                    let parentTransaction = this.transactions.find(tran => tran.transaction.id == transaction.transaction.parent_transaction_id)
                    if (parentTransaction === undefined) {
                        this.getTransaction(transaction.transaction.parent_transaction_id);
                    } else {
                        this.$refs.crmtransaction.setSelectedTransaction(parentTransaction)
                        //this.$refs.crmtransaction.onPaymentCategoryChange()
                        crm_transactions_modal.show();
                    }
                } else {
                    this.$refs.crmtransaction.setSelectedTransaction(transaction)
                    //this.$refs.crmtransaction.onPaymentCategoryChange()
                    crm_transactions_modal.show();
                }
            },
            onClickSearch(){
                this.search.active = true
                this.search.start = this.search.startObj
                this.search.end = this.search.endObj
                this.search.startCreatedAt = this.search.startCreatedAtObj
                this.search.endCreatedAt = this.search.endCreatedAtObj
                this.search.startDepositDate = this.search.startDepositDateObj
                this.search.endDepositDate = this.search.endDepositDateObj
                this.$refs.vuetable.refresh()
            },
            refreshTable(){
                this.$refs.vuetable.refresh();
            },
            arrayGet(array, key, def) {
                return array ? array[key] : def;
            },
            getTransaction(id) {
                this.get({
                    url: this.endpoint,
                    data: {
                        transaction_id: id
                    }
                }).then(result => {
                    this.$refs.crmtransaction.setSelectedTransaction(result.data.splits.data[0])
                    //this.$refs.crmtransaction.onPaymentCategoryChange()
                    crm_transactions_modal.show();
                })
            },
            tagsChange(value, id) {
                this.search.tags = this.tags.map(p => p.id)
            },
            purposesChange(value, id) {
                this.search.chart = this.charts.map(p => p.id)
            },
            campaignsChange(value, id) {
                this.search.campaign = this.campaigns2.map(p => p.id)
            },
            channelsChange(value, id) {
                this.search.channel = this.channels2.map(p => p.id)
            },
            importTransaction(data) {
                this.numTrans = data.unlinked_transactions
                this.$nextTick(() => {
                    this.selectedCard({index: data.selected});
                    this.$refs.carousel.goToPage(data.toPage)
                })
                Swal.fire('Import Success', 'All transactions imported successfully!', 'success')
              },
            dismissUpload(){
                this.toUpload = [];
                $('#toUploadModal').modal('hide')
            },
            uploadImport(evt) {
                $('#import_transaction_modal').modal('show')
            },
            importTransactions(bankaccount) {
                this.uploadImport();
            },
        }
    }
</script>

<style scoped>
ul.pagination{
    display: block;
    width:100%;
    text-align:center;
}
ul.pagination li{
    display:inline-block;
    *display:inline; /*IE7*/
    *zoom:1; /*IE7*/
}

.multiple-selection{
    height: 100px;
    border: 1px solid rgba(0, 0, 0, 0.15);
    overflow: auto;
}

.multiple-selection .list .item{
    padding: 6px 10px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.15);
}
</style>
<style>
th.sort-asc::after{
  font-family: FontAwesome;
  content: 'f0d8';
}
</style>
