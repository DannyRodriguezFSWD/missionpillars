<template>
    <div class="accounting-match-transactions">
        <div>
            <button type="button" class="btn btn-primary" @click="openLinkTransactionsModal">
                <i class="fa fa-link"></i>
                Link to contributions
            </button>
        </div>

        <CRMModal v-if="showLinkTransactionsModal" modal-container-style="max-height: 100vh; overflow-y: auto;" modal-body-style="margin: 0;" modal-footer-style="display: none;">
            <div slot="header">
                <h3 class="d-inline">Link transactions</h3>
                <button class="close" type="button" @click="closeModal()">&times;</button>
            </div>
            <div slot="body">
                <div class="row">
                  <div class="col-12">
                    <p class="m-0 p-0">Search transaction between a specific date range</p>
                    <div class="row match-datepicker">
                      <div class="col-xl-4">
                        <label class="m-0 p-0" for="">From</label>
                        <button type=button class="btn btn-sm btn-primary" @click="prevDate('from')">&lt;</button>
                        <datepicker v-model="state.from" :format="customFormatter" :highlighted="state.highlighted" :bootstrap-styling="true" input-class="bg-white" name="from" id="from" placeholder="Choose date"></datepicker>
                        <button type=button class="btn btn-sm btn-primary" @click="nextDate('from')">&gt;</button>
                      </div>
                      <div class="col-xl-4">
                        <label class="m-0 p-0" for="">To</label>
                        <button type=button class="btn btn-sm btn-primary" @click="prevDate('to')">&lt;</button>
                        <datepicker v-model="state.to" :format="customFormatter" :highlighted="state.highlighted" :bootstrap-styling="true" input-class="bg-white" name="to" id="to" placeholder="Choose date"></datepicker>
                        <button type=button class="btn btn-sm btn-primary" @click="nextDate('to')">&gt;</button>
                      </div>
                      <div class="col-xl-4">
                        <br/>
                        <div class="row">
                          <div class="col-md-12 col-lg-4">
                            <button class="btn btn-primary mb-1" @click="search()">
                              <i class="fa fa-search"></i> Search
                            </button>
                          </div>
                          <div class="col-md-12 col-lg-8">
                            <button @click="useSelectedTransactions()" class="btn btn-success" :disabled="select_button_disabled">
                              <i class="fa fa-check-square"></i> Use selected transactions
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row my-3">
                      <div class="col-12">
                        <table class="table table-borderless table-sm text-right mb-0">
                          <tbody>
                          <tr>
                            <td>{{ getHelperStringState }} Amount</td>
                            <td style="width: 70px;">${{ formatMoney(getHelperAmountState) }}</td>
                          </tr>
                          <tr>
                            <td>- Contributions</td>
                            <td style="width: 70px;">${{ formatMoney(total_contributions) }}</td>
                          </tr>
                          <tr>
                            <td>Balance</td>
                            <td style="width: 70px;">${{ formatMoney(getHelperTotalAmountState) }}</td>
                          </tr>
                          </tbody>
                        </table>
                      </div>
                      <div class="col-sm-12">
                        <hr class="m-0 p-0">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <div class="table-responsive">
                          <table class="table table-striped">
                            <thead>
                            <tr>
                              <th>&nbsp;</th>
                              <th>Date</th>
                              <th>From</th>
                              <th>Purpose</th>
                              <th>Amount</th>
                              <th v-if="showFees">Fee</th>
                              <th style="font-size: smaller">Payment<br>Type</th>
                              <th style="font-size: smaller">Channel</th>

                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="item in transactions" :key="item.id" :title="item.transaction.comment ? 'comment: '+item.transaction.comment : ''">
                              <td class="text-center">
                                <input type="checkbox" v-model="select_transactions" :value="item">
                              </td>
                              <td>{{ dateTimeFormat(item.date) }}</td>
                              <td><span :title="item.contact.email">{{ item.contact.name }}</span></td>
                              <td>{{ item.purpose.name }}
                              <td>${{ item.amount }}</td>
                              <td v-if="showFees">
                                <span v-if="item.transaction.fee > 0">${{ item.transaction.fee }}</span>
                                <span v-else></span>
                              </td>
                              <td>{{ item.transaction.payment_type }}</td>
                              <td>{{ item.transaction.channel }}</td>
                            </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
        </CRMModal>
    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    import loading from '../Loading.vue'
    import CRMModal from '../crm-modal.vue'; 
    import Datepicker from 'vuejs-datepicker';
    
    export default {
        name: 'AccountingMatchTransactions',
        components: {
            Datepicker,
            loading,
            CRMModal
        },
        props: [],
        data() {
            return {
                state: {
                    highlighted: {
                        dates: [
                            new Date()
                        ]
                    },
                    from: this.customFormatter(),
                    to: this.customFormatter()
                },
                select_button_disabled: true,
                transactions: [],
                select_transactions: [],
                total_contributions: 0,
                total_sum: 0,
                /*
                disabledDates: {
                    to: new Date(moment().subtract(8, 'days') - 8640000)
                },
                */
                showLinkTransactionsModal: false,
                showFees: false
            }
        },
        mounted() {
            //console.log('accounting match transactions MOUNTED')
            //console.log(moment().subtract(8, 'days'))
        },
        computed: {
            ...mapGetters([
                'getIsLoadingState',
                'getHelperAmountState',
                'getHelperStringState',
                'getHelperTotalAmountState',
            ]),
            ...mapState([
                
            ])
        },
        watch: {
            select_transactions(newValue) {
                let t = this.getHelperAmountState
                this.total_contributions = 0
                this.total_sum = t - this.total_contributions 
                if(this.select_transactions.length > 0){
                    this.select_button_disabled = false
                    for(let i in this.select_transactions){
                        this.total_contributions = this.total_contributions + parseFloat(this.select_transactions[i].amount)
                        
                        if (this.select_transactions[i].transaction.fee > 0) {
                            this.total_contributions = this.total_contributions - parseFloat(this.select_transactions[i].transaction.fee);
                        }
                        
                        this.total_sum = t - this.total_contributions
                    }
                }
                else{
                    this.select_button_disabled = true
                }
                this.$store.dispatch('setHelperTotalAmountState', this.total_sum)
            }
        },
        methods: {
            formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") {
                try {
                    decimalCount = Math.abs(decimalCount);
                    decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

                    const negativeSign = amount < 0 ? "-" : "";

                    let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
                    let j = (i.length > 3) ? i.length % 3 : 0;

                    return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
                } catch (e) {
                    console.log('formatMoney error', e)
                }
            },
            customFormatter(date) {
                return moment(date).format('YYYY-MM-DD');
            },
            dateTimeFormat(date) {
                return moment(date).format('MM/DD/YYYY h:mm a')
            },
            ...mapActions([
                'post',
                'get',
                'setHelperTotalAmountState'
            ]),
            closeModal: function(){
                this.showLinkTransactionsModal = false
                this.reset()
            },
            search(){
                this.reset()
                var data = { 
                    from: this.state.from,
                    to: this.state.to
                }
                this.get({
                    url: '/accounting/ajax/transactions/accounting/linking',
                    data: data
                }).then(result => {
                    this.transactions = result.data
                    this.updateShowFees();
                })
            },
            useSelectedTransactions(){
                this.$emit('use-selected-transactions', this.select_transactions)
                this.reset()
                this.closeModal()
            },
            reset(){
                this.select_button_disabled = true;
                this.transactions = [];
                this.select_transactions = [];
                this.total_contributions = 0;
                this.total_sum = 0;
            },
            convertDate(date) {
                var yyyy = date.getUTCFullYear().toString();
                var mm = (date.getUTCMonth()+1).toString();
                var dd  = date.getUTCDate().toString();

                var mmChars = mm.split('');
                var ddChars = dd.split('');

                return yyyy + '-' + (mmChars[1]?mm:"0"+mmChars[0]) + '-' + (ddChars[1]?dd:"0"+ddChars[0]);
            },
            prevDate(attr) {
                // console.log('prev', this.state[attr])
                var d = new Date(this.state[attr])
                d.setUTCDate(d.getUTCDate() - 1)
                this.state[attr] = this.convertDate(d)
            },
            nextDate(attr) {
                // console.log('next', this.state[attr])
                var d = new Date(this.state[attr])
                d.setUTCDate(d.getUTCDate() + 1)
                this.state[attr] = this.convertDate(d)
            },
            openLinkTransactionsModal() {
                this.state.from = this.addWorkDays(new Date($("#date").val()), -2);
                this.state.to = this.addWorkDays(new Date($("#date").val()), 2);
                this.showLinkTransactionsModal = true
            },
            addWorkDays(startDate, days) {
                if (isNaN(days)) {
                    console.log("Value provided for \"days\" was not a number");
                    return
                }
                if(!(startDate instanceof Date)) {
                    console.log("Value provided for \"startDate\" was not a Date object");
                    return
                }
                
                // Current day
                var dow = startDate.getDay();
                var daysToAdd = parseInt(days);
                
                // If Monday
                if (dow == 1 && days < 0) {
                    daysToAdd--;
                }
                
                // If Tuesday
                if (dow == 2 && days < 0) {
                    daysToAdd--;
                    daysToAdd--;
                }
                
                // If Thursday
                if (dow == 4 && days > 0) {
                    daysToAdd++;
                    daysToAdd++;
                }
                
                // If Friday
                if (dow == 5 && days > 0) {
                    daysToAdd++;
                }
                
                startDate.setDate(startDate.getDate() + daysToAdd);
                
                var day = startDate.getDate();
                var month = startDate.getMonth() + 1;
                var year = startDate.getFullYear();
                if (day < 10) {
                    day = '0' + day;
                }
                if (month < 10) {
                    month = '0' + month;
                }
                
                return year + '-' + month + '-' + day;
            },
            updateShowFees() {
                this.showFees = false;
                
                if (this.transactions.length > 0) {
                    for (let i=0; i<this.transactions.length; i++) {
                        if (this.transactions[i].transaction.fee > 0) {
                            this.showFees = true;
                            break;
                        }
                    }
                }
            }
        }
    }
