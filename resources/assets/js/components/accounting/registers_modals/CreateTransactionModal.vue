<template>
    <div id="create-transaction-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        {{ getCurrentRecord ? (canUpdate ? 'Update Transaction' : 'View Transaction') : 'Create Transaction' }}
                    </h4>
                    <button class="close" type="button" data-dismiss="modal" @click="splits = resetRows()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="register_id">Register</label>
                            <input v-if="canUpdate" type="register_id" class="form-control" id="register_id" readonly disabled :value="currentRegisterName">
                            <span v-else> {{currentRegisterName}} </span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <label for="check_number">Check Number</label>
                            <input v-if="canUpdate" type="text" class="form-control" id="check_number" v-model="register.check_number">
                            <span v-else> {{register.check_number}} </span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <label v-if="canUpdate" for="date">Choose date</label>
                            <label v-else for="date">Date</label>
                            <datepicker v-if="canUpdate" :format="customFormatter" :highlighted="state.highlighted" :bootstrap-styling="true" input-class="bg-white" name='date' id='date' v-model="register.date" placeholder='Choose date' :use-utc="true">
                            </datepicker>
                            <span v-else> {{customFormatter(register.date)}} </span>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                            <label for="comment">Comment</label>
                            <input v-if="canUpdate" type="text" name="comment" id="comment" class="form-control" v-model="register.comment">
                            <span v-else> {{register.comment}} </span>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <label for="debit">
                                <!-- Debit -->
                                {{getHelperTitlesState.debit}}
                            </label>
                            <input v-if="canUpdate" type="number" step="0.01" min="0" name="debit" id="debit" class="form-control" v-model="register.debit" @change="calcEqual('debit')">
                            <span v-else> {{register.debit}} </span>
                        </div>
                        <div class="col-sm-2">
                            <label for="credit">
                                <!-- Credit -->
                                {{ getHelperTitlesState.credit }}
                            </label>
                            <input v-if="canUpdate" type="number" step="0.01" min="0" name="credit" id="credit" class="form-control" v-model="register.credit" @change="calcEqual('credit')">
                            <span v-else> {{register.credit}} </span>
                        </div>

                    </div>


                    <div class="row">
                        <div class="col md-12">
                            <div v-if="canUpdate">How will this transaction be split up?</div>
                            <AccountingMatchTransactions v-if="canUpdate" v-on:use-selected-transactions="useSelectedTransactions"></AccountingMatchTransactions>
                            <div class="table-responsive">
                                <table class="table mt-2 mb-0">
                                    <thead>
                                        <tr>
                                            <th>Account</th>
                                            <th>Fund</th>
                                            <th>Payee</th>
                                            <th>Comment</th>
                                            <th>Amount</th>
                                            <th>Tags</th>
                                            <th>&nbsp;</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, index) in splits" :key="index">
                                            <td>
                                                <input v-if="canUpdate" v-model="row.account_name" type="text" :name="'account-' + index" :id="'account-' + index" class="form-control ui-autocomplete-input accounts" v-on:keyup.delete="resetInput('accounts', index)" @focus="findAccount(index)" @change="calcTotalAndValidate" @blur="calcTotalAndValidate" autocomplete="off" placeholder="Account Name">
                                                <span v-else> {{row.account_name}} </span>
                                                <small class="text-danger hidden" :id="'account-error-'+index">Select a valid account</small>
                                                <small class="text-danger hidden" :id="'account-error2-'+index">Select a different account</small>
                                            </td>
                                            <td>
                                                <input v-if="canUpdate" v-model="row.fund_name" type="text" :name="'fund-' + index" :id="'fund-' + index" class="form-control ui-autocomplete-input funds" v-on:keyup.delete="resetInput('funds', index)" @focus="findFund(index)" @change="calcTotalAndValidate" @blur="calcTotalAndValidate" autocomplete="off" placeholder="Fund Name">
                                                <span v-else> {{row.fund_name}} </span>
                                                <small class="text-danger hidden" :id="'fund-error-'+index">Select a valid fund</small>
                                            </td>
                                            <td>
                                                <input v-if="canUpdate" v-model="row.contact" type="text" :name="'contact-' + index" :id="'contact-' + index" class="form-control ui-autocomplete-input contacts" v-on:keyup.delete="resetInput('contacts', index)" @focus="findContact(index)" @change="calcTotalAndValidate" @blur="calcTotalAndValidate" autocomplete="off" placeholder="Contact's Name">
                                                <span v-else> {{row.contact}} </span>
                                                <small class="text-danger hidden" :id="'contact-error-'+index">Select a valid contact</small>
                                            </td>
                                            <td>
                                                <input v-if="canUpdate" v-model="splits[index].comment" type="text" :name="'comment-' + index" :id="'comment-' + index" class="form-control" autocomplete="off">
                                                <span v-else> {{splits[index].comment}} </span>
                                            </td>
                                            <td>
                                                <input v-if="canUpdate" v-model="splits[index].amount" type="number" step="0.01" :name="'amount-' + index" :id="'amount-' + index" class="form-control" autocomplete="off" @keyup="calcTotalAndValidate()" @change="formatNum(index)">
                                                <span v-else> {{splits[index].amount}} </span>
                                            </td>
                                            <td>
                                                <input v-if="canUpdate" v-model="splits[index].tag" type="text" :name="'tag-' + index" :id="'tag-' + index" class="form-control" autocomplete="off">
                                                <span v-else> {{splits[index].tag}} </span>
                                            </td>
                                            <td style="vertical-align: middle">
                                                <a v-if="canDelete" @click="removeElement(index)" style="cursor: pointer"><i class="fa fa-trash"></i></a>
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
                            <p class="text-danger">Make sure total amount on top equals total amount in splits</p>
                        </div>
                    </div>
                    <div class="row mt-0">
                        <div class="col-sm-3">
                            <button v-if="canUpdate" class="btn btn-success" @click="addRow"><i class="fa fa-plus"></i> Add Row</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <button v-if="canUpdate" type="button" class="btn btn-primary" @click.prevent="createTransaction" :disabled="saveDisabled">Save</button>
                  <button v-if="canDelete && getCurrentRecord" type="button" class="btn btn-danger" @click.prevent="deleteTransaction">Delete</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="splits = resetRows()">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import Datepicker from 'vuejs-datepicker';
