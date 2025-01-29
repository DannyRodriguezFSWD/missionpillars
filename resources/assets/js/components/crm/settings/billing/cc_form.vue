<template>
    <div class="crm-cc-form">
        <div id="add-stripe-card" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="post" id="payment-form">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Credit or debit card</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert-warning" role="alert" v-if='saveCardError'>
                                {{ saveCardError }}
                            </div>
                            <div class="form-group">
                                <label for="card-element"></label>
                                <div id="card-element">
                                <!-- A Stripe Element will be inserted here. -->
                                </div>

                                <!-- Used to display Element errors. -->
                                <div id="card-errors" role="alert"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary">Save card</button>
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
                saveCardError: null,
                stripe: null,
                elements: null,
                card: null,
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
            this.stripe = Stripe(this.stripeApiKey);
            this.elements = this.stripe.elements();
            var style = {
                base: {
                    color: '#32325d',
                    lineHeight: '18px',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };

            // Create an instance of the card Element.
            this.card = this.elements.create('card', {style: style});

            // Add an instance of the card Element into the `card-element` <div>.
            this.card.mount('#card-element');
            // Handle real-time validation errors from the card Element.
            this.card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
            // Handle form submission.
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', event => {
                event.preventDefault();

                this.stripe.createToken(this.card).then(result => {
                    if (result.error) {
                        // Inform the user if there was an error.
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                    } else {
                        // Send the token to your server.
                        this.saveCreditCard(result.token)
                    }
                });
            });
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
            saveCreditCard: function(result){
                this.saveCardError = null;
                var params = {
                    cc: this.cc,
                    contact: this.contact,
                    stripe: result
                };
                var url = this.url+'/save/credit/card'
                this.planIsLoading = true
                axios.post(url, params)
                .then( response => {
                    console.log(response)
                    this.planIsLoading = false
                    this.SET_CREDIT_CARD_REQUIRED_STATE(false)
                    this.$emit('credit-card-added', true)
                    $('#add-stripe-card').modal('hide')
                    $('#activate-subscription-alert').remove()
                    Swal.fire('Your account has been activated successfully', '', 'success');
                })
                .catch( error => {
                    console.log(error.response)
                    if (error.response.status == 400) {
                        this.saveCardError = error.response.data
                    }
                    else {
                        this.saveCardError = "Please try again"
                    }
                    this.planIsLoading = false
                })
            },
            getCountries: function(){
                axios.get(this.url+'/get/modules').then(response => {
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

    /**
    * The CSS shown here will not be introduced in the Quickstart guide, but shows
    * how you can use CSS to style your Element's container.
    */
    .StripeElement {
    background-color: white;
    height: 40px;
    padding: 10px 12px;
    border-radius: 4px;
    border: 1px solid transparent;
    box-shadow: 0 1px 3px 0 #e6ebf1;
    -webkit-transition: box-shadow 150ms ease;
    transition: box-shadow 150ms ease;
    }

    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }

    .StripeElement--invalid {
        border-color: #fa755a;
    }

    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
</style>