</script>

<style scoped>
.match-datepicker > div > button,
.match-datepicker input#from,
.match-datepicker input#to,
.match-datepicker > div,
.match-datepicker div.vdp-datepicker,
.match-datepicker div.input-group {
    display: inline-block !important;
}
.match-datepicker > div > label {
    display: block;
}
.match-datepicker > div {
    white-space: nowrap;
}
.match-datepicker > div * {
    white-space: normal;
}

/**** HACKS for Edge, Safari, and mobile Safari/Chrome

/* Edge hack */
@supports (-ms-ime-align:auto) {
    div.table-responsive {
        max-height: 50vh;
        overflow-x: hidden;
        overflow-y: auto;
    }
}

/* Safari hack 7.1+ DOESN'T WORK  */
_::-webkit-full-page-media, _:future, :root .safari_only {
    div.table-responsive {
        max-height: 50vh;
        overflow-x: hidden;
        overflow-y: auto;
    }
}

/* Giving up, and styling all browsers the same :-( */

div.table-responsive {
    max-height: 30vh;
}
</style>
<style>
@media (min-width: 576px) {
    #link-bank-transaction-modal .modal-dialog:not(.plaid-error-modal) {
        max-width: 90vw !important;
    }
}
th {
    vertical-align: bottom;
}
@media only screen and (max-width: 600px) {
  #link-bank-transaction-modal .modal-container{
    padding: 10px 10px;
    width: 80vw;
  }
}
</style>
