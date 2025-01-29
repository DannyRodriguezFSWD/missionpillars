<template>
    <div class="crm-billing-software-upgrade">
        <div class="card">
            <div class="card-body" v-if="crmfeature != '' && crmmodule != null">
                <p>{{ crmfeature }} is part of the {{ crmmodule }} Module.</p>
                <p>For access to that feature and others, please enable {{ crmmodule }} module below.</p>
            </div>
            <div class="card-body">
                <div id="alert_unpaid_invoices" class="alert-warning" v-if="amount_unpaid > 0">
                    <h2>Unpaid Invoice</h2>
                    You have unpaid <a class="alert-link" :href="invoice_link">invoice balance</a> of ${{ amount_unpaid.toFixed(2) }}. 
                    <hr>
                    To activate new or reactivate previously subscribed modules, please click <b>Enable Module</b>.
                </div>
                
                <h4>Current plan details</h4>
                <table class="table installed-modules">
                    <tr v-for="(m, index) in crmmodules.modules.installed" :key="index">
                        <template v-if="m.name != 'Basic ChMS'">
                            <td>{{ m.name }}</td>
                            <td v-if="m.pivot.is_trial == 0 || has_payment_option">{{ billingStarted(m) ? 'Next bill on' : 'Billing starts on '}} {{ nextModuleBillingDate(m) }}</td>
                            <td v-else>
                                <h5>
                                    <span class="badge badge-warning">
                                        Trial will end on {{ nextModuleBillingDate(m) }}
                                    </span>
                                </h5>
                            </td>
                            <td class="text-right">
                                <button class="btn btn-link" v-on:click="seeModuleDetails(m, 'disable')">See Module Details</button>
                                <button class="btn btn-primary" v-if="m.pivot.is_trial == 1 && !has_payment_option" v-on:click="enableOrDisableModule(m, true)">
                                    <i class="fa fa-angle-double-up" aria-hidden="true"></i>
                                    Enable Module
                                </button>
                                <button class="btn btn-danger" v-if="m.pivot.cancel == 0 && (m.pivot.is_trial == 0 || has_payment_option)" v-on:click="enableOrDisableModule(m, false)">
                                    <i class="fa fa-angle-double-down" aria-hidden="true"></i>
                                    Disable Module
                                </button>
                                <button disabled class="btn btn-danger" v-if="m.pivot.cancel == 1" v-on:click="enableOrDisableModule(m, false)">
                                    <i class="fa fa-angle-double-down" aria-hidden="true"></i>
                                    Processing cancelation request
                                </button>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="crmmodules.modules.installed.length <= 1">
                        <td>
                            <div class="alert alert-danger mb-0">You must enable one of the modules below</div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-body" v-if="crmmodules.modules.available.length > 0">
                <h4>Available Modules</h4>
            
                <p>&nbsp;</p>
                <table class="table">
                    <tr v-for="(m, index) in crmmodules.modules.available" :key="index">
                        <td>{{ m.name }}</td>
                        <td>
                            <h5 v-if="m.id == 2 && crmmodules.plan.chms.free || m.id == 3 && crmmodules.plan.accounting.free">
                                <span class="badge badge-success">
                                    14 day free trial!
                                </span>
                            </h5>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-link" v-on:click="seeModuleDetails(m, 'enable')">See Module Details</button>
                           
                            <button class="btn btn-primary" v-on:click="enableOrDisableModule(m, true)">
                                <i class="fa fa-angle-double-up" aria-hidden="true"></i>
                                Enable Module
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <CRMModal v-if="crmmodal.modal.show">
            <h3 slot="header">{{ crmmodal.modal.header }}</h3>
            <div slot="body">{{ crmmodal.modal.body }}</div>
            <div slot="footer">
                <button class="modal-default-button btn btn-secondary" v-on:click="showModal(false)">
                Close
                </button>
            </div>
        </CRMModal>

        <div class="row" id="available-modules">
            <div class="col-md-6">
                <CHMSPlan
                :app_fee="chms_fee" :contact_fee="contact_fee"
                v-on:enable-disable-module="enableOrDisableModule(crmmodules.modules.all[1], $event)"
                :discount="discounts['CRM']"
                :promocode="discounts['withpromocode']['CRM'] ? promocodes['CRM'] :''"
                :current_app_fee="discounts['currentmodulefee']['CRM']"
                :current_contact_fee="discounts['currentcrmcontactfee']">
                </CHMSPlan>
            </div>
            
            <div class="col-md-6">
                <AccountingPlan :url="accounting"
                :app_fee="acct_fee" 
                v-on:enable-disable-module="enableOrDisableModule(crmmodules.modules.all[2], $event)"
                :discount="discounts['Accounting']"
                :promocode="discounts['withpromocode']['Accounting'] ? promocodes['Accounting'] :''"
                :current_app_fee="discounts['currentmodulefee']['Accounting']">
                </AccountingPlan>
            </div>
            
        </div>

        <CRMModal v-if="confirm.show">
            <h3 slot="header">{{ confirm.header }}</h3>
            <div slot="body">
                <div v-if="action === 'enable'">{{ confirm.body }}</div>
                <div v-if="action === 'enable'">
                    <br>
                    <div class="row" v-if="amount_unpaid == 0">
                        <div class="col-md-4" :class="{'has-success': isSuccess, 'has-danger': isError}">
                            <input type="text" id="promo-code" class="form-control" placeholder="Promotion code" v-model="promoCode" @input="clearPromoValidation">
                            <div v-if="isSuccess" class="text-success">Promotion code is valid!</div>
                            <div v-if="isError" class="text-danger">Promotion code is not valid!</div>
                        </div>
                        <div class="col-md-2 float-right">
                            <button id="validate-promo-code" class="btn btn-primary" v-on:click="checkPromotionCode()">Validate</button>
                        </div>
                    </div>
                    <div class="row" v-if="amount_unpaid > 0">
                        <div id="alert_payment_required" class="col alert-info">
                            <h4>Payment Required to Enable</h4>
                            Your unpaid balance is ${{ amount_unpaid.toFixed(2) }}
                            <hr>
                            By enabling this module, this balance will be paid using the provided payment method.                            
                        </div>
                    </div>
                </div>
                <div v-if="require_feedback && action == 'disable'">
                    <p>Please let us know why you would like to cancel your subscription and after review an admin will cancel your subscription.</p>
                    <p>Your information is very valuable in helping us make sure we better our software for other uses.  Also let us know if there is anything we can do to keep you on as a valued client.</p>
                    <textarea v-model="message" name="feedback" id="feedback" class="form-control"></textarea>
                </div>

                <div v-if="crmmodules.plan.chms.free == true && crmmodules.selected.id == 2">
                    <!-- {{ crmmodules.plan.chms.free }} -->
                    <p class="mt-4">
                        <i class="fa fa-check text-success"></i>
                        First 14 days are free
                    </p>
                    <p>
                        <i class="fa fa-check text-success"></i>
                        Billing will start at {{ crmmodules.next_billing_date }}
                    </p>
                </div>

                <div v-if="crmmodules.plan.accounting.free == true && crmmodules.selected.id == 3">
                    <!-- {{ crmmodules.plan.accounting.free }} -->
                    <p class="mt-4">
                        <i class="fa fa-check text-success"></i>
                        First 14 days are free
                    </p>
                    <p>
                        <i class="fa fa-check text-success"></i>
                        Billing will start at {{ crmmodules.next_billing_date }}
                    </p>
                </div>
            </div>
            <div slot="footer">
                <button class="btn btn-secondary mr-4" v-on:click="confirm.show = false">
                No
                </button>

                <button class="modal-default-button btn btn-primary" v-on:click="enableOrDisableModuleInDb()">
                Yes, I wish to {{ action }} module {{ crmmodules.selected.name }}
                </button>
            </div>
        </CRMModal>

        <CRMCreditCardForm :stripe-api-key="stripeApiKey" :url="url" v-on:credit-card-added="upgradePlan($event)"></CRMCreditCardForm>

        <div id="overlay" v-if="getIsLoadingState || planIsLoading">
            <div class="spinner">
                <div class="rect1"></div>
                <div class="rect2"></div>
                <div class="rect3"></div>
                <div class="rect4"></div>
                <div class="rect5"></div>
            </div>
        </div>
    </div>
