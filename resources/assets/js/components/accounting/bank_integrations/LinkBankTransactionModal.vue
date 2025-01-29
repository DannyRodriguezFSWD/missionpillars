<template>
    <div id="link-bank-transaction-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Link Transaction</h4>
                    <button class="close" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="register_id">Register</label>
                            <input type="register_id" class="form-control" id="register_id" readonly disabled :value="extra.name">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <label for="check_number">Check Number</label>
                            <input type="text" class="form-control" id="check_number" v-model="extra.check_number">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <label for="date">Choose date</label>
                            <datepicker :format="customFormatter" :highlighted="state.highlighted" :bootstrap-styling="true" input-class="bg-white" name='date' id='date' v-model="extra.date" placeholder='Choose date' :use-utc="true">
                            </datepicker>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                            <label for="comment">Comment</label>
                            <input type="text" name="comment" id="comment" class="form-control" disabled v-model="extra.description">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <label for="debit">
                                <!-- Debit -->
                                {{getHelperTitlesState.debit}}
                            </label>
                            <input type="number" step="0.01" min="0" name="debit" id="debit" class="form-control" disabled v-model="extra.debit" @change="calcEqual('debit')">
                        </div>
                        <div class="col-sm-2">
                            <label for="credit">
                                <!-- Credit -->
                                {{ getHelperTitlesState.credit }}
                            </label>
                            <input type="number" step="0.01" min="0" name="credit" id="credit" class="form-control" disabled v-model="extra.credit" @change="calcEqual('credit')">
                        </div>

                    </div>

                    <div class="row">
                        <div class="col md-12">
                            <div>How will this transaction be split up?</div>
                            <AccountingMatchTransactions v-on:use-selected-transactions="useSelectedTransactions"></AccountingMatchTransactions>
                            <div class="table-responsive">
                              <table class="table mt-2 mb-0">
                                <thead>
                                <tr>
                                  <th>Account</th>
                                  <th>Fund</th>
                                  <th>Payee</th>
                                  <th>Comment</th>
                                  <th>Amount</th>
                                  <th v-if="showFees">Fee</th>
                                  <th>Tags</th>
                                  <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(row, index) in rows" :key="index">
                                  <td>
                                    <input v-model="row.account_name" type="text" :name="'account-' + index" :id="'account-' + index" class="form-control ui-autocomplete-input accounts" v-on:keyup.delete="resetInput('accounts', index)" @focus="findAccount(index)" @change="calcTotalAndValidate" @blur="calcTotalAndValidate" autocomplete="off" placeholder="Account Name">
                                  </td>
                                  <td>
                                    <input v-model="row.fund_name" type="text" :name="'fund-' + index" :id="'fund-' + index" class="form-control ui-autocomplete-input funds" v-on:keyup.delete="resetInput('funds', index)" @focus="findFund(index)" @change="calcTotalAndValidate" @blur="calcTotalAndValidate" autocomplete="off" placeholder="Fund Name">
                                  </td>
                                  <td>
                                    <input v-model="row.contact_name" type="text" :name="'contact-' + index" :id="'contact-' + index" class="form-control ui-autocomplete-input contacts" v-on:keyup.delete="resetInput('contacts', index)" @focus="findContact(index)" @change="calcTotalAndValidate" @blur="calcTotalAndValidate" autocomplete="off" placeholder="Contact's Name">
                                  </td>
                                  <td>
                                    <input type="text" :name="'comment-' + index" :id="'comment-' + index" class="form-control" v-model="rows[index].comment" autocomplete="off">
                                  </td>
                                  <td>
                                    <input type="number" step="0.01" :name="'amount-' + index" :id="'amount-' + index" class="form-control" autocomplete="off" v-model="rows[index].amount" @keyup="calcTotalAndValidate" @blur="calcTotalAndValidate">
                                  </td>
                                  <td v-if="showFees">
                                    <input v-if="row.payment_processor == 'stripe' || row.payment_processor == 'wepay'" type="number" step="0.01" :name="'fee-' + index" :id="'fee-' + index" class="form-control" autocomplete="off" v-model="rows[index].fee" @keyup="calcTotalAndValidate" @blur="calcTotalAndValidate">
                                    <span v-else></span>
                                  </td>
                                  <td>
                                    <input type="text" :name="'tag-' + index" :id="'tag-' + index" class="form-control" v-model="rows[index].tag" autocomplete="off">
                                  </td>
                                  <td style="vertical-align: middle">
                                    <a @click="removeElement(index)" style="cursor: pointer"><i class="fa fa-trash"></i></a>
                                  </td>
                                  <td>&nbsp;</td>
                                </tr>
                                </tbody>
                              </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center" v-if="amountsError">
                        <p class="text-danger">Make sure total amount on top equals total amount in splits.<span v-if="currentTotal > 0"> Current total is: ${{ currentTotal }}</span></p>
                        </div>
                        <div class="col-md-12 text-center">
                            <p v-if="accountError" class="text-danger">Select account</p>
                            <p v-if="fundError" class="text-danger">Select fund</p>
                            <p v-if="contactError" class="text-danger">Select payee</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <button class="btn btn-success" @click="addRow"><i class="fa fa-plus"></i> Add Row</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!--
                    <button @click.prevent="deleteTransaction" type="button" class="btn btn-danger mr-4">Delete</button>
                    -->
                  <button @click.prevent="createTransaction" type="button" class="btn btn-primary" :disabled="disabled">Save</button>
                  <button class="btn btn-outline-warning float-right" @click="hideRow">Hide Transaction</button>
                  <button data-dismiss="modal" type="button" class="btn btn-secondary">Close</button>
                </div>
            </div>
        </div>
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>

