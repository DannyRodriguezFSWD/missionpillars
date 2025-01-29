<template>
  <div class="modal" id="import_bank_transaction_modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Import Bank Transaction</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 mb-4">
              <span
                  v-if="import_step > 1"><b>Bank Institution:</b> {{ selected_institution.bank_institution }}<br></span>
              <span v-if="import_step > 2"><b>Bank Account:</b> {{ selected_bank_account.name }}<br></span>
            </div>
            <transition name="fade" mode="out-in">
              <div :key="1" v-if="import_step === 1" class="col-md-12">
                <div class="form-group">
                  <label for="select_institution">Please select Bank Institution or type the name and press enter to create a new one on import.</label>
                  <Multiselect @tag="addNewInstitution"
                               placeholder="Select or Create Bank Institution"
                               deselectLabel=""
                               tagPlaceholder="Press enter to create new bank institution." :multiple="false"
                               :allow-empty="false" id="select_institution" v-model="selected_institution"
                               :options="institution_options" track-by="id"
                               label="bank_institution"></Multiselect>
                </div>
              </div>
              <div :key="2" v-if="import_step === 2" class="col-md-12">
                <div class="form-group">
                  <label for="select_bank_account">Please select Bank Account or type the name and press enter to create a new one on import.</label>
                  <Multiselect @tag="addNewBankAccount"
                               placeholder="Select or Create Bank Account"
                               deselectLabel=""
                               tagPlaceholder="Press enter to create new bank account." :multiple="false"
                               :allow-empty="false" id="select_bank_account" v-model="selected_bank_account"
                               :options="bank_account_options" track-by="id"
                               label="name"></Multiselect>
                </div>
                <template v-if="selected_bank_account">
                  <div v-if="selected_bank_account.bank_account_id == 'new'" class="form-group">
                    <label for="selected_bank_account_type">Please Select Account Type or type the name and press enter to create a new one on import.</label>
                    <Multiselect  track-by="" label="" :multiple="false" id="selected_bank_account_type"
                                  placeholder="Select or Create Account Type"
                                  deselectLabel=""
                                  @tag="addNewBankType"
                                  tagPlaceholder="Please select or type the name and press enter to create a new one on import."
                                  :options="bank_account_type_options"
                                  v-model="selected_bank_account_type"></Multiselect>
                  </div>
                </template>
              </div>
              <div :key="3" v-if="import_step === 3" class="col-md-12">
                <div class="form-group">
                  <label for="fileInput_" class="btn btn-primary btn-block">
                    <i class="fa fa-upload"></i> Upload File
                    <input type="file" class="d-none" id="fileInput_" @input="uploadImport" accept=".xls,.xlsx,.csv">
                  </label>
                  <p class="small"><span class="text-danger">*</span> Allowed file types: csv, xlsx, xls</p>
                </div>
              </div>
              <div :key="4" v-if="import_step === 4" class="col-md-12">
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="c-switch-group d-flex">
                      <label class="mr-2">First row in file is a header row</label>
                      <label class="c-switch c-switch-label c-switch-sm c-switch-primary">
                        <input v-model="has_header" type="checkbox" class="c-switch-input" checked>
                        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Date Format: </label>
                    <select v-model="date_format" class="form-control">
                      <option value="Y-m-d">YYYY-MM-DD or YYYY/MM/DD</option>
                      <option value="m-d-Y">MM-DD-YYYY or MM/DD/YYYY</option>
                      <option value="d-m-Y">DD-MM-YYYY or DD/MM/YYYY</option>
                      <option value="Y-d-m">YYYY-DD-MM or YYYY/DD/MM</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Date Field: </label>
                    <select v-model="columns.date_column" class="form-control">
                      <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                        {{ index + 1 }}: {{ item }}
                      </option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Amount Field: </label>
                    <select v-model="columns.amount_column" class="form-control">
                      <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                        {{ index + 1 }}: {{ item }}
                      </option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Description Field: </label>
                    <select v-model="columns.description_column" class="form-control">
                      <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                        {{ index + 1 }}: {{ item }}
                      </option>
                    </select>
                  </div>
                  <b v-show="hasDupColumn || hasNullColumn" class="text-danger">Please select unique column for each field.</b><br>
                  <b v-show="!date_format" class="text-danger">Please select date format.</b>
                </div>
              </div>
              <div :key="5" v-if="import_step === 5" class="col-md-12">
                <div v-if="transactions.length" class="table-responsive" style="max-height: 50vh; overflow: auto">
                  <table class="table table-hover table-sm">
                    <thead>
                    <tr>
                      <th><input type="checkbox" v-model="includeAll"></th>
                      <th>Date</th>
                      <th>Amount</th>
                      <th>Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="transaction in transactions" :key="transaction.id">
                      <td><input type="checkbox" :value="transaction.id" v-model="selected_transactions"></td>
                      <td>{{transaction.date}}</td>
                      <td class="text-right"><span class="mr-3">{{ formatTransactionAmount(transaction.amount)}}</span></td>
                      <td>{{transaction.description}}</td>
                    </tr>
                    </tbody>
                  </table>
                </div>
                <template v-if="!selected_bank_account.account">
                  <div class="form-group my-2">
                    <label>Select an account register</label>
                    <select name="account_id" id="account_id" class="form-control" autocomplete="off"
                            v-model="selected_bank_account.account_id">
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
                  <div class="form-group" v-if="noGroups">
                    <div class="p-2 text-danger alert alert-info">
                      <h4>Uh oh! You don't have any available account registers! </h4>
                      <p>
                        Bank accounts can be linked to an Asset or Liability account with a type set to 'use as
                        register'&nbsp;<a href="/accounting/accounts" class="font-weight-bold text-primary">Go to
                        Accounts</a>
                        If you have already done this, you may have already linked bank accounts to all available
                        registers.
                      </p>
                    </div>
                  </div>
                </template>
              </div>
            </transition>

            <div class="col-md-12 mt-3 text-right">
              <button v-if="![1].includes(import_step)" @click="backStep" :disabled="false" class="btn btn-secondary float-left">
                Back
              </button>
              <button v-if="![3,5].includes(import_step)" @click="nextStep" :disabled="disableNextButton" class="btn btn-primary">
                Next
              </button>
              <button v-if="import_step == 5" @click="importTransactions" :disabled="disableImport" class="btn btn-primary">
                Import
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>
<script>
import Multiselect from '../../mp/multiselect'

