<template>
  <div id="create-journal-entries-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">
            {{ getCurrentRecord ? (canUpdate ? 'Edit Journal Entry' : 'View Journal Entry') : 'Create Journal Entry' }}
          </h4>
          <button class="close" type="button" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row form-group">
            <label for="je_id" class="col-md-2 col-form-label">Journal Entry #: </label>
            <div class="col-md-3">
              <input v-if="canUpdate" type="text" name="je_id" id="je_id" class="form-control" :disabled="true"  v-model="registers.journal_entry_id">
              <span v-else> {{registers.journal_entry_id}} </span>
            </div>
          </div>
          <div class="row form-group">
            <label for="je-date" class="col-md-2 col-form-label">Date*</label>
            <div class="col-md-3">
              <datepicker v-if="canUpdate" :format="customFormatter" :highlighted="state.highlighted" :bootstrap-styling="true" input-class="bg-white" name='date' id='date' v-model="registers.date" placeholder='Choose date' :use-utc="true"></datepicker>
              <span v-else>
                            </span>
            </div>
          </div>
          <div class="row form-group">
            <label for="je-memo" class="col-sm-2 col-form-label">Memo*</label>
            <div class="col-sm-6">
              <input v-if="canUpdate" type="text" name="je-memo" id="je-memo" :class="'form-control '+invalidMemo" v-model="registers.comment" required>
              <span v-else> {{registers.comment}} </span>
              <small class="text-danger" v-if="invalidMemo">Add a memo</small>
            </div>
            <label for="je-total" class="col-sm-2 col-form-label text-right">Total: </label> {{ formatCurrency(registers.amount) }}
            <div class="col-sm-2">
              <input type="hidden" name="je-total" id="je-total" class="form-control" v-model="registers.amount">
            </div>
          </div>
          <table class="table mt-3">
            <colgroup>
              <col span=2 class="journal-meta">
              <col span=2 class="journal-amount">
              <col>
            </colgroup>
            <thead>
            <tr>
              <th>Account* / Tag</th>
              <th>Fund* / Payee*</th>
              <th>Debit</th>
              <th>Credit</th>
              <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(row, index) in rows" :key="index">
              <td class="je-account-col">
                <input v-if="canUpdate" v-model="row.account_name" type="text" :name="'account-' + index" :id="'account-' + index" :class="'form-control ui-autocomplete-input accounts '+invalidAccount[index]" @focus="findAccount(index)" autocomplete="off" placeholder="Account Name">
                <span v-else> {{row.account_name}} </span>
                <small class="text-danger" v-if="invalidAccount[index]">Select an account</small>
                <br>
                <input v-if="canUpdate" v-model="row.tag" type="text" :name="'tag-' + index" :id="'tag-' + index" class="form-control tag" autocomplete="off" placeholder="Enter a tag">
                <span v-else> {{row.tag}} </span>
              </td>
              <td class="je-fund-col">
                <input v-if="canUpdate" v-model="row.fund_name" type="text" :name="'fund-' + index" :id="'fund-' + index" :class="'form-control ui-autocomplete-input funds '+invalidFund[index]" @focus="findFund(index)" autocomplete="off" placeholder="Fund Name">
                <span v-else> {{row.fund_name}} </span>
                <small class="text-danger" v-if="invalidFund[index]">Select a fund</small>
                <br>
                <input v-if="canUpdate" v-model="row.contact" type="text" name="contact" :id="'contact-' + index" :class="'form-control ui-autocomplete-input autocomplete '+invalidContact[index]" @focus="findContact(index)" autocomplete="off" placeholder="Contact's Name">
                <span v-else> {{row.contact}} </span>
                <small class="text-danger" v-if="invalidContact[index]">Select a contact</small>
              </td>
              <td class="text-right">
                <input v-if="canUpdate" v-model="row.debit" type="number" step="0.01" min="0" :name="'debit-' + index" :id="'debit-' + index" :class="'form-control '+invalidAmount[index]" autocomplete="off" @keydown="calcTotals()" @change="formatNum(index, 'debit');invalidForm">
                <span v-else> {{row.debit}} </span>
                <small class="text-danger" v-if="invalidAmount[index]">Specify a debit</small>
              </td>
              <td>
                <input v-if="canUpdate" v-model="row.credit" type="number" step="0.01" min="0" :name="'credit-' + index" :id="'credit-' + index" :class="'form-control '+invalidAmount[index]" autocomplete="off" @keydown="calcTotals()" @change="formatNum(index, 'credit');invalidForm">
                <span v-else> {{row.credit}} </span>
                <small class="text-danger" v-if="invalidAmount[index]">or a credit</small>
              </td>
              <td style="padding-top: 18px;">
                <a v-if="canDelete && index > 0 && rows.length > 2" @click="removeElement(row.id,index)" style="cursor: pointer"><i class="fa fa-trash"></i></a>
              </td>
            </tr>
            <tr>
              <td colspan="5">
                <div class="row">
                  <div class="col-sm-2">
                    <button v-if="canUpdate" class="btn btn-primary" @click="addRow"><i class="fa fa-plus"></i> Add Row</button>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td></td>
              <td id="totals">Totals:</td>
              <td id="totals-debit"></td>
              <td id="totals-credit"></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td id="oob" :class="invalidTotals?'error':''">Out of Balance:</td>
              <td id="oob-debit"></td>
              <td id="oob-credit"></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td id="foob">Funds out of Balance:</td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr v-for="(foob, index) in outOfBalance" class="foob-color">
              <td></td>
              <td>{{ foob.name }}</td>
              <td>{{ foob.debit ? foob.debit : '' }}</td>
              <td>{{ foob.credit ? foob.credit : '' }}</td>
              <td></td>
            </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button v-if="canDelete && getCurrentRecord" @click="deleteJournalEntry()" class="btn btn-danger mr-4">
            Delete
          </button>
          <input type="button" class="btn btn-secondary" data-dismiss="modal" value="Close">
          <input v-if="canUpdate" type="button" class="btn btn-primary" @click="createJournalEntry" value="Save" :disabled="invalidForm">
        </div>
      </div>
    </div>
    <loading v-if="getIsLoadingState"></loading>
  </div>