import accounting from 'accounting'
import AccountingMatchTransactions from '../accounting-match-transactions'
export default {
    components: {
        Datepicker,
        AccountingMatchTransactions
    },
    mounted() {
        // console.log('Create Transaction Modal Mounted')
        $('#create-transaction-modal').on('hidden.coreui.modal', () => {
            this.setCurrentRecord(null)
        }).on('show.coreui.modal', () => {
            if(this.getCurrentRecord){
                this.register = JSON.parse(JSON.stringify(this.getCurrentRecord.register))
                this.splits = JSON.parse(JSON.stringify(this.getCurrentRecord.splits))
                this.calcTotalAndValidate()
            }
            else{
                this.refreshView()
            }
        })
    },
    props: {
        account_register_id: Number,
        account_register_name: String,
        permissions: Array|Object,
    },
    watch: {
        id: function() {
            this.register.account_register_id = this.id
        },
        'register.amount'(newValue){
            this.$store.dispatch('setHelperAmountState', newValue)
            let text = this.register.credit != '' ? 'Credit' : 'Debit'
            this.$store.dispatch('setHelperStringState', text)

            let _amount = 0 - newValue
            this.$store.dispatch('setHelperTotalAmountState', _amount)
        }
    },
    data() {
        return {
            amountsError: false,
            beforeCreate: true,

            findingAccounts: false,
            findingFunds: false,
            findingContacts: false,

            register: this.resetReg(),
            remove: [],
            saveDisabled: true,
            splits: this.resetRows(),
            state: {
                highlighted: {
                    dates: [
                        new Date()
                    ]
                }
            },
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
        currentRegisterName() {
            if (this.getCurrentRecord) {
                var account = this.getCurrentRecord.register.account;
                if (!account) return '' // this may occur for non-fund transfer journal entries
                return account.number + " - " + account.name
            }
            return this.account_register_name
        },
        canDelete() {
            return this.permissions['accounting-delete'] || !this.getCurrentRecord && this.permissions['accounting-create']
        },
        canUpdate() {
            return this.permissions['accounting-update'] || !this.getCurrentRecord && this.permissions['accounting-create']
        },
    },
    methods: {
        ...mapActions('JournalEntries', [
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
                this.splits[index].contact_id = ''
                this.splits[index].contact = ''
            }

            if(input == 'funds'){
                this.splits[index].fund_id = ''
                this.splits[index].fund_name = ''
            }

            if(input == 'accounts'){
                this.splits[index].account_id = ''
                this.splits[index].account_name = ''
            }

            this.calcTotalAndValidate()
        },
        resetReg(){
            return {
                account_register_id: this.account_register_id,
                date: moment().format(),
                comment: '',
                credit: '',
                debit: '',
                check_number: '',
                amount: '',
            }
        },
        resetRows(){
            return [{
                transaction_split_id: 0,
                account_name: "",
                account_id: "",
                fund_id: "",
                fund_name: "",
                comment: "",
                amount: "",
                tag: "",
                contact: "",
                contact_id: ""
            }]
        },
        validateAll(){
            var invalid = 0;
            this.splits.forEach((item, index) => {
                invalid = this.validateInput(index, 'account-error') ? invalid : invalid+1
                invalid = this.validateInput(index, 'account-error2') ? invalid : invalid+1
                invalid = this.validateInput(index, 'fund-error') ? invalid : invalid+1
                invalid = this.validateInput(index, 'contact-error') ? invalid : invalid+1
            })

            if(invalid > 0 || this.amountsError){
                this.saveDisabled = true
            }
            else{
                this.saveDisabled = false
            }
        },
        validateInput(index, prefix){
            var valid = false;
            var record = this.splits[index];
            var selector = '#'+prefix+'-'+index;
            switch (prefix) {
                case 'account-error':
                    if(record.account_id){
                        $(selector).hide()
                        valid = true
                    }
                    else{ $(selector).show() }
                    break;
                case 'account-error2':
                    if(record.account_id != this.register.account_register_id){
                        $(selector).hide()
                        valid = true
                    }
                    else{ $(selector).show() }
                    break;
                case 'fund-error':
                    if(record.fund_id){
                        valid = true
                        $(selector).hide()
                    }
                    else{ $(selector).show() }
                    break;
                case 'contact-error':
                    if(record.contact_id){
                        valid = true
                        $(selector).hide()
                    }
                    else{ $(selector).show() }
                    break;
                default:
                    break;
            }

            return valid;
        },
        formatNum(index) {
            this.splits[index].amount = this.formatNumber(this.splits[index].amount)
            this.calcTotalAndValidate()
        },
        calcTotalAndValidate() {
            var total_in_cents = 0
            $.each(this.splits, function(index, row) {
                total_in_cents = total_in_cents + MPHelper.dollarsToCents(row.amount)
            })

            if (MPHelper.dollarsToCents(this.register.amount) !== total_in_cents || !this.register.amount) {
                this.amountsError = true
            } else {
                this.amountsError = false
            }

            this.validateAll()
        },
        calcEqual(doc) {
            if (doc === 'credit') {
                this.register.debit = ''
                this.register.credit = this.formatNumber(this.register.credit)
                this.register.amount = this.register.credit
            } else {
                this.register.credit = ''
                this.register.debit = this.formatNumber(this.register.debit)
                this.register.amount = this.register.debit
            }
            this.calcTotalAndValidate()
        },
        refreshView(msg){
            this.register = this.resetReg()
            this.splits = this.resetRows()
            $('.contacts').each(function() {
                this.value = ''
            })
            $('.funds').each(function() {
                this.value = ''
            })
            $('.accounts').each(function() {
                this.value = ''
            })
            if(msg){
                this.$emit('flashMessage', msg)
                this.$parent.$refs.vuetable.refresh()
            }
        },
        createTransaction() {
            if(this.getCurrentRecord){//update
                this.put({
                    url: `/accounting/registers/${this.register.id}`,
                    data: {
                        register: this.register,
                        splits: this.splits,
                        remove: this.remove
                    }
                }).then(res => {
                    this.refreshView('updated')
                    $('#create-transaction-modal').modal('hide');
                }).catch((err) => {
                    console.log(err)
                })
            }
            else{//insert
                this.post({
                    url: '/accounting/registers',
                    data: {
                        register: this.register,
                        splits: this.splits
                    }
                }).then(res => {
                    this.refreshView('created')
                    $('#create-transaction-modal').modal('hide');
                }).catch((err) => {
                    console.log(err)
                })
            }

        },
        deleteTransaction() {
          Swal.fire({
            title: "Are you sure?",
            text: "Please confirm to continue",
            type: 'question',
            showCancelButton: true
          }).then(res => {
            if (res.value) {
              this.destroy({
                url: '/accounting/registers/' + this.register.id
              }).then((res) => {
                $('#create-transaction-modal').modal('hide');
                this.refreshView('deleted')
              }).catch((err) => {
                console.log(err)
              })
            }
          })
        },
        customFormatter(date) {
            return moment(date).utc().format('YYYY-MM-DD');
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
                            category: '',
                        }
                        // Do not include the current register in the list of accounts
                        res.data = res.data.filter(function (account) { return account.id != that.register.account_register_id })
                        res.data.unshift(data)
                        response( res.data )
                    })
                    .catch(() => { that.findingAccounts = false })
                },
                minLength: 0,
                select: function( event, ui ) {
                    if (ui.item.data) {
                        that.splits[index].account_id = ui.item.data
                        that.splits[index].account_name = ui.item.label

                        if(ui.item.account_fund_id !== null) {
                            if (that.findingFunds) return;
                            that.findingFunds = true

                            axios.post('/accounting/ajax/funds/autocomplete', {account_fund_id: ui.item.account_fund_id})
                            .then((res) => {
                                that.findingFunds = false
                                that.splits[index].fund_id = res.data[0].data
                                that.splits[index].fund_name = res.data[0].label

                                if($('#account-' + index).autocomplete( "instance" ) === undefined) {
                                    this.findFund(index)
                                }
                                $('#fund-' + index).val(res.data[0].label)
                                that.splits[index].fund_id = res.data[0].data
                            })
                            .catch(() => { that.findingFunds = false })
                        }
                    } else {
                        let win = window.open(ui.item.uri, '_blank')
                        win.focus()
                    }
                    that.calcTotalAndValidate()
                },
            }).focus(function(){
                let value = $(this).val();
                if(value == '+ Create New Account'){
                    value = ''
                    $(this).val(value);
                }
                // $(this).data("uiAutocomplete").search(value)
                $(this).data("customCatcomplete").search(value)
            });

            if(instance == undefined) {
                $('#account-' + index).focus()
            }
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
                        that.splits[index].fund_id = ui.item.data
                        that.splits[index].fund_name = ui.item.label
                    } else {
                        let win = window.open(ui.item.uri, '_blank')
                        win.focus()
                    }
                    that.calcTotalAndValidate()
                },
                selectFirst: true
            }).focus(function(){
                let value = $(this).val();
                if(value == '+ Create New Fund'){
                    value = ''
                    $(this).val(value);
                }
                $(this).data("uiAutocomplete").search(value)
            });

            if(instance == undefined) {
                $('#fund-' + index).focus()
            }
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
                        that.splits[index].contact_id = ui.item.data
                        that.splits[index].contact = ui.item.label
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

            if(instance == undefined) {
                $('#contact-' + index).focus();
            }
        },
        removeElement: function(index) {
            this.remove.push(this.splits[index].id)
            this.splits.splice(index, 1)
            this.calcTotalAndValidate()
        },
        addRow: function() {
            let elem = document.createElement('tr');
            this.splits.push({
                account_id: "",
                fund_id: "",
                contact_id: "",
                comment: "",
                amount: "",
                tag: ""
            });
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
            };
            return accounting.formatMoney(value, options)
        },
        useSelectedTransactions(data){
            // console.log(data)
            //here we are going to draw the splits
            if(this.splits.length > 0){
                if(this.splits[0].contact_id == ''){//we can safely reset splits
                    this.splits = []
                }
            }

            data.forEach(item => {
                //console.log(item)
                let elem = document.createElement('tr');
                this.splits.push({
                    transaction_split_id: item.id,
                    account_name: item.account.name,
                    account_id: item.account.id,
                    fund_id: item.fund.id,
                    fund_name: item.fund.name,
                    comment: item.transaction.comment ? item.transaction.comment : '',
                    amount: item.amount,
                    tag: "",
                    contact: item.contact.name,
                    contact_id: item.contact.id
                })
            })
            if (this.register.debit) this.calcEqual('debit');
            else if (this.register.credit) this.calcEqual('debit');
            this.$nextTick(() => {
              this.validateAll()
            })
        }
    }
}
</script>

<style scoped>
.hidden{ display: none; }

label {
    font-weight: bold;
}
</style>
