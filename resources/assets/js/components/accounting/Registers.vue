<template>
    <div class="card-body">
        <div class="row" v-if="enableCreate">
            <div class="col-md-12">
                <div class="alert alert-info">
                    Please select register in order to be able to view existing
                    <template v-if="canCreate"> and create new </template>
                     new transactions!
                </div>
            </div>
        </div>
        <div class="row">
            <flash-message class="flash-message-custom"></flash-message>
            <div class="col-md-3">
                <label for="account_register_id">Account Register</label>
                <select name="account_register_id" id="account_register_id" @change="setRegister()" class="form-control mb-2" v-model="account_register_index">
                    <option v-for="(accReg, index) in accountsRegister" :key="index" :value="index">{{  accReg.number + ' - ' + accReg.name }}</option>
                </select>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6">
                <button v-if="canCreate" class="btn btn-primary" @click.prevent="showCreateModal" :disabled="enableCreate">Create Transaction</button>
                <button v-if="canCreate" class="btn btn-primary" @click.prevent="showSearchModal" :disabled="enableCreate"><i class="fa fa-search"></i>&nbsp;Search</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                  <vuetable ref="vuetable"
                            :api-url="apiUrl"
                            :fields="fields"
                            :css="css.table"
                            :sort-order="sortOrder"
                            :multi-sort="true"
                            data-path="data"
                            pagination-path=""
                            :append-params="search"
                            @vuetable:loading="() => $store.dispatch('setIsLoadingState', true)"
                            @vuetable:loaded="() => $store.dispatch('setIsLoadingState', false)"
                            @vuetable:pagination-data="data => {
                      $refs.pagination.setPaginationData(data)
                      $refs.paginationInfo.setPaginationData(data)
                    }"
                            @vuetable:cell-clicked="onCellClicked"
                  >
                  </vuetable>
                </div>
                <div class="vuetable-pagination text-center">
                  <vuetable-pagination ref="pagination"
                                       @vuetable-pagination:change-page="page => $refs.vuetable.changePage(page)"
                  ></vuetable-pagination>
                  <vuetable-pagination-info ref="paginationInfo" info-class="pagination-info">
                  </vuetable-pagination-info>
                </div>
            </div>
        </div>

        <CreateTransactionModal v-bind:account_register_id="account_register_id" v-bind:account_register_name="account_register_name" @flashMessage="setMessage" :permissions="permissions"></CreateTransactionModal>
        <CreateJournalEntryModal v-bind:registers="register" v-bind:rows="register.splits" v-bind:maxjournalid="journal_entry_id" v-bind:groups="groups" v-bind:funds="funds" @flashMessage="setMessage" :permissions="permissions"></CreateJournalEntryModal>
        <FundTransfers v-bind:maxjournalid="journal_entry_id" v-bind:groups="groups" v-bind:funds="funds" :permissions="permissions"></FundTransfers>
        <loading v-if="getIsLoadingState"></loading>
      <div class="modal modal-primary fade" id="search_accounting_transactions_modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                Search Transactions
              </h5>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="contact_name">Contact's Name</label>
                    <input id="contact_name" type="text" v-model="search.contact_name" placeholder="Contact's Name" class="form-control">
                  </div>
                  <div class="form-group">
                    <label for="contact_email">Contact's Email</label>
                    <input id="contact_email" type="text" placeholder="Contact's Email" v-model="search.contact_email" class="form-control">
                  </div>
                  <div class="form-group">
                    <human-date-range :prop-start.sync="search.from" :prop-end.sync="search.to"></human-date-range>
                  </div>
                  <div class="form-group">
                    <label for="check">Check Number</label>
                    <input id="check" type="text" placeholder="Check Number" v-model="search.check_number" class="form-control">
                  </div>
                  <div class="form-group">
                    <label for="account">Account</label>
                    <input type="text" placeholder="Account name" id="searchAccounts" @focus="triggerSearchAccounts" v-model="search.account" class="form-control">
                  </div>
                  <div class="form-group">
                    <label for="comment">Comment</label>
                    <input id="comment" type="text" placeholder="Comment" v-model="search.comment" class="form-control">
                  </div>
                  <div class="form-group">
                    <label for="tag">Tag</label>
                    <input id="Tag" type="text" placeholder="tag" v-model="search.tag" class="form-control">
                  </div>
                  <div class="form-group">
                    <label for="amount">Amount</label>
                    <input id="amount" type="number" placeholder="Amount" v-model="search.amount" class="form-control">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" @click="onClickSearch" data-dismiss="modal">Search</button>
              <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<style>
    .flash-message-custom {
        width: 100%;
        padding: 0 15px;
    }
    /* pagination */
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
    #search_accounting_transactions_modal > .modal-dialog{
      max-width: 800px !important;
    }
