<template>
    <div id="create-fund-transfers-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        {{ getCurrentRecord ? (canUpdate ? 'Edit Fund Transfer' : 'View Fund Transfer') : 'Create Fund Transfer' }}
                    </h4>
                    <button class="close" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row form-group">
                        <label for="je_id" class="col-sm-2 col-form-label">Journal Entry #: </label>
                        <div class="col-sm-2">
                            <input v-if="canUpdate" type="text" name="je_id" id="je_id" class="form-control" :disabled="true" v-model="registers.journal_entry_id">
                            <span v-else> {{registers.journal_entry_id}} </span>
                        </div>
                    </div>
                    <div class="row">
                        <label for="date" class="col-sm-2 col-form-label">Date*</label>
                        <div class="col-sm-3 form-group">
                            <datepicker v-if="canUpdate" :format="customFormatter" :highlighted="state.highlighted" :bootstrap-styling="true" input-class="bg-white" name='date' id='date' v-model="registers.date" placeholder='Choose date' :use-utc="true"></datepicker>
                            <span v-else> {{registers.date}} </span>
                        </div>
                    </div>
                    <div class="row">
                        <label for="memo" class="col-sm-2 col-form-label">Memo*</label>
                        <div class="col-sm-6 form-group">
                            <input v-if="canUpdate" type="text" name="memo" id="memo" class="form-control" required v-model="registers.comment">
                            <span v-else> {{registers.comment}} </span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="account_id" class="col-form-label col-sm-4">{{ canUpdate ? 'Select account' : 'Account' }} for fund transfer</label>
                        <div class="col-sm-4">
                            <select v-if="canUpdate" name="account_id" id="account_id" class="form-control" autocomplete="off" required v-model="registers.account_id">
                                <option value="">Select an account</option>
                                <optgroup v-for="group in groups" :key="group.id" :label="group.name" v-if='group.accounts.length > 0 && (group.chart_of_account === "asset" || group.chart_of_account === "liability")'>
                                    <option v-for='account in group.accounts' :key="account.id" :value="account.id">{{ account.name }}</option>
                                </optgroup>
                            </select>
                            <span v-else> {{ registers.account ? registers.account.name : '' }} </span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="source_fund_id" class="col-form-label col-sm-4">{{ canUpdate ? 'Select source' : 'Source'}} fund</label>
                        <div class="col-sm-3">
                            <select v-if="canUpdate" name="source_fund_id" id="source_fund_id" class="form-control" autocomplete="off" required v-model="registers.source_fund_id">
                                <option value="">Select a fund</option>
                                <option v-for="fund in funds" :key="fund.id" :value="fund.id">{{ fund.name }}</option>
                            </select>
                            <span v-else>
                                {{ !registers.source_fund_id ?'': funds[registers.source_fund_id].name}}
                            </span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="fund_id" class="col-form-label col-sm-4">{{ canUpdate ? 'Select target' : 'Target'}} fund</label>
                        <div class="col-sm-3">
                            <select v-if="canUpdate" name="fund_id" id="fund_id" class="form-control" autocomplete="off" required v-model="registers.target_fund_id">
                                <option value="">Select a fund</option>
                                <option v-for="fund in funds" :key="fund.id" :value="fund.id">{{ fund.name }}</option>
                            </select>
                            <span v-else>
                                {{ !registers.target_fund_id ?'': funds[registers.target_fund_id].name}}
                            </span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="fund_transfer_amount" class="col-sm-4">Transfer Amount</label>
                        <div class="col-sm-3">
                            <input v-if="canUpdate" type="number" step="0.01" name="fund_transfer_amount" id="fund_transfer_amount" class="form-control" min="0" v-model="registers.fund_transfer_amount" >
                            <span v-else> {{registers.fund_transfer_amount}} </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button v-if="canUpdate && getCurrentRecord" @click="deleteJournalEntry()" class="btn btn-danger mr-4">
                        Delete
                    </button>
                  <button v-if="canUpdate" class="btn btn-primary" @click="createFundTransferEntry" :disabled="invalidForm">Save</button>
                  <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>
<style>
.tag {
    width: 50%;
}
#totals, #oob, #foob, #totals-debit, #totals-credit, #oob-debit, #oob-credit, #foob-debit, #foob-credit, .foob-color {
    font-weight: bold;
}
#oob-debit, #oob-credit, #foob-debit, #foob-credit, .foob-color {
    color: #BE0707
}
.je-account-col {
    width: 50%;
}
#je_id, #je-total {
    background-color: #e8e8e8;
}
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0;
}
#create-fund-transfers-modal label {
    font-weight: bold;
}
</style>
<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import loading from '../Loading.vue'
import Datepicker from 'vuejs-datepicker';
import accounting from 'accounting'