</template>
<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import loading from '../../Loading.vue'
import Datepicker from 'vuejs-datepicker';
import accounting from 'accounting'
export default {
  components: {
    Datepicker,
    loading
  },
  mounted () {
    // console.log('Journal Entry Mounted')
    $('#create-journal-entries-modal').on('hidden.coreui.modal', () => {
      this.setCurrentRecord(null)
      this.remove = [];
    }).on('show.coreui.modal', () => {
      if(this.getCurrentRecord){
        this.registers = JSON.parse(JSON.stringify(this.getCurrentRecord.register))
        this.rows = JSON.parse(JSON.stringify(this.getCurrentRecord.splits))
        this.calcTotals()
        this.invalidForm // triggers validation
      }
      else{
        this.registers = this.setRegister()
        this.registers.journal_entry_id = this.maxjournalid//reset counter
        this.rows = this.setRows()
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
  data () {
    return {
      registers: this.setRegister(),
      state: {
        highlighted: {
          dates: [
            new Date()
          ]
        }
      },
      rows: this.setRows(),
      invalidTotals: true,
      invalidAccount: [],
      invalidAmount: [],
      invalidContact: [],
      invalidFund: [],
      fundTotals: '',
      outOfBalance: [],
      remove:[],
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
      var that = this
      function validRow(row, index) {
        that.invalidAccount[index]  = row.account_id && row.account_name ? '' : 'error';
        that.invalidFund[index]     = row.fund_id && row.fund_name ? '' : 'error';
        that.invalidContact[index]  = row.contact_id && row.contact ? '' : 'error';
        that.invalidAmount[index]  = row.credit > 0 || row.debit > 0 ? '' : 'error';

        return !that.invalidAccount[index] && !that.invalidFund[index] && !that.invalidAmount[index] && !that.invalidContact[index]
      }
      return !(this.registers.comment) || !this.rows.every(validRow) || this.invalidTotals
    },
    invalidMemo() {
      return this.registers.comment ? '' : 'error';
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
        journal_entry_id: this.maxjournalid,
        date: moment().format(),
        comment: '',
        amount: '',
        register_type: 'journal_entry'
      }

      if(content){
        register = content
      }
      return register
    },
    row(){
      return {
        account_id: "",
        account_name: "",
        fund_id: "",
        fund_number: "",
        fund_name: "",
        contact_id: "",
        contact: "",
        amount: "",
        tag: "",
        credit: "",
        debit: ""
      }
    },
    setRows(content){
      let rows = [this.row(), this.row()]
      if(content){
        rows = content
      }

      return rows;
    },
    formatNum(index, cod) {
      if (cod === 'credit') {
        this.rows[index].credit = this.formatNumber(this.rows[index].credit)
        this.rows[index].amount = this.formatNumber(this.rows[index].credit)
        this.rows[index].debit = null
      } else {
        this.rows[index].debit = this.formatNumber(this.rows[index].debit)
        this.rows[index].amount = this.formatNumber(this.rows[index].debit)
        this.rows[index].credit = null
      }
      this.calcTotals()
    },
    fetchData() {
      axios.get('/accounting/journal-entries/fetch/data')
          .then((res) => {
            this.journal_entry_id = res.data.journal_entry_id
          })
          .catch((err) => {
            console.log(err)
          })
    },
    refreshView(msg){
      $('#create-journal-entries-modal').modal('hide');
      this.$emit('flashMessage', msg)
      this.$parent.$refs.vuetable.refresh()
      this.beforeCreate = true
      this.fetchData();
      $.each(this.rows, function(index, val) {
        $('#debit-' + index).val('')
        $('#credit-' + index).val('')
      })
      this.rows = this.setRows()
      this.setRegister(null)

      $('#totals-debit').text('')
      $('#totals-credit').text('')
      $('#je-total').val('')
    },
    createJournalEntry () {
      if (this.registers.date && this.registers.comment) {
        if(this.getCurrentRecord){//its an update
          this.put({
            url: 'registers/' + this.getCurrentRecord.register.id,
            data: {
              register: this.registers,
              splits: this.rows,
              remove: this.remove
            }
          }).then(res => {
            this.refreshView('updated')
          }).catch((err) => {
            console.log(err)
          })
        }
        else{//its insert
          this.post({
            url: 'registers',
            data: {
              register: this.registers,
              splits: this.rows
            }
          }).then(res => {
            this.$emit('registry-stored')
            this.refreshView('created')
          }).catch((err) => {
            console.log(err)
          })
        }
      } else {
        Swal.fire('Enter the description field','','info')
      }
    },
    customFormatter(date) {
      return moment(date).utc().format('YYYY-MM-DD');
    },
    removeElement (row_id, index) {
      if (row_id) this.remove.push(row_id)
      this.rows.splice(index, 1);
      this.calcTotals();
      this.invalidForm // triggers validation
    },
    addRow () {
      var elem = document.createElement('tr');
      this.rows.push(this.row());
      this.calcTotals()
      this.invalidForm // triggers validation
    },
    calcTotals () {
      var debitTotals = 0
      var creditTotals = 0
      var oob = 0
      var fundTotals = {}
      var that = this

      $.each(this.rows, function(index, val) {
        if (!val.debit && !val.credit) return
        var debit_or_credit;
        if (val.debit) {
          debit_or_credit =  'debit'
          debitTotals = Number(debitTotals) + Number(val.debit)
        } else {
          debit_or_credit =  'credit'
          creditTotals = Number(creditTotals) + Number(val.credit)
        }


        $.each(that.funds, function(i, fund) {
          if (val.fund_id === fund.id) {
            if (!fundTotals[fund.name]) {
              fundTotals[fund.name] = { debit: 0, credit: 0}
            }
            fundTotals[fund.name][debit_or_credit] = Number(fundTotals[fund.name][debit_or_credit])
                ? Number(fundTotals[fund.name][debit_or_credit]) + Number(val[debit_or_credit])
                : Number(val[debit_or_credit])
            // console.log('comparing funds to rows', fundTotals[fund.name])
          }
        })
      })
      this.registers.amount = this.formatNumber(debitTotals)
      this.registers.display_amount = this.formatCurrency(debitTotals)
      $('#totals-debit').text(this.formatCurrency(debitTotals))
      $('#totals-credit').text(this.formatCurrency(creditTotals))
      oob = +debitTotals - +creditTotals

      if (oob > 0) {
        $('#oob-credit').text('')
        $('#oob-debit').text(this.formatCurrency(oob))
        this.invalidTotals = true
      } else if (oob < 0) {
        $('#oob-debit').text('')
        $('#oob-credit').text(this.formatCurrency(Math.abs(oob)))
        this.invalidTotals = true
      } else {
        $('#oob-debit').text('')
        $('#oob-credit').text('')
        this.invalidTotals = false
      }
      // console.log('all fundtotals', fundTotals, this.invalidTotals)
      $.each(fundTotals, function(index, val) {
        if (val.debit == Math.abs(val.credit)) {
          delete fundTotals[index]
        } else {
          that.invalidTotals = true
          // fundTotals[index].debit = val.debit ? val.debit - Number(val.credit) : Number(0)
          // fundTotals[index].credit = val.credit ? val.credit - Number(val.debit) : Number(0)
          fundTotals[index].oob_debit = val.debit > val.credit ? val.debit - Number(val.credit) : Number(0)
          fundTotals[index].oob_credit = val.credit > val.debit ? val.credit - Number(val.debit) : Number(0)
        }
      })

      this.fundsOutOfBalance(fundTotals)
    },
    fundsOutOfBalance(totals) {
      // console.log('fundsOutOfBalance',totals)
      var that = this
      that.outOfBalance = []
      var debit
      var credit
      $.each(totals, function(index, val) {
        // console.log(val.debit, val.credit)
        debit = val.oob_debit
        credit = val.oob_credit

        that.outOfBalance.push({
          name: index,
          debit: debit ? that.formatCurrency(Math.abs(debit)) : debit,
          credit: credit ? that.formatCurrency(Math.abs(credit)) : credit,
        });
      })
      // console.log(that.outOfBalance)
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

    findAccount(index) {
      let that = this

      let instance = $('#account-' + index).catcomplete( "instance" )
      if (instance) return

      $('#account-' + index).catcomplete({
        source: function( request, response ) {
          axios.post('/accounting/ajax/accounts/autocomplete', {search: request.term})
              .then((res) => {
                let data = {
                  value: '',
                  label: '+ Create New Account',
                  uri: '/accounting/accounts',
                  category: '',
                }
                res.data.unshift(data)
                response( res.data )
              })
        },
        minLength: 0,
        select: function( event, ui ) {
          if (ui.item.data) {
            that.rows[index].account_id = ui.item.id
            that.rows[index].account_name = ui.item.name
            that.invalidAccount[index] = ''

            if(ui.item.account_fund_id !== null) {
              axios.post('/accounting/ajax/funds/autocomplete', {account_fund_id: ui.item.account_fund_id})
                  .then((res) => {
                    that.rows[index].fund_id = res.data[0].id
                    that.rows[index].fund_name = res.data[0].name

                    if($('#account-' + index).autocomplete( "instance" ) === undefined) {
                      that.findFund(index)
                    }
                    $('#fund-' + index).val(res.data[0].label)
                    that.rows[index].fund_id = res.data[0].id
                    that.rows[index].fund_number = ui.item.id
                    that.calcTotals()
                  })
            }
          } else {
            let win = window.open(ui.item.uri, '_blank')
            win.focus()
          }
        },
      }).focus(function(){
        // $(this).data("uiAutocomplete").search($(this).val())
        $(this).data("customCatcomplete").search($(this).val())
      });

      if(instance == undefined) {
        $('#account-' + index).focus()
      }
    },
    findFund(index) {
      let that = this

      let instance = $('#fund-' + index).autocomplete( "instance" )
      if (instance) return

      $('#fund-' + index).autocomplete({
        source: function( request, response ) {
          axios.post('/accounting/ajax/funds/autocomplete', {search: request.term})
              .then((res) => {
                let data = {
                  value: '',
                  label: '+ Create New Fund',
                  uri: '/accounting/accounts'
                }
                res.data.unshift(data)
                response( res.data )
              })
        },
        minLength: 0,
        select: function( event, ui ) {
          if (ui.item.data) {
            that.rows[index].fund_id = ui.item.id
            that.rows[index].fund_number = ui.item.id
            that.rows[index].fund_name = ui.item.name
            that.invalidFund[index] = ''
          } else {
            let win = window.open(ui.item.uri, '_blank')
            win.focus()
          }
          that.calcTotals()
        },
        selectFirst: true
      }).focus(function(){
        $(this).data("uiAutocomplete").search($(this).val())
      });

      if(instance == undefined) {
        $('#fund-' + index).focus()
      }
    },
    findContact(index) {
      let that = this

      let instance = $('#contact-' + index).autocomplete( "instance" )
      if (instance) return

      $('#contact-' + index).autocomplete({
        source: function( request, response ) {
          axios.post('/crm/ajax/contacts/autocomplete', {search: request.term})
              .then((res) => {
                let data = {
                  value: '',
                  label: '+ Create New User',
                  uri: '/crm/contacts/create'
                }
                res.data.unshift(data)
                response( res.data )
              })
        },
        minLength: 0,
        select: function( event, ui ) {
          that.rows[index].contact_id = ui.item.id
          that.rows[index].contact = ui.item.label
          that.invalidContact[index] = ''
        }
      }).focus(function(){
        $(this).data("uiAutocomplete").search($(this).val());
      });

      if(instance == undefined) {
        $('#contact-' + index).focus();
      }
    },
    deleteJournalEntry () {
      Swal.fire({
        title: "Are you sure?",
        text: "Are you sure you want to delete this Journal Entry?",
        type: "question",
        showCancelButton: true,
      }).then(res => {
        if (res.value) {
          this.destroy({
            url: 'registers/' + this.registers.id,
            data: {
              split: false
            }
          }).then(result => {
            this.refreshView('deleted')
            $('#create-journal-entries-modal').modal('hide')
          })
              .catch((err) => {
                console.log(err)
              })
        }
      })
    },
  }
}
</script>

<style>
#totals, #oob, #foob, #totals-debit, #totals-credit, #oob-debit, #oob-credit, #foob-debit, #foob-credit, .foob-color {
  font-weight: bold;
}
#oob-debit, #oob-credit, #foob-debit, #foob-credit, .foob-color {
  color: #BE0707
}
.je-account-col, .je-fund-col {
  width: 25%;
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

.journal-meta {
}

.journal-amount {
  background-color: #f9f9f9;
}

input.error {
  /* border-color: red; */
  background-color: #f9ffff;
}
td.error {
  color: red;
}

#create-journal-entries-modal label {
  font-weight: bold;
}
</style>