</template>
<style scoped>
    #overlay{ display: block; }
    .was-validated .form-control:invalid, .form-control.is-invalid, .was-validated .custom-select:invalid, .custom-select.is-invalid{
        border-color: #f86c6b;
    }
    
    #alert_unpaid_invoices,
    #alert_payment_required {
        border: 1px solid lightgrey;
        padding: 10px;
        margin-bottom: 20px;
    }
    
    .installed-modules td {
        vertical-align: middle;
    }
</style>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    import CRMModal from '../../../crm-modal.vue'
    import CHMSPlan from './plan/chms.vue'
    import AccountingPlan from './plan/accounting.vue'
    import CRMCreditCardForm from './cc_form'

    export default {
        props: {
            url: String, crmmodule: String, crmfeature: String, stripeApiKey: String, accounting: String,
            chms_fee: {
                default: 40,
            },
            acct_fee: {
                default: 29,
            },
            contact_fee: {
                default: 0.03,
            },
            invoice_link: {},
            amount_unpaid: {},
            promocodes: {},
            discounts: {},
            has_payment_option: {}
        },
        name: 'CRMSettingsBillingSoftwareUpgrade',
        data: function (){
            return {
                planIsLoading: false,
                plan: {
                    free: {
                        show: false
                    },
                    chms: {
                        show: false,
                        action: 'enable'
                    },
                    accounting: {
                        show: false,
                        action: 'enable'
                    }
                },
                confirm: {
                    header: 'Confirm',
                    body: '',
                    show: false
                },
                message: '',
                require_feedback: true,
                promoCode: '',
                isError: false,
                isSuccess: false,
            }
        },
        components: {
            CRMModal,
            CHMSPlan,
            AccountingPlan,
            CRMCreditCardForm
        },
        mounted: function () {
            this.$store.dispatch('crmmodules/getCurrentModulesAction', this.url+'/get/modules')
        },
        computed: {
            ...mapState([
                'crmmodal',
                'crmmodules',
                'action'
            ]),
            ...mapGetters([
                'getIsLoadingState',
                'getActionState'
            ]),
        },
        methods: {
            ...mapMutations([
                'SET_ACTION_STATE',
                'SET_CREDIT_CARD_REQUIRED_STATE',
                'SET_PROMOTION_CODE_STATE'
            ]),
            ...mapActions([
                
            ]),
            showModal: function(value){
                this.$store.dispatch('crmmodal/showModalAction', value)
            },
            seeModuleDetails: function(module, action){
                this.SET_ACTION_STATE(action)
                this.$store.dispatch('crmmodules/setSelectedModule', module)
                var container = this.$el.querySelector("#available-modules")

                this.$store.commit('crmmodules/SET_STATE_FREE_ACTIVE', false)
                this.$store.commit('crmmodules/SET_STATE_CHMS_ACTIVE', false)
                this.$store.commit('crmmodules/SET_STATE_ACCOUNTING_ACTIVE', false)
                
                $([document.documentElement, document.body]).animate({
                    scrollTop: container.offsetHeight
                }, 1000);

                switch (module.id) {
                    case 1:
                        this.$store.commit('crmmodules/SET_STATE_FREE_ACTIVE', true)
                        break;
                    case 2:
                        this.$store.commit('crmmodules/SET_STATE_CHMS_ACTIVE', true)
                        break;
                    case 3:
                        this.$store.commit('crmmodules/SET_STATE_ACCOUNTING_ACTIVE', true)
                        break;
                    default:
                        break;
                }
                
            },
            closePlanDetails: function(event){
                switch (event) {
                    case 1:
                        this.plan.free.show = false
                        break;
                    case 2:
                        this.plan.chms.show = false
                        break;
                    case 3:
                        this.plan.accounting.show = false
                        break;
                    default:
                        break;
                }
            },
            enableOrDisableModule: function(module, enable){
                if(enable){//enable module
                    this.clearPromoValidation()
                    
                    if (this.amount_unpaid == 0) {
                        this.confirm.body = 'If you have promotion code, please enter it here.'
                        
                        // Automatic promocodes
                        // console.log([0,0,'CRM','Accounting'][module.id],module)
                        var autopromocode = this.promocodes[[0,0,'CRM','Accounting'][module.id]];
                        if (autopromocode) {
                            this.confirm.body = 'We have automatically applied the promotion code below. Enjoy!';
                            this.promoCode = autopromocode;
                            this.checkPromotionCode();
                        }
                    }
                    this.SET_ACTION_STATE('enable')
                }
                else{//disable module
                    this.confirm.body = '';
                    switch (module.id) {
                        case 2:
                            this.confirm.body += 'If you disable '+module.name+', you will not be able to use all the CRM features and the purchased phone numbers will be available for other companies to purchase'
                            break;
                        case 3:
                            this.confirm.body += 'If you disable '+module.name+', you will not be able to use all the accounting features'
                            break;
                        default:
                            break;
                    }
                    
                    this.confirm.body += 'Are you sure you want to disable '+module.name+' module?'
                    this.SET_ACTION_STATE('disable')
                }

                this.$store.dispatch('crmmodules/setSelectedModule', module)
                this.confirm.show = true;
            },
            clearPromoValidation() {
                this.isSuccess = false;
                this.isError = false;
            },
            checkPromotionCode: function() {
                if (this.promoCode !== '') {
                    axios.get("/promo-codes/check", {
                        params: {
                            'promoCode': this.promoCode
                        }
                    }).then( response => {
                        if (response.data === 'false') {
                            this.isError = true;
                            this.isSuccess = false;
                        } else {
                            this.isError = false;
                            this.isSuccess = true;
                        }
                    })
                }
            },
            enableOrDisableModuleInDb: function(){
                if(this.message.trim() == '' && this.require_feedback == true && this.action == 'disable'){
                    Swal.fire('Please tell us why you are canceling this plan before proceed','','info')
                    return false
                }
                if(this.getActionState == 'enable'){
                    var url = this.url+'/check/info'
                    this.planIsLoading = true
                    axios.get(url).then( response => {
                        if(response.data.length <= 0){//require cc
                            this.planIsLoading = false
                            this.confirm.show = false
                            this.SET_CREDIT_CARD_REQUIRED_STATE(true)
                            this.SET_PROMOTION_CODE_STATE(this.promoCode)
                            $('#add-stripe-card').modal('show')
                        }
                        else{
                            this.upgradePlan(true)
                        }
                    });
                }
                else{
                    this.upgradePlan(true)
                }
            },
            upgradePlan: function(upgrade){
                if(!upgrade){
                    Swal.fire("No upgrade",'','info')
                    return false
                }

                var url = this.url+'/'+this.crmmodules.selected.id

                var params = {
                    id: this.crmmodules.selected.id,
                    action: this.action,
                    message: this.message,
                    promo_code: this.promoCode
                };
                this.planIsLoading = true
                axios.put(url, params).then( response => {
                    if (this.amount_unpaid && response.data == true) window.location.reload()
                    this.planIsLoading = false
                    this.$store.dispatch('crmmodules/getCurrentModulesAction', this.url+'/get/modules').then(response => {
                        this.confirm.show = false
                        this.closePlanDetails(this.crmmodules.selected.id)
                    })
                    this.message = ''
                    this.promoCode = ''
                    if (this.action == 'disable') {
                        Swal.fire('Thank you for your input.','Our team will contact you shortly','success')
                    }
                    switch (this.crmmodules.selected.id) {
                        case 2:
                            this.$store.commit('crmmodules/SET_STATE_CHMS_ACTION', this.action == 'disable' ? 'enable':'disable')
                            break;
                        case 3:
                            this.$store.commit('crmmodules/SET_STATE_ACCOUNTING_ACTION', this.action == 'disable' ? 'enable':'disable')
                            break;
                        default:
                            break;
                    }
                } );
            },
            billingStarted(module) {
                return new Date(module.pivot.start_billing_at) < new Date()
            },
            nextModuleBillingDate(module) {
                var nextbilldate = module.pivot.next_billing_at;
                var startbilldate = module.pivot.start_billing_at;
                if (!nextbilldate || !this.billingStarted(module)) nextbilldate = startbilldate
                // stil nothing, default to end of month
                var now = new Date()
                // console.log (new Date(nextbilldate) < now, new Date(nextbilldate) , now) 
                if (new Date(nextbilldate) < now) {
                    nextbilldate = now
                    nextbilldate.setMonth(nextbilldate.getMonth()+1)
                    nextbilldate.setDate(1)
                    nextbilldate.setDate(nextbilldate.getDate()-1)
                }
                
                return new Intl.DateTimeFormat('en-US').format(Date.parse(nextbilldate))
            },
        }
    }
</script>
