<template>
    <div class="transaction-modal">
        <div class="modal fade" tabindex="-1" id="crm-transactions-modal">
            <div class="modal-dialog modal-lg modal-primary">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">{{ modal_title }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="onCloseModal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body mt-0 mb-0 p-1">
                        <div class="m-4">
                            <div class="row d-none">
                                <div class="col-md-4">
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-primary active is_recurring" aria-pressed="false">
                                            <input type="radio" name="is_recurring" value="0" autocomplete="off" checked="">Single Transaction
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-2" v-if="canUpdate" id="searchDuplicateTransactionContainer">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-primary" @click="showDiplicateTransaction()">
                                        <i class="fa fa-copy"></i> Duplicate Existing Transaction
                                    </button>
                                    
                                    <div class="form-group mt-2">
                                        <input class="form-control ui-autocomplete-input" autocomplete="off" name="duplicate_transaction" type="text" placeholder="Search transaction" id="search-duplicate-transaction" style="display: none;">
                                        
                                        <ul id="listOfDuplicateTransactions" class="mt-2"></ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group ">
                                        <span class="text-danger">*</span>
                                        <label for="contact">Contact's Name</label>
                                        <div v-if="canUpdate"  class="input-group">
                                          <multiselect @select="contactSelect" v-model="contact" class="form-control p-0 border-0"
                                                       @remove="contactRemove"
                                                       label="label" track-by="id" placeholder="Type to search"
                                                       :options="contacts" :multiple="false"
                                                       @tag="addNewContact"
                                                       tagPlaceholder="Press enter to create new contact."
                                                       :searchable="true" :internal-search="false"
                                                       :preserve-search="true" :options-limit="100" :limit="3"
                                                       :max-height="300" :show-no-results="false"
                                                       @search-change="asyncFind" ref="contact_multiselect">
                                          </multiselect>
                                          <span class="input-group-append" @click="addNewContact">
                                            <button class="input-group-text btn btn-primary"><i class="fa fa-plus"></i>&nbsp;Create</button>
                                          </span>
                                        </div>
                                        <div v-else>{{ contact_display_name }}</div>
                                        <input name="contact_id" v-model="transaction.contact_id" type="hidden">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <label>Transaction Splits</label>
                                    <hr class="mt-0">
                                </div>
                            </div>
                            
                            <div id="allSplitsContainer">
                                <div v-for="(split, splitIndex) in splits" class="transaction-split">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group ">
                                                <span class="text-danger">*</span>
                                                <label for="amount">Amount</label>
                                                <input v-if="canUpdate" v-model="split.amount" class="form-control calculate" step="0.01" required="" autocomplete="off" name="amount" type="number" id="amount">
                                                <div v-else>${{ split.amount }}</div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="campaign_id">Fundraiser</label>
                                                <Multiselect v-if="canUpdate" :multiple="false" :options="campaigns" :show-labels="false" @input="campaignChange($event, split)"
                                                                :close-on-select="true"
                                                                placeholder="Select Fundraiser"
                                                                v-model="split.campaign">
                                                </Multiselect>
                                                <div v-else> {{ split.campaign.name }} </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <button type="button" class="btn btn-link pull-right p-0 delete-split" title="Delete Split">
                                                <i class="fa fa-trash text-danger"></i>
                                            </button>
                                        
                                            <div class="form-group purposes-group-container">
                                                <span class="text-danger">*</span>
                                                <label for="purpose_id">Purpose</label>
                                                <Multiselect v-if="canUpdate" :multiple="false" :options="purposes" :show-labels="false" @input="purposeChange($event, split)"
                                                                :disabled="split.disable_purposes"
                                                                group-values="children" group-label="groupName" :group-select="false"
                                                                track-by="name" label="name" :close-on-select="true"
                                                                placeholder="Select Purpose"
                                                                v-model="split.chart">
                                                </Multiselect>
                                                <div v-else> {{ split.chart.name }} </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-4" v-if="link_purposes_and_accounts == 1">
                                            <p class="p-0 m-0"><i class="fa fa-info"></i> Did you know you can link purposes to income accounts?</p>
                                            <p>This makes it easier to manage you accounting entries, <a target="_blank" href="/crm/purposes">click here and start linking your purposes <i class="fa fa-external-link"></i></a></p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="tags">Tags</label>
                                                    <tags-multi-select :disabled="!canUpdate" name="tags"
                                                    :options="$store.getters.getTagOptions"
                                                    v-model="split.tags"
                                                    @tag="(tag)=>{$store.dispatch('newTag', { tag, values: split.tags });}"
                                                    ></tags-multi-select>
                                            </div>
                                        </div>

                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="tax_deductible">Tax Deductible</label>
                                                <br>
                                                <label v-if="canUpdate" class="c-switch c-switch-label  c-switch-primary">
                                                    <input v-model="split.tax_deductible" type="checkbox" name="tax_deductible" class="c-switch-input" value="1" checked="">
                                                    <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

                                                </label>
                                                <span v-else>{{ split.tax_deductible ? 'Yes' : 'No' }}</span>
                                            </div>
                                        </div>

                                        <div class="col-md-5" v-if="canUpdate && showSoftCredits">
                                            <div class="card mb-0">
                                                <div class="card-header bg-white d-table">
                                                    <label class="card-title mb-0 d-table-cell align-middle">
                                                        Soft Credits
                                                    </label>
                                                    <button class="btn btn-light pull-right" @click="openSoftCreditModal(splitIndex)">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                                <div class="card-body py-2" v-if="split.softCredits.length > 0">
                                                    <p class="mb-0 cursor-pointer" v-for="softCredit in split.softCredits" @click="editSoftCredit(softCredit.soft_credit_id, splitIndex)">
                                                        <span v-if="softCredit.remove != true">
                                                            {{ truncateString(softCredit.contact_name, 20) }} 
                                                            <span class="badge badge-success mt-1 pull-right">${{ parseFloat(softCredit.amount).toFixed(2) }}</span>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <hr class="mt-0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <button type="button" class="btn btn-sm btn-primary" @click="addNewSplit()">
                                        <i class="fa fa-plus"></i> Add Split
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="type">Transaction Type</label>
                                        <select v-if="canUpdate" v-model="transaction.type" class="form-control" id="type" name="type">
                                            <option value="donation">Donation</option>
                                            <option value="purchase">Purchase</option>
                                        </select>
                                        <div v-else>{{ transactionType }}</div>
                                    </div>
                                </div>

                                <div class="col-sm-6 transaction_pay_date">
                                    <div class="form-group">
                                        <label for="transaction_initiated_at">Transaction Date</label>
                                        <datepicker v-if="canUpdate "@selected="onSelectedDate" v-model="selected_date" :format="customFormatter" :highlighted="highlighted" :bootstrap-styling="true" input-class="bg-white" name="transaction_initiated_at" id="transaction_initiated_at" placeholder="Choose date"></datepicker>
                                        <div v-else>{{ transactionDate }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="category">Payment category</label>
                                        <select v-if="canUpdate" v-model="transaction.category" @change="onPaymentCategoryChange()" class="form-control" id="category" name="category">
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
                                        <div v-else>{{ paymentCategory }}</div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div v-if="['check', 'ach', 'cc'].includes(transaction.category)" class="form-group payment_option">
                                        <label for="payment_option_id">Payment Option *</label>
                                        <select v-if="canUpdate" v-model="transaction.payment_option_id" class="form-control" id="payment_option_id" name="payment_option_id">
                                            <option disabled value="-1">Select Option</option>
                                            <option v-if="!contact_display_name" disabled>-- For more, specify contact --</option>
                                            <option v-for="(option, index) in payment_options" :key="index" :value="option.id">
                                                {{ option.card_type }} **** {{ option.last_four }}
                                            </option>
                                            <option value="0">New {{ payment_option_label }}</option>
                                        </select>
                                        <div v-else>{{ paymentOption }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div v-if="['cc'].includes(transaction.category) && transaction.payment_option_id == 0" class="form-group">
                                        <label for="card_type">Credit Card Type</label>
                                        <select v-if="canUpdate" v-model="transaction.card_type" class="form-control" id="card_type" name="card_type">
                                            <option disabled value="0">Select Option</option>
                                            <option value="Visa">Visa</option>
                                            <option value="Master Card">Master Card</option>
                                            <option value="JCB">JCB</option>
                                            <option value="Discover">Discover</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div v-else>{{ creditCardType }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div v-if="['ach', 'cc'].includes(transaction.category) && transaction.payment_option_id == 0" class="form-group">
                                        <label>
                                            <span class="text-danger">*</span> Enter first four digits of
                                            <span v-if="transaction.category == 'check'">Checking Account</span>
                                            <span v-if="transaction.category == 'ach'">ACH</span>
                                            <span v-if="transaction.category == 'cc'">Credit Card</span>
                                        </label>
                                        <input v-if="canUpdate" v-model="transaction.first_four" class="form-control" maxlength="4" autocomplete="off" name="first_four" type="text">
                                        <div v-else>{{ transaction.first_four ? transaction.first_four : ''}}</div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div v-if="['check', 'ach', 'cc'].includes(transaction.category) && transaction.payment_option_id == 0" class="form-group">
                                        <span class="text-danger" v-if="['ach', 'cc'].includes(transaction.category) && transaction.payment_option_id == 0">*</span>
                                        <label>
                                            Enter last four digits of
                                            <span v-if="transaction.category == 'check'">Checking Account</span>
                                            <span v-if="transaction.category == 'ach'">ACH</span>
                                            <span v-if="transaction.category == 'cc'">Credit Card</span>
                                        </label>
                                        <input v-if="canUpdate" v-model="transaction.last_four" class="form-control" maxlength="4" autocomplete="off" name="last_four" type="text">
                                        <div v-else>{{ transaction.last_four }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div v-show="['check'].includes(transaction.category) && transaction.payment_option_id >= 0" class="form-group">
                                        <label> Check # </label>
                                        <input v-if="canUpdate" v-model="transaction.check_number" class="form-control" min="0" autocomplete="off" name="check_number" type="text">
                                        <div v-else>{{ transaction.check_number }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="channel">Channel</label>
                                        <select v-if="canUpdate" v-model="transaction.channel" class="form-control" id="channel" name="channel">
                                            <option value="face_to_face">Face to face</option>
                                            <option value="mail">Mail</option>
                                            <option value="ncf">Appreciated Stock Through NCF</option>
                                            <option value="event">Event</option>
                                            <option value="other">Other</option>
                                            <option value="unknown">Unknown</option>
                                            <option value="ctg_direct">CTG - Direct</option>
                                            <option value="ctg_embed">CTG - Website Embedded Form</option>
                                            <option value="ctg_text_link">CTG - Text For Link</option>
                                            <option value="ctg_text_give">CTG - Text To Give</option>
                                            <option value="website">(deprecated) Website</option>
                                        </select>
                                        <span v-else>{{ transactionChannel }}</span>
                                    </div>
                                </div>
                                
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="transaction_initiated_at">Deposit Date</label>
                                        <datepicker v-if="canUpdate" v-model="transaction.deposit_date" :format="customFormatterUTC" :highlighted="highlighted" :bootstrap-styling="true" input-class="bg-white" name="deposit_date" id="deposit_date" placeholder="Choose date"></datepicker>
                                        <div v-else>{{ transaction.deposit_date }}</div>
                                    </div>
                                </div>
                                
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="acknowledged">Acknowledged</label>
                                        <br>
                                        <label v-if="canUpdate" class="c-switch c-switch-label  c-switch-primary">
                                            <input type="checkbox" name="acknowledged" class="c-switch-input" value="1" v-model="transaction.acknowledged">
                                            <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

                                        </label>
                                        <span v-else>{{ transaction.acknowledged ? 'Yes' : 'No' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <label for="comment">Comment from donor</label>
                                    <textarea :disabled="!canUpdate" v-model="transaction.comment" class="form-control" name="comment" rows="3" id="comment"></textarea>
                                </div>
                            </div>

                            <div class="row mt-4" v-if="canUpdate">                            
                                <div class="col-md-6">
                                    <div class="card mb-0">
                                        <div class="card-header bg-white d-table">
                                            <label class="card-title mb-0 d-table-cell align-middle">
                                                Attachments
                                            </label>
                                            <label class="btn btn-light mb-0 pull-right">
                                                <i class="fa fa-plus"></i> <input type="file" class="d-none" @change="addAttachment">
                                            </label>
                                        </div>
                                        <div class="card-body py-2" v-if="transaction.attachments.length > 0">
                                            <p class="mb-0 text-muted" v-for="attachment in transaction.attachments">
                                                <span v-if="attachment.remove != true">
                                                    <a :href="'/crm/documents/'+attachment.attachment_id+'/download'" target="_blank">{{ truncateString(attachment.name, 35) }}</a>
                                                    <i class="fa fa-trash cursor-pointer text-danger mt-1 pull-right" @click="removeAttachment(attachment.attachment_id)"></i> 
                                                    <span class="pull-right">{{ bytesToSize(attachment.size) }}</span>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      <div v-show="register_id && action != 'insert'" class="row mx-2">
                        <div class="col-md-12">
                          <div class="alert alert-info">
                            This transaction is linked to an <a target="_blank" :href="`/accounting/registers/${this.register_id}/view_transaction`">Accounting Transaction</a>.
                          </div>
                        </div>
                      </div>
                    </div>

                  <div class="modal-footer">
                    <button v-if="canDelete && action != 'insert'" class="btn btn-danger mr-2" @click="deleteTransaction" :title="deleteTitle" :disabled="disableDelete">Delete</button>
                    
                    <div v-if="canUpdate && action === 'insert'" class="btn-group mr-2">
                        <button type="button" class="btn btn-primary" :disabled="isDisabled" @click="onSaveTransaction(false)">Save</button>
                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" :disabled="isDisabled">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" @click="onSaveTransaction(false)">Save</a>
                            <a class="dropdown-item" href="#" @click="onSaveTransaction(true)">Save And Add Another</a>
                        </div>
                    </div>
                    
                    <button v-if="canUpdate && action != 'insert'" class="btn btn-primary mr-2" :disabled="isDisabled"
                            @click="onSaveTransaction(false)">
                      Save
                    </button>
                    <button class="btn btn-secondary" data-dismiss="modal" @click="onCloseModal">
                      Close
                    </button>
                  </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="create-new-contact-modal">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Create New Contact</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="onCloseModal">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
              <div class="modal-body">
                <form @submit.prevent="saveNewContact($event)">
                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label for="first_name_field">Type <span class="text-danger">*</span></label>
                        <select required id="contact_type_field" name="type" class="form-control" v-model="contact_type">
                            <option value="person" selected>Person</option>
                            <option value="organization">Organization</option>
                        </select>
                      </div>
                      <div v-if="contact_type === 'person'">
                        <div class="form-group">
                          <label for="first_name_field">First Name</label>
                          <input type="text" @keyup="searchExisting" required id="first_name_field" name="first_name" class="form-control" placeholder="First Name">
                        </div>
                        <div class="form-group">
                          <label for="last_name_field">Last Name</label>
                          <input type="text" @keyup="searchExisting" required id="last_name_field" name="last_name" class="form-control" placeholder="Last Name">
                        </div>
                      </div>
                      <div v-if="contact_type === 'organization'">
                        <div class="form-group">
                          <label for="organization_name">Organization Name</label>
                          <input type="text" @keyup="searchExisting" required id="organization_name_field" name="organization_name" class="form-control" placeholder="Organization Name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="email_field">Email</label>
                        <input type="email" @keyup="searchExisting" id="email_field" name="email_1" class="form-control" placeholder="example@example.com">
                      </div>
                      <div class="form-group">
                        <label for="mailing_address_1">Mailing Address</label>
                        <input type="text" @keyup="searchExisting" id="mailing_address_1" name="mailing_address_1" class="form-control" placeholder="Mailing Address">
                      </div>
                    </div>
                    <div class="col-12 text-right">
                      <button role="button" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;Save</button>
                      <button role="button" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    <div v-show="suggested_contacts.length" class="col-12">
                      <div class="font-weight-bold text-center mb-2">Suggested Contacts</div>
                      <button class="btn btn-secondary btn-block mb-2" v-for="contact in suggested_contacts" :key="contact.id" @click="pickSuggested(contact)">{{contact.label}}</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal fade" id="soft-credit-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Soft Credit</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <span class="text-danger">*</span>
                            <label for="contact">Contact's Name</label>
                            <div v-if="canUpdate"  class="input-group">
                                <multiselect @select="contactSelect" v-model="contactSoftCredit" class="form-control p-0 border-0"
                                             @remove="contactRemove"
                                             label="label" track-by="id" placeholder="Type to search"
                                             :options="contacts" :multiple="false"
                                             @tag="addNewContact"
                                             tagPlaceholder="Press enter to create new contact."
                                             :searchable="true" :internal-search="false"
                                             :preserve-search="true" :options-limit="100" :limit="3"
                                             :max-height="300" :show-no-results="false"
                                             @search-change="asyncFind" ref="contact_multiselect">
                                </multiselect>
                                <span class="input-group-append" @click="addNewContact">
                                    <button class="input-group-text btn btn-primary"><i class="fa fa-plus"></i>&nbsp;Create</button>
                                </span>
                            </div>
                            <input name="contact_id_soft_credit" v-model="softCredit.contact_id" type="hidden">
                            <input name="contact_name_soft_credit" v-model="softCredit.contact_name" type="hidden">
                        </div>
                        
                        <div class="form-group">
                            <span class="text-danger">*</span>
                            <label for="contact">Amount</label>
                            <input type="number" v-model="softCredit.amount" class="form-control" name="soft_credit_amount">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="addSoftCredit" v-if="softCreditMode == 'create'">Add</button>
                        <button type="button" class="btn btn-danger" @click="removeSoftCredit" v-if="softCreditMode == 'edit'">Delete</button>
                        <button type="button" class="btn btn-primary" @click="updateSoftCredit" v-if="softCreditMode == 'edit'">Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="onCloseModalSoftCredit">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    import Datepicker from 'vuejs-datepicker'
    import moment, { locale, utc } from 'moment'

    import tagsMultiSelect from '../../mp/tags-multiselect.vue'
    import Multiselect from "../../mp/multiselect";

    export default {
        name: 'CRMTransaction',
        props: [
            'campaigns',
            'purposes',
            'highlighted',
            'link_purposes_and_accounts',
            'contact_id',
            'contact_name',
            'endpoint',
            "master_id",
            "pledge_status",
            "create_pledge",
            "permissions",
            "campaign_id",
            "purpose_id",
        ],
        components: {
          Multiselect,
            Datepicker,
            tagsMultiSelect,
        },
        data() {
            return {
                disableDelete: null,
                deleteTitle: null,
                register_id: null,
                modal_title: 'Create new transaction',
                organization_purpose: null,
                disabledDates: {
                    to: new Date(moment().subtract(800, 'days') - 8640000)
                },
                selected_date: MPHelper.getToday(),
                contact_display_name: '',
                transaction_id: 0,
                payment_options: [],
                transaction: this.setTransaction(),
                action: 'insert',
                transaction_tag_options: [],
                payment_option_id: 0,

                loading_payment_options: false,
                contacts: [],
                suggested_contacts: [],
                contact: null,
                isEditingSoftCredits: false,
                softCreditMode: 'create',
                contactSoftCredit: null,
                softCredit: [],
                contact_display_name_soft_credit: '',
                showSoftCredits: true,
                attachments: [],
                chart: null,
                campaign: null,
                contact_type: 'person',
                campaignName: null,
                purposeName: null,
                duplicateTransactionsList: [],
                splits: [],
                deleteSplits: [],
                softcreditSplitIndex: null
            }
        },
        mounted() {
            $('[data-toggle="tooltip"]').tooltip();
            document.querySelector("#crm-transactions-modal").addEventListener('shown.coreui.modal', () => {
                this.onPaymentCategoryChange(true)
//                if(this.contact_id > 0){
//                    this.contact = {id: this.contact_id, label: this.contact_name}
//                    this.contacts = [this.contact];
//                    this.transaction.contact_id = this.contact_id
//                    this.transaction.contact_display_name = this.contact_name
//                    this.contact_display_name = this.contact_name
//                }
                // HACK somehow this payment_option_id is being cleared by time we get here.
                // console.log('mounted',this.transaction.payment_option_id, this.payment_option_id)
                this.$set(this.transaction,'payment_option_id',this.payment_option_id)
                this.$set(this.transaction,'category',this.transaction.category)
                
                if (this.modal_title === 'Create new transaction') {
                    $('.multiselect input:eq(0)').focus();
                    this.addNewSplit();
                }
                
                this.campaignName = this.findCampaignName();
                this.purposeName = this.findPurposeName();
            })
            $('#crm-transactions-modal').on('hidden.coreui.modal', ()=>{this.onCloseModal()})
            $('#contact').autocomplete({
                source: ( request, response ) => {
                    $.ajax({
                        url: "/crm/ajax/contacts/autocomplete",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function( data ) {
                            response( data );
                        }
                    });
                },
                minLength: 2,
                select: ( event, ui ) => {
                    this.contact_display_name = ui.item.label
                    this.transaction.contact_id = ui.item.id
                    this.onPaymentCategoryChange()
                }
            });

            $('#soft-credit-modal').on('hidden.coreui.modal', ()=>{this.onCloseModalSoftCredit()})
            
            $('#search-duplicate-transaction').autocomplete({
                source: function (request, response) {
                    // Fetch data
                    $.ajax({
                        url: "/crm/ajax/transactions/autocomplete",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: ( event, transaction ) => {
                    this.setSelectedTransaction(transaction.item.split);
                    this.action = 'insert'
                },
                close(){
                    $('#search-duplicate-transaction').val('');
                }
            })
            
            $('#allSplitsContainer').on('click', '.delete-split', function(e) {
                $(this).parents('.transaction-split').remove();
            });

            this.$store.dispatch('setTagOptions')
        },
        computed: {
          filteredPurposes(){
            return this.purposes.filter(purpose => purpose.deleted_at === null || purpose.id === 0 || purpose.id == this.transaction.purpose_id)
          },
            ...mapGetters([
                'getIsLoadingState',
                'getHelperOrganizationPurposeState',
            ]),
            isDisabled(){
                if (this.transaction.contact_id == null) return true
                        
                for (var i=0; i<this.splits.length; i++) {
                    if (this.splits[i].amount <= 0 || this.splits[i].amount == null || this.splits[i].amount == '' || this.splits[i].purpose_id <= 0 || this.splits[i].purpose_id == undefined) {
                        return true;
                    }
                }
                
                if(['check', 'cash', 'cashapp', 'venmo', 'paypal', 'facebook', 'goods', 'other', 'unknown'].includes(this.transaction.category)) return false

                if (this.transaction.payment_option_id < 0) return true
                if(this.transaction.payment_option_id == 0){
                    if (!this.transaction.last_four || this.transaction.last_four.length < 4) return true
                    if(['ach', 'cc'].includes(this.transaction.category)){
                        if (!this.transaction.first_four || this.transaction.first_four.length < 4) return true
                        if(this.transaction.category == 'cc' && this.transaction.card_type == '0') return true
                    }
                }
                return false
            },
            payment_option_label() {
                var label = ''
                switch (this.transaction.category) {
                    case 'check':
                    label = 'Checking Account'
                    break;
                    case 'ach':
                    label = 'ACH'
                    break;
                    case 'cc':
                    label = 'Credit Card'
                    break;
                }
                return label
            },
            canDelete() {
                return this.permissions['transaction-delete'] || !this.isEditing && this.permissions['transaction-create']
            },
            canUpdate() {
                return this.permissions['transaction-update'] || !this.isEditing && this.permissions['transaction-create']
            },
            isEditing() {
                return this.transaction_id != 0
            },
            transactionType() {
                return this.toTitleCase(this.transaction.type)
            },
            transactionDate() {
                return this.customFormatter(this.selected_date)
            },
            paymentCategory() {
                switch (this.transaction.category) {
                    case 'cash':
                    return 'Cash';
                    case 'check':
                    return 'Check';
                    case 'ach':
                    return 'ACH';
                    case 'cc':
                    return 'Credit Card';
                    case 'cashapp':
                    return 'Cashapp';
                    case 'venmo':
                    return 'Venmo';
                    case 'paypal':
                    return 'Paypal';
                    case 'facebook':
                    return 'Facebook';
                    case 'goods':
                    return 'Goods';
                    case 'other':
                    return 'Other';
                    case 'unknown':
                    return 'Unknown';
                }
                return this.transaction.category
            },
            paymentOption() {
                var option = this.payment_options.find(o => o.id == this.payment_option_id)
                return option ? ('****' + (option.last_four ? option.last_four : '')) : ''
            },
            creditCardType() {
                return this.transaction.card_type ? this.transaction.card_type : ''
            },
            transactionChannel() {
                return this.toTitleCase(this.transaction.channel)
            },
        },
        methods: {
            searchExisting: _.debounce(function (e){
              if (e.target.value.length > 1) {
                axios.post("/crm/ajax/contacts/autocomplete",{
                  search: e.target.value
                }).then((result) => this.suggested_contacts = result.data)
              }
            },700),
            pickSuggested(contact){
                if (this.isEditingSoftCredits) {
                    this.contact_display_name_soft_credit = contact.label
                    this.softCredit.contact_id = contact.id;
                    this.softCredit.contact_name = contact.label;
                    this.contactSoftCredit = contact;
                } else {
                    this.contact_display_name = contact.label;
                    this.transaction.contact_id = contact.id;
                    this.contact = contact;
                }
                
                this.suggested_contacts = [contact];
                this.contacts = [contact];
                $('#create-new-contact-modal').modal('hide');
                document.querySelector('#create-new-contact-modal').querySelector('form').reset();
            },
            saveNewContact(event) {
                this.$store.dispatch('setIsLoadingState', true)
                axios.post('/crm/contacts',new FormData(event.target))
                .then(res => {
                    let contact = res.data
                    contact.label = contact.full_name + ' ('+ contact.email_1 +')';
                    
                    if (this.isEditingSoftCredits) {
                        this.contact_display_name_soft_credit = contact.label;
                        this.softCredit.contact_id = contact.id;
                        this.softCredit.contact_name = contact.label;
                        this.contactSoftCredit = contact;
                    } else {
                        this.contact_display_name = contact.label;
                        this.transaction.contact_id = contact.id;
                        this.contact = contact;
                    }
                    
                    this.contacts = [contact];
                    $('#create-new-contact-modal').modal('hide');
                    event.target.reset();
                }).finally(() => {
                    this.$store.dispatch('setIsLoadingState', false)
                })
            },
            addNewContact(search){
              this.suggested_contacts = [];
              if (typeof search == "string")$('#first_name_field').val(search);
              $('#create-new-contact-modal').modal('show');
            },
            contactSelect(contact){
                if (this.isEditingSoftCredits) {
                    this.contact_display_name_soft_credit = contact.label
                    this.softCredit.contact_id = contact.id;
                    this.softCredit.contact_name = contact.label;
                } else {
                    this.contact_display_name = contact.label
                    this.transaction.contact_id = contact.id
                    this.onPaymentCategoryChange()
                }
            },
            contactRemove(){
                if (this.isEditingSoftCredits) {
                    this.contact_display_name_soft_credit = '';
                    this.softCredit.contact_id = null;
                    this.softCredit.contact_name = null;
                } else {
                    this.contact_display_name = ""
                    this.transaction.contact_id = null
                }
            },
            asyncFind: _.debounce(function (query) {
              axios.post("/crm/ajax/contacts/autocomplete",{
                search: query
              }).then((result) => this.contacts = result.data)
            },700) ,
            deleteTransaction(){
                Swal.fire({
                  title: 'Delete Transaction?',
                  text: 'Are you sure? You cannot revert this action.',
                  type: 'question',
                  showLoaderOnConfirm: true,
                  showCancelButton: true,
                  preConfirm: () => {
                    return axios.delete(`/crm/transactions/${this.transaction_id}`)
                        .then(() => {
                          return true;
                        })
                        .catch(({response}) => {
                          return response
                        });
                  }
                }).then(({value}) => {
                  if (value == true){
                    Swal.fire('Success','Transaction Deleted','success')
                    this.$emit('transactionDeleted')
                    this.transaction=this.setTransaction()
                    crm_transactions_modal.hide();
                  }else {
                    if (value.data.type == 'api'){
                      Swal.fire('Giving App transaction!',response.data.message,'info')
                    }else if (value.data.type == 'linked') {
                      Swal.fire({
                        title: 'Transaction is linked',
                        type: "info",
                        html:`Sorry, you cannot delete this transaction until you unlink it from the accounting transaction you created. <br><br><a target="_blank" href="/accounting/registers/${this.register_id}/view_transaction">View Transaction</a>`
                      })
                    }else {
                      Swal.fire('Oops!','Something went wrong','info')
                    }
                  }
                })
            },
            ...mapActions([
                'post',
                'get',
                'put',
                'destroy',
                'setHelperOrganizationPurposeState',
            ]),
            setSelectedTransaction(transaction){
                this.disableDelete = transaction.transaction.system_created_by === 'Continue to Give'
                this.register_id = transaction.registry.length ? transaction.registry[0].register_id : null;
                this.transaction=this.setTransaction(transaction)
                this.onPaymentCategoryChange()
                this.action = 'edit'
                this.deleteTitle = this.disableDelete ? 'You cannot delete transactions that have origniated from Continue to Give' : 'You can only delete transactions that are not linked with the giving app.'
            },
            setTransaction(t){
                this.modal_title = 'Create new transaction'
                var transaction = {
                    acknowledged: 0,
                    contact_id: null,
                    campaign_id: this.campaign_id ? this.campaign_id : 1,
                    purpose_id: this.purpose_id ? this.purpose_id : 0,
                    type: 'donation',
                    amount: null,
                    category: 'check',
                    transaction_initiated_at: MPHelper.getToday(),
                    // payment_option: {id: null, category: 'check' },
                    check_number: null,
                    channel: 'unknown',
                    tags: [],
                    tax_deductible: 1,
                    comment: null,
                    card_type: '0',
                    first_four: '',
                    last_four: '',
                    master_id: this.master_id,
                    pledge_status: this.pledge_status,
                    create_pledge: this.create_pledge,
                    payment_option_id: 0,
                    softCredits: [],
                    attachments: []
                }
                if (t == undefined) this.payment_option_id = 0
                this.contact_display_name = null
                this.selected_date = transaction.transaction_initiated_at

                if(t){
                    $('#searchDuplicateTransactionContainer').hide();
                    
                    this.transaction_id = t.id // must be first, see this.canUpdate

                    this.modal_title = this.canUpdate ? 'Edit Transaction' : 'View Transaction'
                    
                    if (t.transaction.parent_transaction_id) {
                        this.modal_title = this.canUpdate ? 'Edit Soft Credit' : 'View Soft Credit';
                    }
                    
                    this.contact_display_name = t.transaction.contact.full_name + ' ('+t.transaction.contact.email_1+')'
                    transaction.contact_id = t.transaction.contact.id

                    this.contact = {id: transaction.contact_id, label: this.contact_display_name}
                    this.contacts = [this.contact];

                    transaction.campaign_id = t.campaign_id
                    transaction.purpose_id = t.purpose_id
                    transaction.type = t.type
                    transaction.amount = t.amount
                    transaction.acknowledged = t.transaction.acknowledged
                    transaction.transaction_initiated_at = t.transaction.transaction_initiated_at

                    transaction.check_number = t.transaction.check_number
                    transaction.channel = t.transaction.channel
                    transaction.tags = t.tags
                    transaction.tax_deductible = t.tax_deductible
                    transaction.comment = t.transaction.comment
                    transaction.deposit_date = t.transaction.deposit_date

                    // transaction.payment_option = t.transaction.payment_option
                    transaction.category = t.transaction.payment_option ? t.transaction.payment_option.category : null
                    transaction.payment_option_id = t.transaction.payment_option ? t.transaction.payment_option.id : null
                    this.payment_option_id = t.transaction.payment_option ? t.transaction.payment_option.id : null
                    transaction.card_type = t.transaction.payment_option ? t.transaction.payment_option.card_type : null
                    transaction.first_four = t.transaction.payment_option ? t.transaction.payment_option.first_four : null
                    transaction.last_four = t.transaction.payment_option ? t.transaction.payment_option.last_four : null
                    this.selected_date = this.parseLocalDateTime(transaction.transaction_initiated_at, true)
                    
                    if (t.transaction.soft_credits.length > 0) {
                        for (let i = 0; i < t.transaction.soft_credits.length; i++) {
                            transaction.softCredits.push({
                                contact_id: t.transaction.soft_credits[i].contact_id,
                                contact_name: t.transaction.soft_credits[i].contact.full_name + ' ('+ t.transaction.soft_credits[i].contact.email_1 +')',
                                amount: t.transaction.soft_credits[i].splits[0].amount,
                                soft_credit_id: t.transaction.soft_credits[i].id,
                                isTemp: false
                            });
                        }
                    }
                    
                    this.showSoftCredits = !t.transaction.parent_transaction_id;
                    
                    if (t.transaction.documents.length > 0) {
                        for (let i = 0; i < t.transaction.documents.length; i++) {
                            transaction.attachments.push({
                                name: t.transaction.documents[i].name,
                                size: t.transaction.documents[i].size,
                                attachment_id: t.transaction.documents[i].uuid,
                                isTemp: false,
                                remove: false
                            });
                        }
                    }
                    
                    this.chart = {'id': t.purpose.id, 'name': t.purpose.name}
                    this.campaign = {'id': t.campaign.id, 'name': t.campaign.name}
                    
                    for (var i=0; i<t.transaction.splits.length; i++) {
                        this.splits[i] = t.transaction.splits[i];
                        
                        this.splits[i].campaign = {
                            'id': this.splits[i].campaign.id ? this.splits[i].campaign.id : 1,
                            'name': this.splits[i].campaign.name,
                        }
                        
                        this.splits[i].chart = {
                            'id': this.splits[i].purpose.id,
                            'name': this.splits[i].purpose.name,
                        }
                        
                        this.splits[i].disable_purposes = false
                        
                        this.splits[i].softCredits = []
                    }
                } 

                return transaction
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
                return moment(local_date).format('MM/DD/YYYY HH:mm a')
            },
            customFormatter(date) {
                return moment(date).format('YYYY-MM-DD');
            },
            customFormatterUTC(date) {
                return moment(date).utc().format('YYYY-MM-DD');
            },
            onSelectedDate(date){
                // console.log('onSelectedDate', date)
                if (date) this.selected_date = moment(date)
            },
            onPaymentCategoryChange(mounted){
                this.getPaymentOptions(mounted)
                this.transaction.category
            },
            onSaveTransaction(addAnother){
                for (var i=0; i<this.splits.length; i++) {
                    var totalSoftCreditAmount = 0;
                    
                    if (this.splits[i].softCredits.length > 0) {
                        for (var j=0; j<this.splits[i].softCredits.length; j++) {
                            totalSoftCreditAmount = totalSoftCreditAmount + parseFloat(this.splits[i].softCredits[j].amount);
                        }

                        if (totalSoftCreditAmount > parseFloat(this.splits[i].amount)) {
                            return Swal.fire('Total soft credit amount exceeds the amount of split number '+ (i+1), '', 'error');
                        }
                    }
                }
                
                let service = this.endpoint

                let test_date = this.selected_date
                    ? moment(this.selected_date)
                    : moment(this.transaction.transaction_initiated_at)
                // console.log('selected_date', this.selected_date)
                // console.log('test_date', test_date)

                this.transaction.transaction_initiated_at = test_date.utc().format('YYYY-MM-DD HH:mm:ss')
                // console.log('transaction_initiated_at', this.transaction.transaction_initiated_at)
                this.transaction.tags = this.transaction.tags ? this.transaction.tags.map(tag => tag.id) : []

                this.transaction.splits = this.splits

                if(this.action == 'edit'){

                    service = this.endpoint + '/' + this.transaction_id
                    this.put({
                        url: service,
                        data: this.transaction
                    }).then(result => {
                        this.action = 'insert'
                        this.$emit('transactionStored')
                        this.transaction=this.setTransaction()
                        crm_transactions_modal.hide();
                    })
                }
                else{
                    // let transaction_initiated_at = moment($('#transaction_initiated_at').val()).utc()
                    // this.transaction.transaction_initiated_at = transaction_initiated_at.format('YYYY-MM-DD HH:mm:ss')
                    this.post({
                        url: service,
                        data: this.transaction
                    }).then(result => {
                        this.action = 'insert'
                        this.transaction=this.setTransaction()
                        
                        if (!addAnother) {
                            crm_transactions_modal.hide();
                        }
                        
                        this.$emit('transactionStored', 1)
                        
                        if (addAnother) {
                            this.onCloseModal();
                            this.addNewSplit();
                        }
                    })
                }
            },
            onTagAdded(tag) {
                let self = this, new_tag;

                axios.post("/crm/ajax/tags",{'tag': tag_name})
                .then( response => {
                    let new_tag = response.data
                    new_tag.id = response.data.id
                    new_tag.label = response.data.name
                    self.transaction_tag_options.push(new_tag)
                    self.transaction.tags.push(new_tag)
                    // window.ttags = self.transaction.tags
                })
                .catch( error => console.log('error',error) )
            },
            onCloseModal() {
                this.transaction=this.setTransaction()
                this.setPaymentOptions()
                this.action = 'insert'
                this.$refs.contact_multiselect.search = "";
                this.contacts = [];
                this.contact = null;
                this.chart = null;
                this.campaign = null;
                this.splits = [];
                $('#searchDuplicateTransactionContainer').show();
            },
            setPaymentOptions(options) {
                if (!options) options = []
                this.payment_options = options.slice()
            },
            getPaymentOptions(surpressalerts) {
                if (['cash', 'cashapp', 'venmo', 'paypal', 'facebook', 'goods', 'other', 'unknown'].includes(this.transaction.category)) {
                    this.setPaymentOptions()
                }
                else if (!this.loading_payment_options){
                    this.loading_payment_options = true
                    this.get({
                        url: '/crm/ajax/contacts/payment/options',
                        data: {
                            id: this.transaction.contact_id,
                            category: this.transaction.category
                        }
                    }).then(result => {
                        if(result.data.status != 'null_contact'){
                            this.setPaymentOptions(result.data)
                            if (this.payment_options.length == 0) {
                                this.$set(this.transaction,'payment_option_id',0)
                            } else {
                                let setDefaultPaymentOption = true;
                                
                                for (let i = 0; i < result.data.length; i++) {
                                    if (this.transaction.payment_option_id && result.data[i].id === this.transaction.payment_option_id) {
                                        setDefaultPaymentOption = false;
                                        break;
                                    }
                                }
                                
                                if (setDefaultPaymentOption) {
                                    this.$set(this.transaction, 'payment_option_id', result.data[result.data.length-1].id)
                                }
                            }
                        }
                        else if(!surpressalerts){
                            Swal.fire({
                              title: 'No contact selected!',
                              text: 'Select registered contact',
                              type: 'info',
                              onAfterClose(){
                                document.querySelector('#contact').focus()
                              }
                            })
                        }
                        this.loading_payment_options = false
                    })
                }
            },
            setPurposeID() {
                if (['cash', 'check', 'cashapp', 'venmo', 'paypal', 'facebook', 'goods', 'other', 'unknown'].includes(this.transaction.category)) {
                    if(this.getHelperOrganizationPurposeState && Object.keys(this.getHelperOrganizationPurposeState).length > 0){
                        this.transaction.purpose_id = this.getHelperOrganizationPurposeState.id
                    }
                }
            },
            toTitleCase(str) {
                return str.replace(/(?:^|\s)\w/g, function(match) {
                    return match.toUpperCase();
                });
            },
            openSoftCreditModal(index) {
                $('#soft-credit-modal').modal('show');
                this.isEditingSoftCredits = true;
                this.softcreditSplitIndex = index;
            },
            onCloseModalSoftCredit() {
                this.$refs.contact_multiselect.search = "";
                this.contacts = [];
                this.contactSoftCredit = null;
                this.softCredit.amount = null;
                this.isEditingSoftCredits = false;
                this.softCreditMode = 'create';
            },
            addSoftCredit() {
                if (!this.softCredit.contact_id) {
                    return Swal.fire('Please select a contact', '', 'error');
                }
                
                if (!this.softCredit.amount || this.softCredit.amount <= 0) {
                    return Swal.fire('Please add the amount', '', 'error');
                }
                
                if (!this.splits[this.softcreditSplitIndex].softCredits) {
                    this.splits[this.softcreditSplitIndex].softCredits = [];
                }
                
                this.splits[this.softcreditSplitIndex].softCredits.push({
                    contact_id: this.softCredit.contact_id,
                    contact_name: this.softCredit.contact_name,
                    amount: this.softCredit.amount,
                    soft_credit_id: 'temp_' +  this.splits[this.softcreditSplitIndex].softCredits.length,
                    isTemp: true
                });
                
                $('#soft-credit-modal').modal('hide');
            },
            removeSoftCredit() {
                for (let i = 0; i < this.splits[this.softcreditSplitIndex].softCredits.length; i++) {
                    if (this.softCredit.soft_credit_id === this.splits[this.softcreditSplitIndex].softCredits[i].soft_credit_id) {
                        if (this.splits[this.softcreditSplitIndex].softCredits[i].isTemp) {
                            this.splits[this.softcreditSplitIndex].softCredits.splice(i, 1);
                        } else {
                            this.splits[this.softcreditSplitIndex].softCredits[i].remove = true;
                        }
                        $('#soft-credit-modal').modal('hide');
                        break;
                    }
                }
            },
            editSoftCredit(id, index) {
                this.softcreditSplitIndex = index;
                
                for (let i = 0; i < this.splits[this.softcreditSplitIndex].softCredits.length; i++) {
                    if (id === this.splits[this.softcreditSplitIndex].softCredits[i].soft_credit_id) {
                        this.softCredit.contact_id = this.splits[this.softcreditSplitIndex].softCredits[i].contact_id;
                        this.softCredit.contact_name = this.splits[this.softcreditSplitIndex].softCredits[i].contact_name;
                        this.softCredit.amount = parseFloat(this.splits[this.softcreditSplitIndex].softCredits[i].amount);
                        this.softCredit.soft_credit_id = this.splits[this.softcreditSplitIndex].softCredits[i].soft_credit_id;
                        this.contactSoftCredit = {
                            id: this.splits[this.softcreditSplitIndex].softCredits[i].contact_id,
                            label: this.splits[this.softcreditSplitIndex].softCredits[i].contact_name
                        }
                        $('[name="soft_credit_amount"]').val(this.softCredit.amount);
                        this.softCreditMode = 'edit';
                        this.openSoftCreditModal(index);
                        break;
                    }
                }
            },
            updateSoftCredit() {
                for (let i = 0; i < this.splits[this.softcreditSplitIndex].softCredits.length; i++) {
                    if (this.softCredit.soft_credit_id === this.splits[this.softcreditSplitIndex].softCredits[i].soft_credit_id) {
                        this.splits[this.softcreditSplitIndex].softCredits[i].contact_id = this.softCredit.contact_id;
                        this.splits[this.softcreditSplitIndex].softCredits[i].contact_name = this.softCredit.contact_name;
                        this.splits[this.softcreditSplitIndex].softCredits[i].amount = this.softCredit.amount;
                        this.splits[this.softcreditSplitIndex].softCredits[i].update = true;
                        break;
                    }
                }
                
                $('#soft-credit-modal').modal('hide');
            },
            addAttachment(event) {
                let file = event.target.files[0];
                let formData = new FormData();           
                formData.append('file', file);
                formData.append('folder', 'transaction_attachmemnts');
                
                this.post({
                    url: '/crm/documents/store',
                    data: formData
                }).then(result => {
                    if (result.response === undefined) {
                        if (result.data.success) {
                            if (!this.transaction.attachments) {
                                this.transaction.attachments = [];
                            }

                            this.transaction.attachments.push({
                                name: file.name,
                                size: file.size,
                                attachment_id: result.data.id,
                                isTemp: true,
                                remove: false
                            });
                        } else {
                            Swal.fire('An unexpected error occurred', 'Please try again later or contact support', 'error');
                        }
                    } else {
                        Swal.fire(result.response.data.file[0], '', 'error');
                    }
                    
                    event.target.value = null;
                })
            },
            removeAttachment(id) {
                for (let i = 0; i < this.transaction.attachments.length; i++) {
                    if (id === this.transaction.attachments[i].attachment_id) {
                        this.transaction.attachments[i].remove = true;
                        break;
                    }
                }
            },
            bytesToSize(size) {
                return bytesToSize(size);
            },
            truncateString(str, num) {
                return truncateString(str, num);
            },
            purposeChange(value, split) {
                this.transaction.purpose_id = value.id
                split.purpose_id = value.id
                split.chart.id = value.id
                split.chart.name = value.name
            },
            campaignChange(value, split) {
                this.transaction.campaign_id = value.id
                split.campaign_id = value.id
                split.campaign.id = value.id
                split.campaign.name = value.name
        
                if(this.transaction.campaign_id == 1){
                    split.disable_purposes = false
                }
                else{
                    this.get({
                        url: '/crm/ajax/campaigns/chart',
                        data: {
                            campaign_id: this.transaction.campaign_id
                        }
                    }).then(result => {
                        this.transaction.purpose_id = result.data.id
                        this.chart = {'id': result.data.id, 'name': result.data.name}
                        split.purpose_id = result.data.id
                        split.chart = {'id': result.data.id, 'name': result.data.name}
                        split.disable_purposes = true
                    })
                }
            },
            findCampaignName() {
                var campaign = this.campaigns.find(c => c.id == this.transaction.campaign_id)
                return campaign ? campaign.name : ''
            },
            findPurposeName() {
                var purpose = null;
        
                for (var i=0; i<this.purposes.length; i++) {
                    for (var j=0; j<this.purposes[i].children.length; j++) {
                        if (this.purposes[i].children[j].id == this.transaction.purpose_id) {
                            var purpose = this.purposes[i].children[j];
                            break;
                        }
                    }
                };
        
                return purpose ? purpose.name : ''
            },
            updateDuplicateTransactionsList() {
                let transactionEl = ''
                this.duplicateTransactionsList.forEach(transaction => {
                    transactionEl += `<li>${transaction.item.label}</li>`
                })
                $('#listOfDuplicateTransactions').html(transactionEl);
            },
            addNewSplit() {
                this.splits.push({
                    temp_id: 'temp_' + this.splits.length,
                    amount: null,
                    campaign: {
                        id: 1,
                        name: null
                    },
                    chart: {
                        id: null,
                        name: null
                    },
                    tags: [],
                    tax_deductible: 1,
                    campaign_id: 1,
                    purpose_id: null,
                    disable_purposes: false,
                    softCredits: []
                });
            },
            deleteSplit(split) {
                console.log(this.splits)
                console.log(split)
                
                for (var i=0; i<this.splits.length; u++) {
                    
                }
            },
            selectTransctionToDuplicate(transaction) {
                console.log('selectTransctionToDuplicate');
                console.log(transaction);
            },
            showDiplicateTransaction() {
                $('#search-duplicate-transaction').toggle();
            }
        }
    }
</script>

<style scoped media="screen">
option[disabled] {
    font-style: italic;
    color: grey;
}
label {
    font-weight: bold;
}
.suggested_contact_li:hover{
  cursor: pointer;
  color: #20a8d8;
}
</style>