</style>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import Vuetable from '../MpVueTable/MpVueTable'
import VuetablePagination from '../MpVueTable/MpVueTablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
import CreateTransactionModal from './registers_modals/CreateTransactionModal'
import CreateJournalEntryModal from './journal_entries/CreateJournalEntries'
import FundTransfers from './FundTransfers.vue'
import accounting from 'accounting'
import loading from '../Loading.vue'
import HumanDateRange from "../HumanDateRange/HumanDateRange";
export default {
    components: {
      HumanDateRange,
        Vuetable,
        VuetablePagination,
        VuetablePaginationInfo,
        CreateTransactionModal,
        CreateJournalEntryModal,
        FundTransfers,
        loading,
    },
    mounted () {
        // console.log('Registers Mounted')
        this.$store.dispatch('setIsLoadingState', true);

      (() => {
        let pathname = (new URL(window.location)).pathname;
        let view_transaction = pathname.match('view_transaction');
        let register_id = pathname.split('/')[3];
        if (view_transaction) this.onCellClicked({register_id})
      })()

        setTimeout( ()=> {
            this.$store.dispatch('setIsLoadingState', false)
        }, 3000)
        let that = this;
        $('#searchAccounts').catcomplete({
          source: function (request, response) {
            // Fetch data
            $.ajax({
              url: "/accounting/ajax/accounts/autocomplete",
              type: 'post',
              dataType: "json",
              data: {
                scopes: 'notEquities',
                search: request.term
              },
              success: function (data) {
                response(data);
              }
            });
          },
          delay: 500,
          minLength: 0,
          select: function (event, account) {
            that.search.account = account.item.name
          }
        })
    },
    props: [
        'groups',
        'funds',
        'accountsRegister',
        'permissions',
    ],
    data () {
        return {
            search:{
              contact_name: '',
              contact_email: '',
              from: '',
              to: '',
              check_number: '',
              account: [],
              comment: '',
              tag: '',
              amount: '',
            },
            journal_entry_id: 0,
            register: {},
            account_register_index: -1,
            account_register_id: 0,
            account_register_name: '',
            enableCreate: true,
            apiUrl: '',
            fields: [
                {
                    name: 'date',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                },
                {
                    name: 'contact',
                },
                {
                    name: 'check',
                },
                {
                    name: 'account',
                },
                {
                    name: 'comment',
                },
                {
                    name: 'tag',
                },
                {
                    name: 'amount',
                    titleClass: 'text-center',
                    dataClass: 'text-right',
                    callback: 'formatCurrency'
                },
                {
                    name: 'balance',
                    titleClass: 'text-center',
                    dataClass: 'text-right',
                    callback: 'formatCurrency'
                }
            ],
            css: {
                table: {
                    tableClass: 'table table-bordered table-striped table-hover',
                    ascendingIcon: 'fa fa-caret-up float-none',
                    descendingIcon: 'fa fa-caret-down float-none'
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
        }
    },
    computed: {
        ...mapGetters('JournalEntries', [
            'getCurrentRecord',
        ]),
        ...mapGetters([
            'getIsLoadingState',
            'getHelperTitlesState'
        ]),
        canCreate() {
            return this.permissions['accounting-create']
        },
    },
    methods: {
        showSearchModal(){
          $('#search_accounting_transactions_modal').modal('show');
        },
        onClickSearch(){
          this.$refs.vuetable.refresh();
        },
        ...mapActions('JournalEntries', [
            'setCurrentRecord',
        ]),
        ...mapActions([
            'post',
            'put',
            'get',
            'setHelperTitlesState'
        ]),
        setMessage(type) {
            $( document ).find('.flash-message-custom .flash__message').remove()
            this.flash("Transaction has been "+type+" successfuly!", 'success', {
                timeout: 5000,
                important: true
            });
        },
        showCreateModal() {
            this.titleApi(this.account_register_id)
            $('#create-transaction-modal').modal('show')
        },
        onCellClicked(data, field, event) {
            this.get({
                url: '/accounting/registers/getSplits',
                data: {
                    register_id: data.register_id,
                    register_type: data.register_type
                }
            }).then((res) => {
                this.register = res.data
                this.setCurrentRecord(this.register)
                if (this.register.register.register_type === null) {
                    this.titleApi(this.register.register.account.id)
                    $('#create-transaction-modal').modal('show');
                } else if (this.register.register.register_type === 'journal_entry') {
                    this.journal_entry_id = this.register.register.journal_entry_id
                    $('#create-journal-entries-modal').modal('show');
                } else if (this.register.register.register_type === 'fund_transfer') {
                    $('#create-fund-transfers-modal').modal('show');
                }
            }).catch((err) => {
                console.log(err)
            })

        },
        setRegister() {
            this.account_register_id = this.accountsRegister[this.account_register_index].id
            this.account_register_name = this.accountsRegister[this.account_register_index].number +' - '+ this.accountsRegister[this.account_register_index].name

            var url ='/accounting/registers/table?acc_id=' + this.account_register_id
            this.apiUrl = url
            this.enableCreate = false
        },
        titleApi(register_id) {
            this.get({
                url: '/accounting/registers/get/debit/credit/titles',
                data: {
                    account_register_id: register_id
                }
            }).then((res) => {
                this.setHelperTitlesState(res.data)
            }).catch((err) => {
                console.log(err)
            })
        },
        formatCurrency (value) {
            let options = {
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
        },triggerSearchAccounts(){
          $('#searchAccounts').catcomplete('search',this.search.account);
      }
    }
}
</script>
