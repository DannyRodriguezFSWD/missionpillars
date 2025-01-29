<template>
    <div>
        <div class="card" v-if="accounts.length == 0">
            <div class="card-header">&nbsp;</div>
            <div class="card-body" id="bank-login">
                <div class="row">
                    <div class="col-sm-8 offset-sm-2 text-center">
                        <p>Click the button below to open a list of Institutions. After you select one, you’ll be guided through an authentication process. Upon completion, Your bank institution will be connected with our software. Or you can manually import transactions from an Excel or CSV file.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 text-center">
                        <button id="link-btn" class="btn btn-primary">Connect with Plaid</button>
                        <button @click="uploadImport" class="btn btn-primary">Import Transaction Manually</button>
                    </div>
                </div>
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
        
        <div class="card" v-else>
            <div class="card-header">
                <button class="btn btn-primary mr-2 bank-popover" id="link-btn" data-container="body" data-toggle="popover" data-placement="top" data-content="Add new bank integration">
                    <i class="fa fa-plus"></i>&nbsp;Add Account
                </button>
                <button @click="uploadImport" class="btn btn-primary mr-2 bank-popover" id="open_import_btn" data-container="body" data-toggle="popover" data-placement="top" data-content="Import Transaction">
                    <i class="fa fa-upload"></i>&nbsp;Import
                </button>
                <button class="btn btn-success bank-popover mr-2" @click.prevent="syncTransactions()" data-container="body" data-toggle="popover" data-placement="top" data-content="Sync bank transactions">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="col-md-12">
                        <carousel ref="carousel" :per-page="3" :navigationEnabled="true" :paginationEnabled="false" :autoplayHoverPause="false" :scrollPerPage="true" :per-page-custom="[[0, 1], [1024, 2], [1350, 3], [2000, 4], [2450, 5]]">
                          <slide v-for="(li, index) in accounts" :key="index" @slideclick="selectedCard" :data-index="index" :data-name="index">
                            <div class="card bank-accounts mr-1 ml-1" :id="'bank-account-' + index">
                                <div class="card-body">
                                    <h4 class="card-title text-primary">{{ li.bank_institution }}</h4>
                                    <h5 class="card-title">
                                        <button v-if="!li.imported" class="btn-sm btn-success btn bank-popover mr-1" @click.prevent="syncTransactions(li)" data-container="body" data-toggle="popover" data-placement="top" data-content="Sync bank transactions">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                        <button v-else class="btn-sm btn-success btn mr-1" @click.prevent="importTransactions(li)">
                                            <i class="fa fa-upload"></i>
                                        </button>
                                        <button v-if="permissions['accounting-delete']" class="btn-sm btn btn-danger bank-popover mr-2" @click="stopSync(li)" data-container="body" data-toggle="popover" data-placement="top" data-content="Remove bank account">
                                            <i class="fa fa-stop-circle-o"></i>
                                        </button>
                                        {{ limit(li.name,18) }} <template v-if="li.mask">({{li.mask}})</template>
                                    </h5>
                                    <p class='card-text'>{{ li.account_subtype ? li.account_subtype : '&nbsp;'  }}</p>
                                    <!-- <h5 class='card-title'>Bank Balance: {{ li.current_balance }}</h5> -->
                                    <div v-if="li.account_id === null || li.changeRegister">
                                        <h5 class="card-title text-warning">Account is not linked in our software! <i class="fa fa-question-circle-o text-primary bank-popover" style="cursor: pointer;" data-toggle="popover" data-placement="top" data-content="Bank accounts can be linked to an Asset or Liability account with a type set to 'use as register'"></i></h5>
                                        <div class="row" v-if="selected == index">
                                            <div class="col-12">
                                                <div class="input-group">
                                                    <select name="account_id" id="account_id" class="form-control" autocomplete="off" v-model="link.account_id">
                                                        <option value="">Select an account register</option>
                                                        <optgroup v-for="(group, ind) in groups" :key="ind" :label="group.name" v-if='group.accounts.length > 0'>
                                                            <option v-for='(account, i) in group.accounts' :key="i" :value="account.id">{{ account.name }}</option>
                                                        </optgroup>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary" :disabled="!link.account_id" @click="linkRegister(li)">Link Register</button>
                                                    </div>
                                                    <div class="input-group-append" v-if="li.changeRegister">
                                                        <button class="btn btn-warning" @click="undoChangeRegister(li)"><i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="card-title" v-else>Transactions not linked: <span class="text-danger">{{ numTrans[li.id] }}</span></h5>
                                    <p v-if="li.account && !li.changeRegister && !li.plaid_error_code" class="card-title">
                                        <!-- Register Balance: <span :class="regBalClass(li.id)">{{ false ? 0: registerBalance(li.id) }}</span><br/> -->

                                        <button v-if="permissions['accounting-update']" class="btn-sm btn-warning btn bank-popover mr-2" @click="changeRegister(li)" data-container="body" data-toggle="popover" data-placement="top" data-content="Change register">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        Linked register: {{ li.account.number }} - {{ li.account.name }}
                                    </p>
                                    <p v-if="li.account && li.plaid_error_code" class="card-title">
                                        <span @click.prevent="syncTransactions(li)" class="badge badge-danger cursor-pointer py-2 bank-popover" data-toggle="popover" data-placement="top" :data-content="li.plaid_error_full">Link with Plaid is broken, click here to fix <i class="fa fa-question-circle-o"></i></span>
                                    </p>
                                </div>
                            </div>
                            </slide>
                        </carousel>
                </div>
            </div>
            <div class="row mt-5" v-show="accounts[selected] && accounts[selected].account_id === null">
                <div class="col-md-12">
                    <div class="card-deck">
                        <div v-if="!permissions['accounting-update']" class="card">
                            <div class="card-header bg-warning">Insufficient Permissions </div>
                            <div class="card-body">
                                Your account has insufficient permissions to link bank accounts to account registers
                            </div>
                        </div>
                        <template v-else>
                            <div v-show="noGroups" class="card">
                                <div class="card-body bg-info">
                                    <h4>Uh oh! You don't have any available account registers! </h4>
                                    <p>
                                        Bank accounts can be linked to an Asset or Liability account with a type set to 'use as register'" <br>
                                        <a :href="accountsroute" class="text-light">Go to Accounts</a>
                                    </p>
                                    <p>If you have already done this, you may have already linked bank accounts to all available registers.</p>
                                    </div>

                            </div>
                        </template>

                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card" v-if="selected !== '' && accounts[selected] && accounts[selected].account_id !== null && accounts[selected].id != 0">
                    <div class="card-header bank-name">
                        <strong>{{ accounts[selected].name }}</strong>
                        <!--
                        <button class="btn btn-primary"><i class="fa fa-pencil"></i></button>
                        -->
                    </div>
                    <div class="card-body">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="d-flex float-right">
                              <div class="mr-2">Show Pendings:</div>
                              <label class="c-switch c-switch-label  c-switch-primary">
                                <input type="checkbox" v-model="show_pendings" @change="togglePending" class="c-switch-input" checked>
                                <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                              </label>
                            </div>
                          </div>
                            <div class="col-md-12 mb-3">
                                Unlinked transactions
                                <div class="table-responsive">
                                  <vuetable ref="vuetable"
                                            :api-url="apiUrl"
                                            :fields="fields"
                                            :sort-order="sortOrder"
                                            :multi-sort="true"
                                            data-path="data"
                                            pagination-path=""
                                            @on-view-row="onCellClicked"
                                            @vuetable:pagination-data="onPaginationData"
                                            @vuetable:row-clicked="onCellClicked"
                                            @vuetable:loading="onTableLoading"
                                            @vuetable:loaded="onTableLoaded"
                                  >
                                  <span slot="date" slot-scope="props" :class="{'text-pending':props.rowData.pending}">
                                    {{props.rowData.date}}
                                  </span>
                                    <span slot="payee" slot-scope="props" :class="{'text-pending':props.rowData.pending}">
                                    {{props.rowData.payee}}
                                  </span>
                                    <span slot="account" slot-scope="props" :class="{'text-pending':props.rowData.pending}">
                                    {{props.rowData.account}}
                                  </span>
                                    <template slot="description" slot-scope="props">
                                      <span :class="{'text-pending':props.rowData.pending}">{{props.rowData.description}}</span>
                                      <span v-if="props.rowData.pending" class="badge badge-warning float-right">pending</span>
                                    </template>
                                    <span slot="amount" slot-scope="props" :class="{'text-pending':props.rowData.pending}">
                                    {{formatTransactionAmount(props.rowData.amount, props.rowData.transaction_id)}}
                                  </span>
                                  </vuetable>
                                </div>
                                <div class="vuetable-pagination text-center">
                                    <vuetable-pagination ref="pagination"
                                        :css="css.pagination"
                                        @vuetable-pagination:change-page="onChangePage"
                                    ></vuetable-pagination>
                                  <vuetable-pagination-info ref="paginationInfo" info-class="pagination-info">
                                  </vuetable-pagination-info>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <LinkBankTransactionModal ref="link_bank_transactions_modal" v-bind:register="initial_reg" v-bind:rows="rows" v-bind:bank_integration="1"></LinkBankTransactionModal>
          <div class="modal" id="toUploadModal">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">
                    Review Imports
                  </h5>
                </div>
                <div class="modal-body">
                 <div class="row">
                   <div class="col-md-12">
                     <div class="table-responsive" style="max-height: 60vh">
                       <table class="table table-bordered table-hover">
                         <thead>
                         <tr>
                           <th>Record Row</th>
                           <th>Date</th>
                           <th>Type</th>
                           <th>Description</th>
                           <th>Amount</th>
                         </tr>
                         </thead>
                         <tbody>
                         <tr v-for="(transaction,index) in toUpload">
                           <td>{{index + 2}}</td>
                           <td>{{transaction.date}}</td>
                           <td>{{transaction.transaction_type}}</td>
                           <td>{{transaction.description}}</td>
                           <td>{{formatTransactionAmount(transaction.amount)}}</td>
                         </tr>
                         </tbody>
                       </table>
                     </div>
                   </div>
                 </div>
                  <div v-if="!this.import_on_existing_account" class="row mt-3">
                    <div class="col-md-5" v-if="!noGroups">
                      <input v-model="bank_account_name" type="text" class="form-control" placeholder="Bank Account Name">
                    </div>
                    <div class="col-md-5" v-if="!noGroups">
                      <select name="account_id" id="account_id" class="form-control" autocomplete="off"
                              v-model="link.account_id">
                        <option value="">Select an account register</option>
                        <optgroup v-for="(group, ind) in groups" :key="ind" :label="group.name"
                                  v-if='group.accounts.length > 0'>
                          <option v-for='(account, i) in group.accounts' :key="i" :value="account.id">{{
                              account.name
                            }}
                          </option>
                        </optgroup>
                      </select>
                    </div>
                    <div class="col-md-12" v-if="noGroups">
                      <div class="p-2 text-danger alert alert-info">
                        <h4>Uh oh! You don't have any available account registers! </h4>
                        <p>
                          Bank accounts can be linked to an Asset or Liability account with a type set to 'use as register'&nbsp;<a :href="accountsroute" class="font-weight-bold text-primary">Go to Accounts</a>
                          If you have already done this, you may have already linked bank accounts to all available registers.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div v-if="import_on_existing_account" class="row">
                    <div class="col-md-12">
                      <h5>
                        {{`This transactions will be imported to ${this.accounts[this.selected].name} with a register of ${this.accounts[this.selected].account.name}`}}
                      </h5>
                    </div>
                  </div>
                </div>
                <div class="modal-footer text-right">
                  <button :disabled="(!link.account_id || !bank_account_name) && !import_on_existing_account" class="btn btn-primary" @click="importTransaction">Import</button>
                  <button class="btn btn-secondary" @click="dismissUpload">Discard</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <loading v-if="getIsLoadingState"></loading>
        <input type="hidden" id="permissions">
        <ImportBankTransactions :groups="groups" :key="import_bank_transaction_modal_key" :institutions="institutions" @close="import_bank_transaction_modal_key+=1" @import_success="importTransaction"></ImportBankTransactions>
        
        <div class="modal fade" id="transactionStartDateModal" tabindex="-1" role="dialog" aria-labelledby="transactionStartDateModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Select Date</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>The earliest date for which transaction should be downloaded from Plaid</p>
                        <p><datepicker v-model="transactionsStartDate" :bootstrap-styling="true" :disabledDates="{ to: new Date(new Date().setFullYear(new Date().getFullYear() - 2)) }" input-class="bg-white" name="start_date" id="transactionsStartDate" placeholder="Choose date"></datepicker></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="linkAccounts()">Sync</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