export default {
  props: ['institutions','groups'],
  components: {
    Multiselect
  },
  name: 'ImportBankTransactions',
  data() {
    return {
      columns: {
        date_column: null,
        amount_column: null,
        description_column: null,
      },
      date_format: null,
      has_header: true,
      import_step: 1,
      selected_institution: null,
      selected_bank_account: null,
      selected_bank_account_type: null,
      institution_options: _.cloneDeep(this.institutions),
      bank_account_options: [],
      bank_account_type_options: ['credit', 'brokerage', 'loan', 'depository','savings','checking'],
      transactions: [],
      selected_transactions: [],
      transactions_column: [],
    }
  },
  mounted() {
    $('#import_bank_transaction_modal').on('hidden.coreui.modal', () => {
      this.$emit('close')
    })
    const lastPref = JSON.parse(localStorage.getItem('importBankTransactionLastPreference'));
    if (lastPref){
      this.date_format = lastPref.date_format
      this.columns = _.cloneDeep(lastPref.columns)
      this.has_header = lastPref.has_header
    }
  },
  methods: {
    importTransactions() {
      this.$store.dispatch('setIsLoadingState', true);
      axios.post('/accounting/bank-accounts/import_transactions', {
        bank_account: this.selected_bank_account,
        bank_institution: this.selected_institution,
        bank_account_type: this.selected_bank_account_type,
        transactions: this.transactions.filter(tran => this.selected_transactions.includes(tran.id)),
        date_format: this.date_format
      }).then((res) => {
        this.$emit('import_success',res.data);
        $('#import_bank_transaction_modal').modal('hide');
        localStorage.setItem('importBankTransactionLastPreference',JSON.stringify({
          date_format: this.date_format,
          has_header: this.has_header,
          columns: this.columns
        }));
      }).catch(() => {
      }).finally(() => {
        this.$store.dispatch('setIsLoadingState', false);
      })
    },
    addNewInstitution(newInstitution) {
      const institution = {
        id: (new Date).getTime(),
        bank_institution_id: 'new',
        bank_institution: newInstitution,
        accounts: [],
      }
      this.institution_options.push(institution)
      this.selected_institution = institution
    },
    addNewBankAccount(newBankAccount) {
      const account = {
        id: (new Date).getTime(),
        bank_account_id: 'new',
        name: newBankAccount,
        account_id: null
      }
      this.bank_account_options.push(account)
      this.selected_bank_account = account
    },
    addNewBankType(newType) {
      this.selected_bank_account_type = newType
      this.bank_account_type_options.push(newType)
    },
    backStep(){
      switch (this.import_step) {
        case 2:
          this.selected_bank_account = null;
          this.import_step--;
          break;
        case 3:
          this.selected_bank_account = null;
          this.selected_bank_account_type = null;
          this.bank_account_type_options = ['credit', 'brokerage', 'loan', 'depository', 'savings', 'checking']
          this.import_step--;
          break;
        case 4:
          this.transactions = [];
          this.transactions_column = [];
          this.columns = {date_column: null,amount_column: null,description_column: null};
          this.import_step--;
          break;
        case 5:
          this.selected_transactions = []
          this.import_step--;
          this.backStep();
          break
      }
    },
    nextStep() {
      switch (this.import_step) {
        case 1:
          if (!_.isEqual(this.selected_institution.accounts.slice(0), this.bank_account_options)) {
            this.selected_bank_account = null;
            this.bank_account_options = this.selected_institution.accounts.slice(0);
          }
          this.import_step++
          break;
        case 4:
          this.$store.dispatch('setIsLoadingState', true);
          const transactions = this.transactions
          this.transactions = []
          axios.post('/accounting/bank-accounts/preview', {
            has_header: this.has_header,
            columns: this.columns,
            date_format: this.date_format,
            transactions: transactions,
          }).then((res) => {
            this.transactions = res.data
            this.selected_transactions = res.data.map(tran => tran.id);
          }).catch(err => {
            if (typeof err.response.data == 'string') Swal.fire('Opps!', `${err.response.data}`, 'info');
            else {
              let rows = err.response.data.map(row => {
                return `<tr>
                         <td>${row.row_number}</td>
                         <td>${row.messages}</td>
                      </tr>`
              }).join('')
              let table = `<div class="table-responsive" style="max-height: 80vh">
                          <table class="table table-bordered">
                            <thead>
                            <tr>
                            <th>Record Row</th>
                            <th>Error</th>
                            </tr>
                            </thead>
                            <tbody>
                            ${rows}
                            </tbody>
                        </table>
                          </div>`
              Swal.fire({
                title: 'Invalid data',
                html: table,
              })
            }
            this.import_step = 4;
            this.backStep()
          }).finally(() => {
            this.$store.dispatch('setIsLoadingState', false);
          })
          this.import_step++
          break
        default:
          this.import_step++
      }
    },
    uploadImport(evt) {
      let data = new FormData();
      let el = document.getElementById(`${evt.target.id}`);
      data.append('file', el.files[0]);
      this.$store.dispatch('setIsLoadingState', true);
      axios.post('/accounting/bank-accounts/parseImport', data).then(response => {
        this.transactions_column = response.data.columns
        this.transactions = response.data.transactions
        this.import_step++;
      }).catch(() => {
        Swal.fire('Oops!', 'Something went wrong, Please try again', 'success')
      }).finally(() => {
        this.$store.dispatch('setIsLoadingState', false)
      })
      el.value = '';
    },
    formatTransactionAmount(val){
      return this.$parent.formatTransactionAmount(val);
    }
  }, computed: {
    noGroups() {
      if (!this.groups) return true;

      for (var i = 0; i < this.groups.length; i++) {
        if (this.groups[i].accounts.length) return false;
      }
      return true;
    },
    includeAll: {
      get: function () {
        return this.transactions ? this.selected_transactions.length == this.transactions.length : false;
      },
      set: function (value) {
        let selected = [];
        if (value) this.transactions.forEach(tran => selected.push(tran.id));
        this.selected_transactions = selected;
      }
    },
    disableImport(){
        return (!this.selected_transactions.length || !this.selected_bank_account.account_id)
    },
    disableNextButton() {
      switch (this.import_step) {
        case 1:
          return _.isEmpty(this.selected_institution)
        case 2:
          if (this.selected_bank_account) {
            if (this.selected_bank_account.bank_account_id === 'new') {
              return _.isEmpty(this.selected_bank_account) || _.isEmpty(this.selected_bank_account_type)
            }
            return _.isEmpty(this.selected_bank_account)
          }
          return true
        case 4:
          return this.hasDupColumn || this.hasNullColumn || this.date_format === null;
      }
    },
    hasDupColumn() {
      return [...new Set(Object.values(this.columns))].length < 3
    },
    hasNullColumn() {
      return Object.values(this.columns).some(col => col === null)
    }
  },
  watch:{
    institutions(){
      this.institution_options = _.cloneDeep(this.institutions)
    }
  }
}
</script>
<style scoped>
#import_bank_transaction_modal > .modal-dialog {
  max-width: 800px !important;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity .3s
}

.fade-enter {
  opacity: 0;
}

.fade-leave-to {
  opacity: 0;
}
</style>