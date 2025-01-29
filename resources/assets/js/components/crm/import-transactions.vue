<template>
  <div class="modal" id="import_transaction_modal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Import Transactions</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <transition name="fade" mode="out-in">
              <div :key="1" v-if="import_step === 1" class="col-md-12">
                <div class="alert alert-info">
                    <h5 class="mb-0">First upload your file</h5>
                </div>
              
                <div class="form-group">
                  <label for="fileInput_" class="btn btn-primary btn-block">
                    <i class="fa fa-upload"></i> Upload File
                    <input type="file" class="d-none" id="fileInput_" @input="uploadImport" accept=".xls,.xlsx,.csv">
                  </label>
                  <p class="small"><span class="text-danger">*</span> Allowed file types: csv, xlsx, xls</p>
                </div>
              </div>
              
              <div :key="2" v-if="import_step === 2" class="col-md-12">
                <div class="row">
                  <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5 class="mb-0">Next map the columns in your file with our fields. We will remember this choice the next time you import.</h5>
                    </div>
                  
                    
                  
                    <div class="row">
                        <div class="col-6">
                            <h5>Matching Settings</h5>
                            <hr class="mt-0">
                        
                            <div class="form-group">
                              <div class="c-switch-group d-flex">
                                <label class="c-switch c-switch-label c-switch-sm c-switch-primary">
                                  <input v-model="has_header" type="checkbox" class="c-switch-input" checked>
                                  <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                                </label>
                                <label class="ml-2">First row in file is a header row</label>
                              </div>
                            </div>
                        
                            <div class="form-group">
                              <div class="c-switch-group d-flex">
                                <label class="c-switch c-switch-label c-switch-sm c-switch-primary">
                                  <input v-model="match_contact_email" type="checkbox" class="c-switch-input" checked>
                                  <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                                </label>
                                <label class="ml-2">Match contact by email</label>
                              </div>
                            </div>

                            <div class="form-group">
                              <div class="c-switch-group d-flex">
                                <label class="c-switch c-switch-label c-switch-sm c-switch-primary">
                                  <input v-model="match_contact_name" type="checkbox" class="c-switch-input" checked>
                                  <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                                </label>
                                <label class="ml-2">If not found then match contact by first and last name</label>
                              </div>
                            </div>
                            
                            <div class="form-group">
                              <div class="c-switch-group d-flex">
                                <label class="c-switch c-switch-label c-switch-sm c-switch-primary">
                                  <input v-model="create_new_contact" type="checkbox" class="c-switch-input" checked>
                                  <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                                </label>
                                <label class="ml-2">If not found then create new contact</label>
                              </div>
                            </div>
                        </div>
                    
                        <div class="col-6">
                            <h5>Update Settings</h5>
                            <hr class="mt-0">
                        
                            <div class="form-group">
                                <label class="d-block">
                                    How should we update existing contacts?
                                </label>

                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-primary toggler" :class="{active: contact_update === 'all'}"  role="button">
                                        <input type="radio" value="all" name="contact_update" v-model="contact_update"> Update all info
                                    </label>
                                    <label class="btn btn-outline-primary toggler" role="button" :class="{active: contact_update === 'missing'}">
                                        <input type="radio" value="missing" name="contact_update" v-model="contact_update"> Update only missing info
                                    </label>
                                    <label class="btn btn-outline-primary toggler" role="button" :class="{active: contact_update === 'none' || !contact_update}">
                                        <input type="radio" value="none" name="contact_update" v-model="contact_update" checked> Do not update
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="d-block">
                                    How should we update the address?
                                </label>

                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-primary toggler" :class="{active: address_update === 'update'}"  role="button">
                                        <input type="radio" value="update" name="address_update" v-model="address_update"> Update existing
                                    </label>
                                    <label class="btn btn-outline-primary toggler" role="button" :class="{active: address_update === 'create'}">
                                        <input type="radio" value="create" name="address_update" v-model="address_update"> Always create new address
                                    </label>
                                    <label class="btn btn-outline-primary toggler" role="button" :class="{active: address_update === 'none' || !address_update}">
                                        <input type="radio" value="none" name="address_update" v-model="address_update" checked> Do not update
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                              <div class="c-switch-group d-flex">
                                <label class="c-switch c-switch-label c-switch-sm c-switch-primary">
                                  <input v-model="address_mark_primary" type="checkbox" class="c-switch-input">
                                  <span class="c-switch-slider" data-checked="No" data-unchecked="Yes"></span>
                                </label>
                                <label class="ml-2">Mark address as primary?</label>
                              </div>
                            </div>
                        </div>
                    </div>
                  </div>
                </div>
                  
                <div class="row">
                  <div class="col-md-6">
                    <h5>Contact Fields</h5>
                    <hr class="mt-0">
                  
                    <div class="form-group">
                      <label>Email Field: </label>
                      <select v-model="columns.contact_email1" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                  
                    <div class="form-group">
                      <label>First Name Field <span class="text-danger">*</span>: </label>
                      <select v-model="columns.contact_first_name" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Last Name Field: </label>
                      <select v-model="columns.contact_last_name" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Phone Field: </label>
                      <select v-model="columns.contact_cell_phone" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Address Field: </label>
                      <select v-model="columns.address_mailing_address_1" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>City Field: </label>
                      <select v-model="columns.address_city" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Region Field: </label>
                      <select v-model="columns.address_region" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Country Field (2 or 3 letters): </label>
                      <select v-model="columns.address_country" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Postal Code Field: </label>
                      <select v-model="columns.address_postal_code" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <b v-show="columns.contact_first_name == null && columns.contact_last_name == null" class="text-danger">Please select contact first or last name field.</b><br>
                    <b v-show="!date_format || columns.transaction_initiated_at == null" class="text-danger">Please select date and date format.</b><br>
                    <b v-show="columns.amount == null" class="text-danger">Please select amount field.</b>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group">
                      <h5>Transactions Fields</h5>
                      <hr class="mt-0">
                    
                      <label>Date Format <span class="text-danger">*</span>: </label>
                      <select v-model="date_format" class="form-control">
                        <option value="Y-m-d">YYYY-MM-DD or YYYY/MM/DD</option>
                        <option value="m-d-Y">MM-DD-YYYY or MM/DD/YYYY</option>
                        <option value="d-m-Y">DD-MM-YYYY or DD/MM/YYYY</option>
                        <option value="Y-d-m">YYYY-DD-MM or YYYY/DD/MM</option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Date Field <span class="text-danger">*</span>: </label>
                      <select v-model="columns.transaction_initiated_at" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Amount Field <span class="text-danger">*</span>: </label>
                      <select v-model="columns.amount" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Purpose Field: </label>
                      <select v-model="columns.purpose" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Fundraiser Field: </label>
                      <select v-model="columns.campaign" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Donor Comment Field: </label>
                      <select v-model="columns.comment" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Payment Category Field: </label>
                      <select v-model="columns.payment_category" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Check Number Field: </label>
                      <select v-model="columns.payment_check_number" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Credit Card Last Four Field: </label>
                      <select v-model="columns.payment_cc_last_four" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>ACH Last Four Field: </label>
                      <select v-model="columns.payment_ach_last_four" class="form-control">
                        <option v-for="(item,index) in transactions_column" :value="index" :key="index">Column
                          {{ index + 1 }}: {{ item }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              
              <div :key="3" v-if="import_step === 3" class="col-md-12">
                <div class="alert alert-info">
                    <h5 class="mb-0" v-if="hasTransactionsToImport">Here is a preview of what we will import.</h5>
                    <h5 class="mb-0" v-else>No transactions ca be imported.</h5>
                </div>

                <div v-if="hasTransactionsToImport" class="table-responsive" style="max-height: 40vh; overflow: auto">
                  <table class="table table-hover table-sm">
                    <thead>
                    <tr>
                        <th v-if="false"><input type="checkbox" v-model="includeAll"></th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="transaction in transactions" :key="transaction.id" v-if="!transaction.error">
                        <td v-if="false"><input type="checkbox" :value="transaction.id" v-model="selected_transactions"></td>
                        <td>{{transaction.contact_first_name}}</td>
                        <td>{{transaction.contact_last_name}}</td>
                        <td>{{transaction.contact_email1}}</td>
                        <td>{{transaction.transaction_initiated_at}}</td>
                        <td class="text-right">${{ transaction.amount}}</td>
                    </tr>
                    </tbody>
                  </table>
                </div>
                
                <div v-if="hasTransactionsWithErrors">
                    <div class="alert alert-warning mt-3">
                        <h5 class="mb-0">
                            We will not be able to import these transactions due to some data being incorrect.<br>
                            You can fix the errors and import only these transactions again.
                        </h5>
                    </div>
                    
                    <div v-if="transactions.length" class="table-responsive" style="max-height: 50vh; overflow: auto">
                        <table class="table table-hover table-sm">
                          <thead>
                          <tr>
                              <th>Row</th>
                              <th>First Name</th>
                              <th>Last Name</th>
                              <th>Email</th>
                              <th>Date</th>
                              <th>Amount</th>
                              <th>Error</th>
                          </tr>
                          </thead>
                          <tbody>
                          <tr v-for="transaction in transactions" :key="transaction.id" v-if="transaction.error">
                              <td>{{transaction.row_number}}</td>
                              <td>{{transaction.contact_first_name}}</td>
                              <td>{{transaction.contact_last_name}}</td>
                              <td>{{transaction.contact_email1}}</td>
                              <td>{{transaction.transaction_initiated_at}}</td>
                              <td class="text-right">${{ transaction.amount}}</td>
                              <td v-html="transaction.error"></td>
                          </tr>
                          </tbody>
                        </table>
                    </div>
                </div>
              </div>
            </transition>

            <div class="col-md-12 mt-3 text-right">
              <button v-if="![1].includes(import_step)" @click="backStep" :disabled="false" class="btn btn-secondary float-left">
                Back
              </button>
              <button v-if="![1,3].includes(import_step)" @click="nextStep" :disabled="disableNextButton" class="btn btn-primary">
                Next
              </button>
              <button v-if="import_step == 3" @click="importTransactions" :disabled="disableImport" class="btn btn-primary">
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
import Multiselect from '../mp/multiselect'

export default {
  components: {
    Multiselect
  },
  name: 'ImportTransactions',
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
      transactions: [],
      selected_transactions: [],
      transactionsWithErrors: [],
      transactions_column: [],
      match_contact_email: true,
      match_contact_name: true,
      create_new_contact: true,
      contact_update: 'none',
      address_update: 'none',
      address_mark_primary: false,
      hasTransactionsToImport: false,
      hasTransactionsWithErrors: false
    }
  },
  mounted() {
    $('#import_transaction_modal').on('hidden.coreui.modal', () => {
      this.$emit('close')
    })
    const lastPref = JSON.parse(localStorage.getItem('importTransactionLastPreference'));
    if (lastPref){
      this.date_format = lastPref.date_format
      this.columns = _.cloneDeep(lastPref.columns)
      this.has_header = lastPref.has_header
      this.match_contact_email = lastPref.match_contact_email
      this.match_contact_name = lastPref.match_contact_name
      this.create_new_contact = lastPref.create_new_contact
      this.contact_update = lastPref.contact_update
      this.address_update = lastPref.address_update
      this.address_mark_primary = lastPref.address_mark_primary
    }
  },
  methods: {
    importTransactions() {
        let selectedTransactions = this.transactions.filter(tran => this.selected_transactions.includes(tran.id));
        let $this = this
        
        customAjax({
            url: '/crm/transactions/import',
            data: {
                date_format: this.date_format,
                match_contact_email: this.match_contact_email,
                match_contact_name: this.match_contact_name,
                create_new_contact: this.create_new_contact,
                contact_update: this.contact_update,
                address_update: this.address_update,
                address_mark_primary: this.address_mark_primary,
                transactions: JSON.stringify(selectedTransactions),
                date_format: this.date_format
            },
            success: function (response) {
                if (response.success) {
                    if ($this.transactionsWithErrors.length > 0) {
                        let errorCount = $this.has_header ? $this.transactionsWithErrors.length - 1 : $this.transactionsWithErrors.length;
                        
                        Swal.fire(response.count + ' transactions were imported successfully!', errorCount + ' transaction were not imported. Please check the file we sent and look for the error column in the end. Fix the error and import again.', 'info');
                        
                        const csvContent = mpArrayToCSV($this.transactionsWithErrors);
                        const transactionsWithErrorsBlob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
                        const transactionsWithErrorsLink = document.createElement("a");
                        const transactionsWithErrorsUrl = URL.createObjectURL(transactionsWithErrorsBlob);
                        transactionsWithErrorsLink.setAttribute("href", transactionsWithErrorsUrl);
                        transactionsWithErrorsLink.setAttribute("download", "Transactions with errors.csv");
                        document.body.appendChild(transactionsWithErrorsLink);
                        transactionsWithErrorsLink.click();
                        document.body.removeChild(transactionsWithErrorsLink);
                        URL.revokeObjectURL(transactionsWithErrorsUrl);
                    } else {
                        Swal.fire(response.count + ' transactions were imported successfully!', '', 'success');
                    }
                    
                    $('#import_transaction_modal').modal('hide');
                    $this.$parent.onClickSearch();
                    $this.import_step = 1
                    $this.transactions = []
                    $this.selected_transactions = []
                    $this.transactionsWithErrors = []
                }
            }
        });
    },
    backStep(){
      switch (this.import_step) {
        case 1:
          this.import_step--;
          break;
        case 2:
          this.transactions = [];
          this.import_step--;
          break;
        case 3:
          this.selected_transactions = []
          this.transactionsWithErrors = []
          this.import_step--;
          this.import_step--;
          break
      }
    },
    nextStep() {
      switch (this.import_step) {
        case 2:
            localStorage.setItem('importTransactionLastPreference',JSON.stringify({
                date_format: this.date_format,
                has_header: this.has_header,
                match_contact_email: this.match_contact_email,
                match_contact_name: this.match_contact_name,
                create_new_contact: this.create_new_contact,
                contact_update: this.contact_update,
                address_update: this.address_update,
                address_mark_primary: this.address_mark_primary,
                columns: this.columns
            }));
            
            const transactions = this.transactions
            this.transactions = []

            this.$store.dispatch('setIsLoadingState', true);

            axios.post('/crm/transactions/import-preview', {
                has_header: this.has_header,
                match_contact_email: this.match_contact_email,
                match_contact_name: this.match_contact_name,
                create_new_contact: this.create_new_contact,
                contact_update: this.contact_update,
                address_update: this.address_update,
                address_mark_primary: this.address_mark_primary,
                columns: this.columns,
                date_format: this.date_format,
                transactions: transactions,
            }).then((response) => {
                if (response.data.success) {
                    this.transactions = response.data.transactions
                    this.selected_transactions = response.data.transactions.map(function(tran) {
                        if (!tran.error) {
                            return tran.id;
                        }
                    });
                    this.hasTransactionsToImport = response.data.hasTransactionsToImport
                    this.hasTransactionsWithErrors = response.data.hasErrors
                    
                    if (response.data.hasErrors) {
                        this.transactionsWithErrors = response.data.transactionsWithErrors
                    }
                }
            }).catch(err => {
                Swal.fire('Oops!', 'Something went wrong, Please try again', 'error')
                this.import_step = 2;
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
      axios.post('/crm/transactions/parse-import', data).then(response => {
        this.transactions_column = response.data.columns
        this.transactions = response.data.transactions
        this.import_step++;
      }).catch(() => {
        Swal.fire('Oops!', 'Something went wrong, Please try again', 'error')
      }).finally(() => {
        this.$store.dispatch('setIsLoadingState', false)
      })
      el.value = '';
    }
  }, computed: {
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
        return !this.hasTransactionsToImport
    },
    disableNextButton() {
      switch (this.import_step) {
        case 2:
          return (this.columns.contact_first_name == null && this.columns.contact_last_name == null) || !this.date_format || this.columns.transaction_initiated_at == null || this.columns.amount == null;
      }
    },
    hasDupColumn() {
      return [...new Set(Object.values(this.columns))].length < 3
    },
    hasNullColumn() {
      return Object.values(this.columns).some(col => col === null)
    }
  }
}
</script>
<style scoped>
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