<style>
.modal-footer-edit {
    display: block;
}
</style>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import Datepicker from 'vuejs-datepicker'
import accounting from 'accounting'
import AccountingMatchTransactions from '../accounting-match-transactions'
import loading from '../../Loading'
export default {
    components: {
        Datepicker,
        AccountingMatchTransactions,
        loading
    },
    mounted() {
        // console.log('Edit Transaction Modal Mounted')
        $('#link-bank-transaction-modal').on('shown.coreui.modal', () => {
            this.extra = this.getCurrentRecord
            this.rows = JSON.parse(JSON.stringify(this.extra.rows))
            this.calcTotalAndValidate()
        }).on('hidden.coreui.modal', () => {
            this.rows = this.resetRows()
            this.disabled = true
        })
    },
    props: {
        register: Object,
        bank_integration: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            extra: {},
            disabled: true,
            row_id: null,
            findingAccounts: false,
            findingFunds: false,
            findingContacts: false,

            rows: this.resetRows(),
            reg: {
                id: '',
                account_register_id: '',
                date: '',
                comment: '',
                credit: '',
                debit: '',
                check_number: '',
                amount: '',
            },
            state: {
                highlighted: {
                    dates: [
                        new Date()
                    ]
                }
            },

            amountsError: false,
            accountError: false,
            fundError: false,
            contactError: false,
            currentTotal: null,
            showFees: false
        }
    },
    computed: {
        ...mapGetters('AccountingTransactions', [
            'getCurrentRecord',
        ]),
        ...mapGetters([
            'getIsLoadingState',
            'getHelperTitlesState'
        ]),
    },
    methods: {
        ...mapActions('AccountingTransactions', [
            'setCurrentRecord',
        ]),
        ...mapActions([
            'setHelperAmountState',
            'setHelperStringState',
            'post',
            'get',
            'put',
            'destroy'
        ]),
        resetInput(input, index){
            // console.log(input, index)
            if(input == 'contacts'){
                this.rows[index].contact_id = ''
                this.rows[index].contact_name = ''
            }

            if(input == 'funds'){
                this.rows[index].fund_id = ''
                this.rows[index].fund_name = ''
            }

            if(input == 'accounts'){
                this.rows[index].account_id = ''
                this.rows[index].account_name = ''
            }

            this.calcTotalAndValidate()
        },
        resetRows(){
            return [{
                transaction_split_id: 0,
                account_name: "",
                account_id: "",
                fund_id: "",
                comment: "",
                amount: "",
                tag: "",
                contact: "",
                contact_id: ""
            }]
        },
        formatNum(index) {
            this.rows[index].amount = this.formatNumber(this.rows[index].amount)
            this.calcTotalAndValidate()
        },
        calcTotalAndValidate() {
            this.disabled = true
            this.accountError = false
            this.fundError = false
            this.contactError = false
            var total_in_cents = 0
            let disable_button = false
            $.each(this.rows, (index, row) => {

                total_in_cents = total_in_cents + MPHelper.dollarsToCents(row.amount)
                
                if (row.fee > 0) {
                    total_in_cents = total_in_cents - MPHelper.dollarsToCents(row.fee)
                }
                
                if(row.account_id == '' || row.account_id == null){
                    disable_button = true
                    this.accountError = true
                    return false
                }

                if(row.fund_id == '' || row.fund_id == null){
                    disable_button = true
                    this.fundError = true
                    return false
                }

                if(row.contact_id == '' || row.contact_id == null){
                    disable_button = true
                    this.contactError = true
                    return false
                }
            })

            if(disable_button == true){
                return false
            }
            else{
                if (MPHelper.dollarsToCents(this.extra.amount,true) !== Math.abs(total_in_cents)) {
                    this.disabled = true
                    this.amountsError = true
                    this.currentTotal = parseFloat(total_in_cents / 100).toFixed(2)
                } else {
                    this.disabled = false
                    this.amountsError = false
                }
            }
        },
        calcEqual(doc) {
            var that = this
            if (doc === 'credit') {
                that.reg.debit = ''
                that.reg.credit = that.formatNumber(that.reg.credit)
                that.reg.amount = that.reg.credit
            } else {
                that.reg.credit = ''
                that.reg.debit = that.formatNumber(that.reg.debit)
                that.reg.amount = that.reg.debit
            }
            that.calcTotalAndValidate()
        },
        deleteTransaction() {
          Swal.fire({
            title: 'Are you sure?',
            text: 'Are you sure you want to delete this transaction?\nThis action an not be reverted',
            type: 'question',
            showCancelButton: true
          }).then(res => {
            if (res.value) {
              this.destroy({
                url: '/accounting/registers/' + this.reg.id,
                data: {}
              }).then((res) => {
                $('#link-bank-transaction-modal').modal('hide')
                this.$emit('flashMessage', 'deleted')
                this.$parent.$refs.vuetable.refresh()
              })
                  .catch((err) => {
                    console.log(err)
                  })
            }
          })
        },
        createTransaction() {
            //return false
            this.post({
                url: '/accounting/sync-single-transaction',
                data: {
                    register: this.extra,
                    splits: this.rows,
                    initial_reg: this.register
                }
            }).then((res) => {
                $('#link-bank-transaction-modal').modal('hide')
                this.$emit('flashMessage', 'updated')
                this.$parent.$refs.vuetable.refresh()
                
                if (res.message == 'Request failed with status code 500') {
                    Swal.fire('Error linking bank transaction', 'please try again later or contact our support at customerservice@continuetogive.com', 'error')
                }
            })
            .catch((err) => {
                console.log(err)
            })
        },
        customFormatter(date) {
            return moment(date).utc().format('YYYY-MM-DD')
        },
        findAccount(index) {
            let that = this
            if (that.findingAccounts) return;

            // let instance = $('#account-' + index).autocomplete( "instance" )
            let instance = $('#account-' + index).catcomplete( "instance" )
            if (instance) return

            // Setup autocomplete for the first time and then refocus
            // $('#account-' + index).autocomplete({
            $('#account-' + index).catcomplete({
                source: function( request, response ) {
                    that.findingAccounts = true
                    axios.post('/accounting/ajax/accounts/autocomplete', {
                        scopes: 'notEquities',
                        search: request.term
                    })
                    .then((res) => {
                        that.findingAccounts = false
                        let data = {
                            value: '',
                            label: '+ Create New Account',
                            uri: '/accounting/accounts',
                            category: ""
                        }
                        // Do not include the current register in the list of accounts
                        res.data = res.data.filter(function (account) { return account.id != that.extra.accounts.account_id })
                        res.data.unshift(data)
                        response( res.data )
                    })
                    .catch(() => { that.findingAccounts = false })

                },
                minLength: 0,
                select: function( event, ui ) {
                    if (ui.item.data) {
                        that.rows[index].account_id = ui.item.id
                        that.rows[index].account_name = ui.item.label
                    } else {
                        let win = window.open(ui.item.uri, '_blank')
                        win.focus()
                    }
                    that.calcTotalAndValidate()
                }
            }).focus(function(){
                let value = $(this).val();
                if(value == '+ Create New Account'){
                    value = ''
                    $(this).val(value);
                }

                // $(this).data("uiAutocomplete").search(value)
                $(this).data("customCatcomplete").search(value)
            });

            $('#account-' + index).focus()
        },
        findFund(index) {
            let that = this
            if (that.findingFunds) return;

            let instance = $('#fund-' + index).autocomplete( "instance" )
            if (instance) return

            // Setup autocomplete for the first time and then refocus
            $('#fund-' + index).autocomplete({
                source: function( request, response ) {
                    that.findingFunds = true
                    axios.post('/accounting/ajax/funds/autocomplete', {search: request.term})
                    .then((res) => {
                        that.findingFunds = false
                        let data = {
                            value: '',
                            label: '+ Create New Fund',
                            uri: '/accounting/accounts'
                        }
                        res.data.unshift(data)
                        response( res.data )
                    })
                    .catch(() => { that.findingFunds = false })
                },
                minLength: 0,
                select: function( event, ui ) {
                    if (ui.item.data) {
                        that.rows[index].fund_name = ui.item.label
                        that.rows[index].fund_id = ui.item.id
                    } else {
                        let win = window.open(ui.item.uri, '_blank')
                        win.focus()
                    }
                    that.calcTotalAndValidate()
                }
            }).focus(function(){
                let value = $(this).val();
                if(value == '+ Create New Fund'){
                    value = ''
                    $(this).val(value);
                }
                $(this).data("uiAutocomplete").search(value)
            });

            $('#fund-' + index).focus()
        },
        findContact(index) {
            let that = this
            if (that.findingContacts) return;

            let instance = $('#contact-' + index).autocomplete( "instance" )
            if (instance) return

            // Setup autocomplete for the first time and then refocus
            $('#contact-' + index).autocomplete({
                source: function( request, response ) {
                    that.findingContacts = true
                    axios.post('/crm/ajax/contacts/autocomplete', {search: request.term})
                    .then((res) => {
                        that.findingContacts = false
                        let data = {
                            value: '',
                            label: '+ Create New User',
                            uri: '/crm/contacts/create'
                        }
                        res.data.unshift(data)
                        response( res.data )
                    })
                    .catch(() => { that.findingContacts = false })
                },
                minLength: 0,
                select: function( event, ui ) {
                    if (ui.item.data) {
                        that.rows[index].contact_name = ui.item.label
                        that.rows[index].contact_id = ui.item.id
                    }
                    else {
                        let win = window.open(ui.item.uri, '_blank')
                        win.focus()
                    }
                    that.calcTotalAndValidate()
                }
            }).focus(function(){
                let value = $(this).val();
                if(value == '+ Create New User'){
                    value = ''
                    $(this).val(value);
                }
                $(this).data("uiAutocomplete").search(value)
            });

            $('#contact-' + index).focus();
        },
        removeElement: function(index) {
            this.rows.splice(index, 1)
            this.calcTotalAndValidate()
        },
        addRow: function() {
            let elem = document.createElement('tr')
            this.rows.push({
                account_id: "",
                fund_id: "",
                comment: "",
                amount: "",
                tag: ""
            })
        },
        formatNumber (value) {
            return accounting.toFixed(value, 2)
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
            }
            return accounting.formatMoney(value, options)
        },
        setRows(data){
            this.rows = data
        },
        useSelectedTransactions(data){
            this.showFees = false;

            //here we are going to draw the splits
            if(this.rows.length > 0){
                if(this.rows[0].contact_id == ''){//we can safely reset rows
                    this.rows = []
                }
            }

            data.forEach(item => {
                //console.log(item)
                let elem = document.createElement('tr');
                this.rows.push({
                    transaction_split_id: item.id,
                    account_name: item.account.name,
                    account_id: item.account.id,
                    fund_id: item.fund.id,
                    fund_name: item.fund.name,
                    comment: item.transaction.comment ? item.transaction.comment : '',
                    amount: item.amount,
                    fee: item.transaction.fee,
                    tag: "",
                    contact_name: item.contact.name,
                    contact_id: item.contact.id,
                    payment_processor: item.transaction.payment_processor
                })
                
                if (item.transaction.fee > 0) {
                    this.showFees = true;
                }
            })

            this.calcTotalAndValidate()
        },
        async hideRow () {
          let res = await Swal.fire({
            title: 'Are you sure',
            text: "Are you sure you want to hide this bank transaction?",
            type: 'question',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: async (result) => {
              let id = this.$parent.reg.id;
              await axios.put("/accounting/ajax/bank-transactions/"+id, { hidden: true })
                  .then(response => {
                    $('#link-bank-transaction-modal').modal('hide')
                    this.$parent.$refs.vuetable.refresh()
                  })
                  .catch(error => { console.log ('error', error) })
            }
          })
        },
    }
}
</script>