// const formatter = new Intl.NumberFormat('en-US', {
//     style: 'currency',
//     currency: 'USD',
//     minimumFractionDigits: 2
// })
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import accounting from 'accounting'
import Vuetable from '../../MpVueTable/MpVueTable'
import VuetablePagination from '../../MpVueTable/MpVueTablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
import LinkBankTransactionModal from './LinkBankTransactionModal'
import ImportBankTransactions from './ImportBankTransactions'
import Vue from 'vue'
import CustomActions from './BankAccountsCustomActions'
import { Carousel, Slide } from 'vue-carousel'
import loading from '../../Loading'
import Swal from 'sweetalert2'
import Datepicker from 'vuejs-datepicker'

Vue.component('custom-actions', CustomActions)

export default {
    components: {
        Vuetable,
        VuetablePagination,
        VuetablePaginationInfo,
        LinkBankTransactionModal,
        ImportBankTransactions,
        Carousel,
        Slide,
        loading,
        Datepicker
    },
    mounted() {
        $('#permissions').data('permissions', this.permissions)
        // console.log('list mounted')
        $('.bank-popover').popover({
            container: 'body'
        });

        $('.bank-popover').on({
            mouseenter: function () {
                $(this).popover('show')
            },
            mouseleave: function () {
                $(this).popover('hide')
            }
        })
        this.getRegisterGroups()
    },
    created() {
        var that = this
        var ds = {
            index: 0
        }
        
        this.accounts = this.bankAccounts

        $.each(this.accounts, function(index, acc) {
            acc.current_balance = that.formatNumber(acc.current_balance)
            if (acc.available_balance !== null) {
                acc.available_balance = that.formatNumber(acc.available_balance)
            }
            if (acc.limit_balance !== null) {
                acc.limit_balance = that.formatNumber(acc.limit_balance)
            }
        })
        $.each(this.regBal, function(index, trans) {
            that.regBal[index] = that.formatNumber(trans)
        })
        
        if (this.accounts.length > 0) {
            this.$nextTick(() => {
                that.selectedCard(ds)
            });
        }
    },
    props: ['list', 'funds', 'registerbalances', 'unlinkedtrans', 'config', 'permissions', 'accountsroute', 'bank_accounts'],
    data() {
        return {
            import_bank_transaction_modal_key: 1,
            import_on_existing_account: false,
            bank_account_name: "",
            toUpload: [],
            show_pendings: false,
            downloaded_transactions: 0,
            institutions: this.list,
            bankAccounts: this.bank_accounts,
            accounts: '',
            selected: '',
            apiUrl: '',
            reg: {},
            rows: [],
            initial_reg: {},
            link: {
                account_id: '',
                fund_id: ''
            },
            transactions: [],
            numTrans: this.unlinkedtrans,
            regBal: this.registerbalances,
            groups: {},
            fields: [
                {
                    name: '__slot:date',
                    sortField: 'date',
                    title:'Date',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                },
                {
                    name: '__slot:payee',
                    sortField: 'payee',
                    title:'Payee',
                    titleClass: 'text-center'
                },
                {
                    name: '__slot:account',
                    sortField: 'account',
                    title:'Account',
                    titleClass: 'text-center'
                },
                {
                    name: '__slot:description',
                    sortField: 'description',
                    title: 'Description',
                },

                {
                    name: '__slot:amount',
                    title: 'Amount',
                    titleClass: 'text-center',
                    dataClass: 'text-right',
                    callback: 'formatTransactionAmount'
                },
            ],
            css: {
                table: {
                    tableClass: 'table table-bordered table-striped table-hover',
                    ascendingIcon: 'glyphicon glyphicon-chevron-up',
                    descendingIcon: 'glyphicon glyphicon-chevron-down'
                },
              pagination: {
                    wrapperClass: 'pagination',
                activeClass: 'active',
                disabledClass: 'disabled',
                    pageClass: 'page',
                    linkClass: 'link',
                icons: {
                        first: '',
                        prev: '',
                        next: '',
                        last: '',
                    },
              },
                icons: {
                    first: 'glyphicon glyphicon-step-backward',
                    prev: 'glyphicon glyphicon-chevron-left',
                    next: 'glyphicon glyphicon-chevron-right',
                    last: 'glyphicon glyphicon-step-forward',
                },
            },
            sortOrder: [
                { field: 'date', sortField: 'date', direction: 'desc'}
            ],
            transactionsStartDate: new Date(new Date().setFullYear(new Date().getFullYear() - 1))
        }
    },
    computed:{
        ...mapGetters('AccountingTransactions', [
            'getCurrentRecord',
        ]),
        ...mapGetters([
            'getIsLoadingState',
            'getHelperTitlesState'
        ]),
        selectedAccount() {
            if (this.selected == null || !this.accounts || this.accounts.length === 0 || this.selected > this.accounts.length) return null
            return this.accounts[this.selected].account
        },
        selectedAccountGroup() {
            if (!this.selectedAccount) return null
            return this.groups.find((g) => { return this.selectedAccount.account_group_id == g.id })
        },
        noGroups() {
            if (!this.groups) return true;

            for (var i = 0; i < this.groups.length; i++) {
                if (this.groups[i].accounts.length) return false;
            }
            return true;
        }
    },
    methods: {
      importTransaction(data) {
        this.institutions = data.institutions;
        this.accounts = data.accounts
        this.numTrans = data.unlinked_transactions
        this.bank_account_name = '';
        this.link.account_id = '';
        this.getRegisterGroups();
        this.apiUri(this.accounts[data.selected].id)
        this.$nextTick(() => {
          this.selectedCard({index: data.selected});
          this.$refs.carousel.goToPage(data.toPage)
        })
        Swal.fire('Import Success', 'All transactions imported successfully!', 'success')
        
        if (this.accounts.length === 1) {
            window.location.reload();
        }
      },
      dismissUpload(){
        this.toUpload = [];
        $('#toUploadModal').modal('hide')
      },
      uploadImport(evt) {
        $('#import_bank_transaction_modal').modal('show')
      },
      importTransactions(bankaccount) {
          
          this.uploadImport();
      },
        ...mapActions('AccountingTransactions', [
            'setCurrentRecord',
        ]),
        ...mapActions([
            'post',
            'put',
            'get',
            'setHelperTitlesState'
        ]),
        getRegisterGroups() {
            axios.get('/accounting/registergroups')
            .then((response) => {
                // console.log(response.data)
                this.groups = response.data
            })
        },
        limit(string, chars) { return MPHelper.limit(string,chars) },
        linkAccounts() {
            if (!this.transactionsStartDate) {
                Swal.fire('Please select a date', '', 'error');
                return;
            }
            
            this.$store.dispatch('setIsLoadingState', true);
            let selected_account = this.accounts[this.selected];
            axios.post('/accounting/bank/link/accounts', {link: this.link, account: this.accounts[this.selected], start_date: this.customFormatter(this.transactionsStartDate)})
                .then((res) => {
                    this.apiUri(selected_account.id)
                    this.$store.dispatch('setIsLoadingState', false);
                    this.accounts = res.data.accounts
                    this.numTrans = res.data.unlinked_transactions
                    this.downloaded_transactions = res.data.downloaded_transactions
                    this.institutions = res.data.institutions
                    
                    if (res.data.selected || res.data.selected === 0) {
                        this.$nextTick(() => {
                            this.selectedCard({index: res.data.selected});
                            this.$refs.carousel.goToPage(res.data.toPage)
                        })
                    }
                    
                    this.getRegisterGroups()
                    
                    $('#transactionStartDateModal').modal('hide');
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        unlink(bankaccount) {
            Swal.fire({
                title: 'Are you sure you want to do this?',
                html: 'This will unlink the following bank account and register:<br><b>'
                    + bankaccount.name + (bankaccount.mask ? ' (' + bankaccount.mask + ')' : '') + '<br>'
                    + "Register: " + bankaccount.account.number + ' - ' + bankaccount.account.name + '</br></b>',
                type: 'question',
                showCancelButton: true,
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.value === true) {
                    var data = { bank_account_id: bankaccount.id}
                    this.$store.dispatch('setIsLoadingState', true);
                    axios.post('/accounting/bank/unlink/account', data)
                    .then((res) => {
                        window.location.reload()
                    })
                    .catch((err) => {
                        console.log(err)
                        Swal.fire('Oops!', 'Something went wrong, please try again later.', 'error')
                    })
                } 
            });
        },
        linkRegister(bankaccount) {
            if (!bankaccount.changeRegister) {
                if (bankaccount.start_date) {
                    this.transactionsStartDate = new Date(bankaccount.start_date);
                }
                
                $('#transactionStartDateModal').modal('show');
            } else {
                this.linkAccounts();
            }
        },
        changeRegister(bankaccount) {
            bankaccount.changeRegister = true;
            
            if (bankaccount.start_date) {
                this.transactionsStartDate = new Date(bankaccount.start_date);
            }
        },
        undoChangeRegister(bankaccount) {
            bankaccount.changeRegister = false;
        },
        showTransactions(account) {
            console.log(account)
        },
        selectedCard(dataset) {
            this.selected = dataset.index;
            $('.bank-accounts').removeClass('active');
            $('#bank-account-' + dataset.index).addClass('active');
            if (this.accounts[dataset.index].account_id !== null) {
                // this.getTransactions(this.accounts[dataset.index].id)
                this.apiUri(this.accounts[dataset.index].id)
            }
        },
        apiUri(id) {
          let url = '/accounting/transactions?acc_id=' + id + '&show_pending=' + this.show_pendings + '&time=' + (new Date).getTime()
          this.apiUrl = url
        },
        getTransactions(acc) {
            axios.get('/accounting/transactions',{
                params: {
                    acc_id: acc
                }
            })
            .then((res) => {
                this.transactions = res.data
            })
            .catch((err) => {
                console.log(err)
            })

            $.each(this.transactions, function(index, val) {
                formatter.format(val.amount)
            })
        },
        onPaginationData (paginationData) {
            this.$refs.pagination.setPaginationData(paginationData)
            this.$refs.paginationInfo.setPaginationData(paginationData)
        },
        onChangePage (page) {
            this.$refs.vuetable.changePage(page)
        },
        togglePending (page) {
          let url = new URL(window.location.origin+this.apiUrl);
          url.searchParams.set('show_pending',this.show_pendings);
          this.apiUrl = url.pathname + url.search;
        },
        onCellClicked (data, field, event) {
            if(!this.permissions['accounting-update']) return;
            var amount = data.amount
            // increases in assets and expenses as positive numbers (negative in Plaid data)
            // if (this.selectedAccountGroup && ['asset','expense'].includes(this.selectedAccountGroup.chart_of_account)) amount *= -1

            this.get({
                url: '/accounting/registers/get/debit/credit/titles',
                data: {
                    account_register_id: data.accounts.account_id
                }
            }).then((res) => {
                this.setHelperTitlesState(res.data)
            }).catch((err) => {
                console.log(err)
            })

            axios.get('/accounting/bank-accounts/getBankData', {
                params: {
                    bank_transaction_id: data.id,
                    amount: amount
                }
            })
            .then((res) => {
                this.initial_reg = {
                    id: res.data.id,
                    aol: res.data.aol
                }

                this.reg = {...data,
                    ...{
                        comment: data.description,
                        name: res.data.number + ' - ' + res.data.name,
                        credit: res.data.credit,
                        debit: res.data.debit,
                        amount: res.data.credit > 0 ? res.data.credit : res.data.debit
                    }
                }

                // NOTE Plaid bank transactions use negative numbers for money going INTO an account, so negate
                if (data.transaction_id) {
                    this.$store.dispatch('setHelperAmountState', -1*amount)
                } else {
                    this.$store.dispatch('setHelperAmountState', 1*amount)
                }
                //let text = this.register.credit != '' ? 'Credit' : 'Debit'
                //this.$store.dispatch('setHelperStringState', text)

                // NOTE Total is initially the same as amount ('total amount' is 'the amount LESS ZERO initially selected contributions')
                this.$store.dispatch('setHelperTotalAmountState', -1*amount)
                //console.log(data)
                this.rows = [{
                    transaction_split_id: 0,
                    account_name: data.account ? data.account : "",
                    account_id: data.account_id ? data.account_id : "",
                    fund_id: data.fund_id ? data.fund_id : "",
                    fund_name: data.fund_name ? data.fund_name : "",
                    comment: "",
                    amount: Math.abs(data.amount),
                    tag: "",
                    contact_name: data.payee ? data.payee : "",
                    contact_id: data.contact_id ? data.contact_id : ""
                }]

                this.reg.rows = this.rows
                this.setCurrentRecord(this.reg)
                this.$refs.link_bank_transactions_modal.setRows(this.rows)

                if (data.pending) {
                    Swal.fire({
                        text: "This transaction is still pending. Once your banking institution confirms it is complete, you'll be able to map it. This usually takes 1 business day.",
                        type: 'warning',
                        confirmButton: false
                    })
                } else {
                    $('#link-bank-transaction-modal').modal('show')
                }
            })
            .catch((err) => {
                console.log(err)
            })
        },
        allcap (value) {
            return value.toUpperCase()
        },
        formatAmount (value) {
            return accounting.toFixed(value, 2)
        },
        formatTransactionAmount(value, isPlaid) {
            // Display increases in assets and expenses as positive numbers (negative in Plaid data)
            // https://support.plaid.com/hc/en-us/articles/360008413653-Negative-transaction-amount
            
            if ((isPlaid && this.selectedAccountGroup && ['asset','expense'].includes(this.selectedAccountGroup.chart_of_account))
                || (!isPlaid && this.selectedAccountGroup && ['liability','expense'].includes(this.selectedAccountGroup.chart_of_account))) {
                value *= -1
            } 

            return this.formatNumber(value);
        },
        formatNumber (value) {
            var options = {
                symbol : "$",
                decimal : ".",
                thousand: ",",
                precision : 2,
                format: {
                    pos : "%s%v",
                    neg : "%s(%v)",
                    zero: "%s0.00"
                }
            };
            return accounting.formatMoney(value, options)
        },
        mapTransactionsBulk() {
            var transactions = this.$refs.vuetable.selectedTo

            axios.post('/accounting/transactions-bulk', {account: this.accounts[this.selected], transactions: transactions})
                .then((res) => {
                    console.log(res.data);
                })
                .catch((err) => {
                    console.log(err)
                })

            // console.log(this.$refs.vuetable.selectedTo)
        },
        syncTransactions(bankaccount) {
            var acc_id = false
            if (bankaccount) {
                acc_id = bankaccount.id
            }

            this.$store.dispatch('setIsLoadingState', true);
            axios.post('/accounting/bank/sync/transactions', {
                acc_id: acc_id,
                // bank_id: this.accounts[this.selected].bank_institution_id
            })
            .then((res) => {
                this.$store.dispatch('setIsLoadingState', false);
                this.numTrans = res.data.unlinked_transactions
                this.downloaded_transactions = res.data.downloaded_transactions
                this.$refs.vuetable.refresh()
                // display errors
                if (res.data.errors && !_.isEmpty(res.data.errors)) {
                    msg = 'Plaid return the following error code(s):'
                    for (var account_id in res.data.errors) {
                        let err = res.data.errors[account_id]
                        if (err.error_code == 'ITEM_LOGIN_REQUIRED') {
                            this.accounts = res.data.accounts
                            return this.displayLoginRequired(err)
                        }

                        msg += ' ' + err.error_code
                    }


                    Swal.fire({
                        text: msg,
                        type: 'error',
                        confirmButtonText: 'Close',
                        footer: 'You may want to try again later'
                    })
                    
                    this.accounts = res.data.accounts
                    
                    return
                }

                // otherwise display success
                let msg = 'Your account is up to date';
                if(this.downloaded_transactions > 0){
                    msg = `${this.downloaded_transactions} new transactions were synced`
                }
                
                Swal.fire({
                    text: msg,
                    type: 'success',
                    confirmButtonText: 'Close'
                })
                
                this.accounts = res.data.accounts
            })
            .catch((err) => {
                this.$store.dispatch('setIsLoadingState', false);
                console.log(err)
            })
        },
        /**
         * @param  {object} err An object with the following attributes: error_code, error_message, bank, bank_id
         */
        displayLoginRequired(err) {
            console.log('dlr',err)
            let msg = 'Accounts from ' + err.bank + ' were unable to sync';
            Swal.fire({
                text: msg,
                type: 'error',
                confirmButtonText: 'Login to ' + err.bank,
                onAfterClose: () => {
                    this.userBankAuth(err)
                }
            });
        },
        userBankAuth(data) {
            this.$store.dispatch('setIsLoadingState', true);
            axios.post('/accounting/bank/update-link-token/'+data.bank_id)
                .then((res) => {
                    let handler = Plaid.create({
                        token: res.data.linkToken,
                        onSuccess: function(public_token, metadata) {
                            
                        },
                        onExit: function(error, metadata) {
                            if (!error) return
                        },
                    });

                    this.$store.dispatch('setIsLoadingState', false);

                    handler.open();
                })
                .catch((err) => {
                    console.log(err)
                })

        },
        stopSync(bankaccount){
            var data = {}
            if (bankaccount) {
                Swal.fire({
                    title: 'Are you sure you want to do this?',
                    html: 'This will remove the following bank account:<br><b>'
                        + bankaccount.name + (bankaccount.mask ? ' (' + bankaccount.mask + ')' : '') + '</b>',
                    type: 'question',
                    showCancelButton: true,
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.value === true) {
                        data.bank_account_id = bankaccount.id
                        
                        this.$store.dispatch('setIsLoadingState', true);
                        axios.post('/accounting/bank/stop/sync/transactions', data)
                        .then((res) => {
                            window.location.reload()
                        })
                        .catch((err) => {
                            console.log(err)
                            Swal.fire('Oops!', 'Something went wrong, please try again later.', 'error')
                        })
                    } 
                });
            }
        },
        onTableLoading(){
            this.$store.dispatch('setIsLoadingState', true);
        },
        onTableLoaded(){
            this.$store.dispatch('setIsLoadingState', false);
        },
        registerBalance(id) {
            return this.regBal[id] ? this.regBal[id] : 0
        },
        regBalClass(id) {
            // negative amounts are represnted, e.g.,  $(12.34)
            // return ''
            return this.registerBalance(id).includes('(') ? 'text-danger' : ''
        },
        customFormatter(date) {
            return moment(date).format('YYYY-MM-DD');
        }
    },
    events: {
        'filter-set' (filterText) {
            this.moreParams = {
                filter: filterText
            }
            Vue.nextTick( () => this.$refs.vuetable.refresh() )
        },
        'filter-reset' () {
            this.moreParams = {}
            Vue.nextTick( () => this.$refs.vuetable.refresh() )
        }
    }
}
</script>
<style>
.text-pending {
  color: gray;
  font-style: italic;
}
.bank-accounts {
    border-radius: 10px;
    transition: color 0.5s ease-out;
    min-height: 250px;
}
.bank-accounts:hover {
    background: #005471;
    border-color: var(--primary);
    color: #fff;
}
.not-linked {
    color: #FFCE5F;
}
.card.bank-accounts.active {
    background: #005471;
    border-color: var(--primary);
    color: #fff;
}
.card-header.bank-name .btn {
    margin-top: 0;
}
.pagination {
  margin: 0;
  float: right;
}
.pagination a.page {
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 10px;
  margin-right: 2px;
}
.pagination a.page.active {
  color: white;
  background-color: #337ab7;
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 10px;
  margin-right: 2px;
}
.pagination a.btn-nav {
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 7px;
  margin-right: 2px;
}
.pagination a.btn-nav.disabled {
  color: lightgray;
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 7px;
  margin-right: 2px;
  cursor: not-allowed;
}
.pagination-info {
  float: left;
}
.filter-bar {
  padding: 10px;
}
div.bank-accounts.active .card-title.text-primary{
    color: #fff!important;
}

.VueCarousel-slide {
    overflow-wrap: normal;
}
.VueCarousel-slide .card-title{
    word-break: normal;
}

/* HACK: This is from a newwer version of coreui.css. move to custom.css or remove completely when new layout adds this definition */
.card-body {
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    min-height: 1px;
    padding: 1.25rem;
}
.text-light {
    color: #f0f3f5 !important;
}
</style>