export default {
    components: {
        Datepicker,
        loading
    },
    mounted() {
        // console.log('Fund Transfers Entries')
        $('#create-fund-transfers-modal').on('hidden.coreui.modal', () => {
            this.setCurrentRecord(null)
        }).on('show.coreui.modal', () => {
            if(this.getCurrentRecord){
                Object.assign(this.registers,  JSON.parse(JSON.stringify(this.getCurrentRecord.register)))
                Object.assign(this.rows, JSON.parse(JSON.stringify(this.getCurrentRecord.splits)))
                if(this.rows.length > 1){
                    this.registers.source_fund_id = this.rows[0].fund_id
                    this.registers.target_fund_id = this.rows[1].fund_id
                }
                // console.log(this.registers, this.funds)
            }
            else{
                this.setCurrentRecord(null)
                this.registers = this.setRegister()
                this.rows = []
                this.get({
                    url: '/accounting/registers/get/next/entry/number',
                    data: {}
                }).then(result => {
                    this.registers.journal_entry_id = result.data
                })
            }
        })
    },
    props: [
        'maxjournalid',
        'groups',
        'funds',
        'permissions',
    ],
    data() {
        return {
            registers: this.setRegister(),
            rows: [],
            source_fund_id: '',
            target_fund_id: '',
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
        ]),
        ...mapState([

        ]),
        invalidForm() {
            return ! (this.registers.comment && this.registers.account_id && this.registers.source_fund_id && this.registers.target_fund_id && this.registers.fund_transfer_amount)
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
            'post',
            'get',
            'put',
            'destroy'
        ]),
        setRegister(content){
            let register = {
                journal_entry_id: this.maxjournalid + 1,
                date: moment().format(),
                comment: '',
                amount: '',
                fund_transfer_amount: '',
                fund_id: '',
                source_fund_id: '',
                target_fund_id: '',
                register_type: '',
                account_id: '',
                account_register_id: ''
            }

            if(content){
                register = content
            }
            return register
        },
        refreshView(msg){
            $('#create-fund-transfers-modal').modal('toggle');
            this.$emit('flashMessage', msg)
            this.$parent.$refs.vuetable.refresh()
            this.beforeCreate = true
            this.registers = this.setRegister()
            this.$root.$emit('jeTableRefresh', this.addFunds)
        },
        createFundTransferEntry () {
            this.registers.register_type = 'fund_transfer'
            this.registers.amount = 0

            if(this.getCurrentRecord){//edit
                this.rows[0].fund_id = this.registers.source_fund_id
                this.rows[1].fund_id = this.registers.target_fund_id
                this.registers.account_register_id = this.registers.account_id
                var data = {}
                Object.assign(data, this.registers)
                delete data.journal_entry_id // do not uppdate
                // console.log(data)

                this.put({
                    url: '/accounting/registers/' + this.getCurrentRecord.register.id,
                    data: {
                        register: data,
                        type: 'fund_transfer',
                        splits: this.rows
                    }
                }).then((res) => {
                    this.refreshView('updated')
                })
                .catch((err) => {
                    console.log(err)
                })
            }
            else{
                this.registers.account_register_id = this.registers.account_id
                this.registers.amount = this.registers.fund_transfer_amount
                //return false
                this.post({
                    url: '/accounting/registers',
                    data: {
                        register: this.registers,
                        type: 'fund_transfer',
                        splits: this.rows
                    }
                }).then((res) => {
                    this.$emit('registry-stored')
                    this.refreshView('created')
                })
                .catch((err) => {
                    console.log(err)
                })
            }
        },
        customFormatter(date) {
            return moment(date).utc().format('YYYY-MM-DD');
        },
        deleteJournalEntry () {
            Swal.fire({
              title: "Are you sure?",
              text: 'Are you sure you want to delete this Fund Transfer Entry?',
              type: 'question',
              showCancelButton: true
            }).then(res => {
              if (res.value) {
                this.destroy({
                  url: '/accounting/registers/' + this.registers.id,
                  data: {
                    split: false
                  }
                }).then(result => {
                  this.refreshView('deleted')
                  $('#create-fund-transfers-modal').modal('hide')
                })
                    .catch((err) => {
                      console.log(err)
                    })
              }
            })
        },
    },
}
</script>
