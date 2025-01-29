<template>
    <div class="crm-billing-payment-options">
        <div class="card">
            <div class="card-body">
                <div class="btn-group btn-group" role="group" aria-label="...">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#add-stripe-card">
                        <i class="icon icon-plus"></i>
                        Add new payment option
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p>Select your preferred payment option</p>
                <ul class="list-group">
                    <li v-for="(option, index) in options" class="list-group-item">
                        <i v-if="option.selected" class="fa fa-check"></i>
                        <i v-else class="fa fa-check text-white"></i>

                        {{ option.card_type }}
                        **** {{ option.last_four }}

                        <button v-if="!option.selected" v-on:click="confirmDelete(index)" class="btn btn-danger pull-right">
                            <i class="fa fa-trash"></i> Delete
                        </button>

                        <button v-on:click="showEditCard(index)" class="btn btn-link pull-right" :class="{'mr-4': !option.selected}">
                            <i class="fa fa-pencil"></i> Edit
                        </button>

                        <button v-if="!option.selected" v-on:click="selectCard(index)" class="btn btn-link pull-right">
                            <i class="fa fa-check"></i> Set as default
                        </button>

                        <div v-if="option.selected" class="btn pull-right">(Default)</div>
                    </li>
                </ul>
            </div>

            <CRMModal v-if="modal.show">
                <h3 slot="header">{{ modal.header }}</h3>
                <div slot="body">{{ modal.body }}</div>
                <div slot="footer">
                    <button class="btn btn-secondary mr-4" v-on:click="showModal(false)">
                    No
                    </button>

                    <button class="modal-default-button btn btn-danger" v-on:click="deleteCard(toDelete)">
                    Yes
                    </button>
                </div>
            </CRMModal>


            <CRMCreditCardForm :stripe-api-key="stripeApiKey" :url="url" v-on:credit-card-added="reloadCreditCardOptions($event)"></CRMCreditCardForm>

            <div id="edit-stripe-card" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Credit or debit card</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 text-center">
                                        **** {{ toEdit != null ? toEdit.last_four : '' }}
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input v-model="card.month" type="text" class="form-control" placeholder="MM" maxlength="2" v-on:keypress="isNumber($event)">
                                            <p v-if="card.showMonthError" class="text-danger">Invalid month</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input v-model="card.year" type="text" class="form-control" placeholder="YYYY" maxlength="4" v-on:keypress="isNumber($event)">
                                            <p v-if="card.showYearError" class="text-danger">Invalid year</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-primary" v-on:click="updateCreditCard">Update card</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>

                    </div>
                </div>
            </div>

            <div id="overlay" v-if="getIsLoadingState || isLoading">
                <div class="spinner">
                    <div class="rect1"></div>
                    <div class="rect2"></div>
                    <div class="rect3"></div>
                    <div class="rect4"></div>
                    <div class="rect5"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
    #overlay{ display: block; }
    .was-validated .form-control:invalid, .form-control.is-invalid, .was-validated .custom-select:invalid, .custom-select.is-invalid{
        border-color: #f86c6b;
    }
</style>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    import CRMModal from '../../../crm-modal.vue'
    import CRMCreditCardForm from './cc_form'

    export default {
        props: ['url', 'stripeApiKey'],
        name: 'CRMSettingsBillingPaymentOptions',
        data: function (){
            return {
                isLoading: true,
                confirm: {
                    header: 'Confirm',
                    body: '',
                    show: false
                },
                options: [],
                toDelete: null,
                toEdit: null,
                modal: {
                    header: 'Confirm',
                    body: '',
                    show: false
                },
                card: {
                    month: '',
                    year: '',
                    showMonthError: false,
                    showYearError: false
                }
            }
        },
        components: {
            CRMModal,
            CRMCreditCardForm
        },
        mounted: function () {
            this.loadCreditCardoptions()
        },
        computed: {
            ...mapState([
                'crmmodal',
                'crmmodules',
                'action'
            ]),
            ...mapGetters([
                'getIsLoadingState'
            ]),
        },
        methods: {
            isNumber: function(evt) {
                evt = (evt) ? evt : window.event;
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                if ((charCode > 31 && (charCode < 48 || charCode > 57))) {
                    evt.preventDefault();;
                } else {
                    return true;
                }
            },
            ...mapMutations([
                'SET_ACTION_STATE',
                'SET_CREDIT_CARD_REQUIRED_STATE'
            ]),
            ...mapActions([

            ]),
            loadCreditCardoptions: function(){
                this.isLoading = true
                axios.get(this.url+'/payment/options').then(response => {
                    this.options = response.data
                    this.isLoading = false
                });
            },
            showModal: function(value){
                this.modal.show = false
            },
            confirmDelete: function(index){
                this.toDelete = index
                this.modal.body = 'Are you sure you want to delete this credit card'
                this.modal.show = true
            },
            deleteCard: function(index){
                this.modal.show = false
                var url = this.url + '/delete/payment/option'
                this.isLoading = true
                axios.put(url, this.options[index]).then(request => {
                    this.isLoading = false
                    this.loadCreditCardoptions()
                })
            },
            selectCard: function(index){
                var url = this.url + '/update/payment/option'
                this.isLoading = true
                axios.put(url, this.options[index]).then(request => {
                    this.isLoading = false
                    this.loadCreditCardoptions()
                })
            },
            reloadCreditCardOptions: function(reload){
                if(reload){
                    this.loadCreditCardoptions()
                }
            },
            showEditCard: function(index){
                this.toEdit = this.options[index]
                console.log(this.toEdit)
                $('#edit-stripe-card').modal('show')
            },
            updateCreditCard: function(){
                this.card.showMonthError = this.card.month.length < 2
                this.card.showYearError = this.card.year.length < 4

                if(this.card.showMonthError || this.card.showYearError){
                    return false
                }

                var url = this.url + '/update/payment/option'
                var params = {
                    'payment_option_id': this.toEdit.id,
                    'card': this.card
                }
                this.isLoading = false
                axios.put(url, params).then(request => {
                    this.isLoading = false
                    this.loadCreditCardoptions()
                    $('#edit-stripe-card').modal('hide')
                })
            }
        }
    }
</script>
