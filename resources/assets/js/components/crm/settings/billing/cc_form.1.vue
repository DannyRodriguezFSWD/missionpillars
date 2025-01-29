<template>
    <div class="crm-cc-form">
        <CRMModal v-if="creditCardRequired">
            <h3 slot="header">{{ cc.header }}</h3>
            <div slot="body">
                <form :action="url" method="POST" id="payment-form">
                    <p>In order to upgrade your plan we need you to add your credit card info</p>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group" :class="validation.hasError('cc.number') ? 'was-validated' : ''">
                                <label for="credit_card_number">Credit Card Number</label>
                                <input data-stripe="number" v-model="cc.number" autofocus name="credit_card_number" type="text" class="form-control" :class="validation.hasError('cc.number') ? 'is-invalid' : ''" placeholder="Credit card number" autocomplete="off" maxlength="16">
                                <small class="text-danger">{{ validation.firstError('cc.number') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="expiration_date_month">Expiration Date</label>
                            <select data-stripe="exp-month" v-model="cc.month" name="expiration_date_month" class="form-control">
                                <option v-for="(month, index) in months.available" :value="month.value">{{ month.display }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="expiration_date_year">&nbsp;</label>
                            <select data-stripe="exp-year" v-model="cc.year" name="expiration_date_year" class="form-control">
                                <option v-for="(year, index) in years.available" :value="year">{{ year }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" :class="validation.hasError('cc.cvc') ? 'was-validated' : ''">
                                <label for="credit_card_cvc">CVC</label>
                                <input data-stripe="cvc" v-model="cc.cvc" name="credit_card_cvc" type="password" class="form-control" :class="validation.hasError('cc.cvc') ? 'is-invalid' : ''" placeholder="***" maxlength="4">
                                <small class="text-danger">{{ validation.firstError('cc.cvc') }}</small>
                            </div>
                        </div>
                    </div>
                    <!--
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input name="first_name" type="text" class="form-control" placeholder="First Name" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input name="last_name" type="text" class="form-control" placeholder="Last Name" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group" :class="validation.hasError('contact.address.address') ? 'was-validated' : ''">
                                <label for="address">Address</label>
                                <input data-stripe="address_line1" v-model="contact.address.address" name="address" type="text" class="form-control" placeholder="Address" :class="validation.hasError('contact.address.address') ? 'is-invalid' : ''" autocomplete="off">
                                <small class="text-danger">{{ validation.firstError('contact.address.address') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group" :class="validation.hasError('contact.address.city') ? 'was-validated' : ''">
                                <label for="city">City</label>
                                <input data-stripe="address_city" v-model="contact.address.city" name="city" type="text" class="form-control" placeholder="City" :class="validation.hasError('contact.address.city') ? 'is-invalid' : ''" autocomplete="off">
                                <small class="text-danger">{{ validation.firstError('contact.address.city') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" :class="validation.hasError('contact.address.state') ? 'was-validated' : ''">
                                <label for="state">State</label>
                                <input data-stripe="address_state" v-model="contact.address.state" name="state" type="text" class="form-control" placeholder="State" :class="validation.hasError('contact.address.state') ? 'is-invalid' : ''" autocomplete="off">
                                <small class="text-danger">{{ validation.firstError('contact.address.state') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" :class="validation.hasError('contact.address.zip') ? 'was-validated' : ''">
                                <label for="zip">Postal Code</label>
                                <input data-stripe="address_state" v-model="contact.address.zip" name="zip" type="text" class="form-control" placeholder="Postal Code" :class="validation.hasError('contact.address.zip') ? 'is-invalid' : ''" autocomplete="off" maxlength="5">
                                <small class="text-danger">{{ validation.firstError('contact.address.zip') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="country">Country</label>
                            <select data-stripe="address_country" v-model="contact.address.country" name="country" class="form-control">
                                <option v-for="(country, index) in countries" :value="country.value">{{ country.label }}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div slot="footer">
                <button class="btn btn-secondary mr-4" v-on:click="SET_CREDIT_CARD_REQUIRED_STATE(false)">
                Close
                </button>

                <button class="modal-default-button btn btn-primary" v-on:click="validateCreditCard()">
                    Save Credit Card
                </button>
            </div>
            
        </CRMModal>

        <CRMModal v-if="crmmodal.modal.show">
            <h3 slot="header">{{ crmmodal.modal.header }}</h3>
            <div slot="body">{{ crmmodal.modal.body }}</div>
            <div slot="footer">
                <button class="modal-default-button btn btn-secondary" v-on:click="$store.commit('crmmodal/SHOW_MODAL', false)">
                Close
                </button>
            </div>
        </CRMModal>

        <div id="overlay" v-if="planIsLoading">
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

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    import CRMModal from '../../../crm-modal.vue'
    import SimpleVueValidation from 'simple-vue-validator';
    const Validator = SimpleVueValidation.Validator;

    export default {
        props: ['url', 'stripeApiKey'],
        name: 'CRMCreditCardForm',
        validators: {
            'cc.number': function (value) {
                return Validator.value(value).required().length(16);
            },
            'cc.cvc': function (value) {
                return Validator.value(value).required().minLength(3).integer();
            },
            'contact.address.address': function (value) {
                return Validator.value(value).required();
            },
            'contact.address.city': function (value) {
                return Validator.value(value).required();
            },
            'contact.address.state': function (value) {
                return Validator.value(value).required();
            },
            'contact.address.zip': function (value) {
                return Validator.value(value).required().minLength(5).integer();
            }
        },
        components: {
            CRMModal
        },
        data: function(){
            return {
                stripe: null,
                planIsLoading: false,
                countries: [],
                cc: {
                    header: 'Credit Card Info',
                    show: false,
                    number: '',
                    year: new Date().getFullYear(),
                    month: new Date().getMonth()+1,
                    cvc: ''
                },
                contact: {
                    firstname: '',
                    lastname: '',
                    address: {
                        address: '',
                        city: '',
                        state: '',
                        country: 840,
                        zip: ''
                    }
                },
                years: {
                    available: [],
                    selected: 0
                },
                months: {
                    available: [
                        {value: 1, display: '01 - January'},
                        {value: 2, display: '02 - February'},
                        {value: 3, display: '03 - March'},
                        {value: 4, display: '04 - April'},
                        {value: 5, display: '05 - May'},
                        {value: 6, display: '06 - June'},
                        {value: 7, display: '07 - July'},
                        {value: 8, display: '08 - August'},
                        {value: 9, display: '09 - September'},
                        {value: 10, display: '10 - October'},
                        {value: 11, display: '11 - November'},
                        {value: 12, display: '12 - December'}
                    ],
                    selected: 1
                }
            }
        },
        mounted() {
            this.getCountries()
            var current_year = new Date().getFullYear()
            this.years.available.push(current_year)
            this.years.selected = current_year
            for(var i=1; i <= 10; i++){//10 years
                current_year++
                this.years.available.push(current_year)
            }
            
        },
        computed: {
            ...mapState([
                'crmmodal',
                'crmmodules',
                'creditCardRequired'
            ]),
        },
        methods: {
            ...mapMutations([
                'SET_CREDIT_CARD_REQUIRED_STATE'
            ]),
            validateCreditCard: function(){
                this.$validate()
                .then(success => {
                    if (success) {
                        this.saveCreditCard()
                    }
                });
            },
            saveCreditCard: function(){
                var $form = $('#payment-form')
                this.planIsLoading = true
                Stripe.setPublishableKey(this.stripeApiKey)
                Stripe.card.createToken($form, (status, response) => {
                    this.planIsLoading = false
                    if (response.error) {
                        
                        this.$store.commit('crmmodal/SET_MODAL_BODY', response.error.message)
                        this.$store.commit('crmmodal/SHOW_MODAL', true)
                    } else {
                        var params = {
                            cc: this.cc,
                            contact: this.contact,
                            stripe: response
                        };
                        var url = this.url+'/save/credit/card'
                        this.planIsLoading = true
                        axios.post(url, params).then( response => {
                            this.planIsLoading = false
                            this.SET_CREDIT_CARD_REQUIRED_STATE(false)
                            this.$emit('credit-card-added', true)
                        });
                    }
                })
                
            },
            getCountries: function(){
                axios.get(this.url).then(response => {
                    this.countries = response.data.countries
                    if(this.countries.length > 0){
                        this.contact.address.country = 'US'
                    }
                })
            }
        }
    }
</script>

<style scoped>
    #overlay{ display: block; z-index: 99999; }
    .was-validated .form-control:invalid, .form-control.is-invalid, .was-validated .custom-select:invalid, .custom-select.is-invalid{
        border-color: #f86c6b;
    }
</style>