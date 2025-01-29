<template>
    <div class="card-body">
        <div class="row hide-on-print">
            <div class="col-md-12 text-right">
                <button class="btn btn-secondary" type="button" @click="printReport()">
                    <i class="fa fa-download"></i> Print/Download as PDF
                </button>
            </div>
        </div>
        <div class="row hide-on-print">
            <div class="form-group col-md-2">
                <label for="period">Select Period</label>
                <select name="period" id="period" class="form-control" v-model="range" @change="query()">
                    <option value="1">This Month</option>
                    <option value="2">Last Month</option>
                    <option value="3">This Quarter</option>
                    <option value="4">Last Quarter</option>
                    <option value="5">This Year</option>
                    <option value="6">Last Year</option>
                    <option value="7">Custom</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="filters">Select Filter</label>
                <select name="filters" id="filters" class="form-control" v-model="filters">
                    <option value="false">Report Filters</option>
                    <option value="true">Funds</option>
                </select>
            </div>
        </div>
        <!--
        <div class="row mt-3 hide-on-print">
            <div class="custom-control custom-checkbox col-md-2 ml-3 mr-0">
                <input type="checkbox" name="zero_balances" id="zero_balances" class="custom-control-input"  v-model="zero_balances" @change="query()">
                <label class="custom-control-label" for="zero_balances">Show zero balance</label>
            </div>
        </div>
        -->
        <div class="row mt-3" v-if="range==7">
            <div class="form-group col-md-2">
                <label for="start-date" >Start date</label>
                <datepicker v-model="start_date" :format="customFormatter" :highlighted="highlighted" :bootstrap-styling="true" input-class="bg-white" name="from" id="from" placeholder="Choose date"></datepicker>
            </div>
            <div class="form-group col-md-2">
                <label for="end-date" >End date</label>
                <datepicker v-model="end_date" :format="customFormatter" :highlighted="highlighted" :bootstrap-styling="true" input-class="bg-white" name="to" id="to" placeholder="Choose date"></datepicker>
            </div>
        </div>
        <div class="row mt-3" v-if="range==7">
            <div class="col-md-4 alert alert-danger" role="alert" v-if="filterIsInvalid">
                {{ customPeriodValidationError }}
            </div> 
        </div>
        <div class="row mt-3 hide-on-print" v-if="filters === 'true'">
            <div class="custom-control custom-checkbox col-md-2 ml-3 mr-0" v-for="(fund, index) in funds" :key="index">
                <input type="checkbox" :checked="true" :name="'fund-' + index" :id="'fund-' + index" class="custom-control-input" :value="fund.id" v-model="filters_fund_array">
                <label class="custom-control-label" :for="'fund-' + index">{{ fund.name }}</label>
            </div>
        </div>
        <div class="row mt-3 hide-on-print" v-if="filters === 'true' || range == 7">
            <div class="col-md-2">
                <input type="button" value="Apply" class='btn btn-success' @click="query()" :disabled="filterIsInvalid">
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-12 text-center">
                <h5>{{ tenant.organization }}</h5>
                <h5>Income Statement by Month {{ reportPeriod }}</h5>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table borderless">
                <thead class="thead-inverse">
                    <tr>
                        <th>&nbsp;</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th v-for="(month, index) in report.current_months" :key="index">
                            {{ month.name }}
                        </th>
                        <th>{{ totalLabel }}</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <h4>Income</h4>
                        </td>
                        <td v-for="(month, index) in report.current_months" :key="index">&nbsp;</td>
                        <td>&nbsp;</td>
                        <th>&nbsp;</th>
                        <td>&nbsp;</td>
                    </tr>
                    <template v-for="income in report.income">
                        <tr v-for="account in income.accounts" :key="account.account_id">
                            <td>&nbsp;</td>
                            <td class="text-right">{{ account.account_number }}</td>
                            <td class="text-right">{{ account.account_name }}</td>
                            <td class="text-right" v-for="(month, index) in account.months" :key="index">
                                <span v-if="month.amount < 0">
                                    (${{ month.amount == null ? '0.00' : month.amount.toFixed(2) }})
                                </span>
                                <span v-else>
                                    ${{ month.amount == null ? '0.00' : month.amount.toFixed(2) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span v-if="account.total_year < 0">
                                    (${{ account.total_year == null ? '0.00' : account.total_year.toFixed(2) }})
                                </span>
                                <span v-else>
                                    ${{ account.total_year == null ? '0.00' : account.total_year.toFixed(2) }}
                                </span>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><strong>Total {{ income.group.group_name }} Amount</strong></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="totals-number" v-for="(month, index) in report.current_months" :key="index">
                                &nbsp;
                            </td>
                            <td class="totals-number">&nbsp;</td>
                            <td class="text-right totals-number">
                                <strong v-if="income.group.total_group_amount < 0">
                                    (${{ income.group.total_group_amount == null ? '0.00' : income.group.total_group_amount.toFixed(2) }})
                                </strong>
                                <strong v-else>
                                    ${{ income.group.total_group_amount == null ? '0.00' : income.group.total_group_amount.toFixed(2) }}
                                </strong>
                            </td>
                            <td class="totals-number">&nbsp;</td>
                        </tr>
                    </template>
                    <tr>
                        <td>
                            <strong>Total Income</strong>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="totals-number" v-for="(month, index) in report.current_months" :key="index">
                            &nbsp;
                        </td>
                        <td class="totals-number">&nbsp;</td>
                        <td class="totals-number">&nbsp;</td>
                        <td class="text-right totals-number">
                            <strong v-if="report.total_income < 0">
                                    (${{ report.total_income == null ? '0.00' : report.total_income.toFixed(2) }})
                            </strong>
                            <strong v-else>
                                ${{ report.total_income == null ? '0.00' : report.total_income.toFixed(2) }}
                            </strong>
                        </td>
                    </tr>
                    <tr class="blank_row"><td colspan="6">&nbsp;</td></tr>
                    <tr>
                        <td>
                            <h4>Expense</h4>
                        </td>
                        <td v-for="(month, index) in report.current_months" :key="index">&nbsp;</td>
                        <td>&nbsp;</td>
                        <th>&nbsp;</th>
                        <td>&nbsp;</td>
                    </tr>
                    <template v-for="expense in report.expense">
                        <tr v-for="account in expense.accounts" :key="account.account_id">
                            <td>&nbsp;</td>
                            <td class="text-right">{{ account.account_number }}</td>
                            <td class="text-right">{{ account.account_name }}</td>
                            <td class="text-right" v-for="(month, index) in account.months" :key="index">
                                <span v-if="month.amount < 0">
                                    (${{ month.amount == null ? '0.00' : month.amount.toFixed(2) }})
                                </span>
                                <span v-else>
                                    ${{ month.amount == null ? '0.00' : month.amount.toFixed(2) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span v-if="account.total_year < 0">
                                    (${{ account.total_year == null ? '0.00' : account.total_year.toFixed(2) }})
                                </span>
                                <span v-else>
                                    ${{ account.total_year == null ? '0.00' : account.total_year.toFixed(2) }}
                                </span>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Total {{ expense.group.group_name }} Amount</strong>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="totals-number" v-for="(month, index) in report.current_months" :key="index">
                                &nbsp;
                            </td>
                            <td class="totals-number">&nbsp;</td>
                            <td class="text-right totals-number">
                                <strong v-if="expense.group.total_group_amount < 0">
                                    (${{ expense.group.total_group_amount == null ? '0.00' : expense.group.total_group_amount.toFixed(2) }})
                                </strong>
                                <strong v-else>
                                    ${{ expense.group.total_group_amount == null ? '0.00' : expense.group.total_group_amount.toFixed(2) }}
                                </strong>
                            </td>
                            <td class="totals-number">&nbsp;</td>
                        </tr>
                    </template>
                    <tr>
                        <td>
                            <strong>Total Expense</strong>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="totals-number" v-for="(month, index) in report.current_months" :key="index">
                            &nbsp;
                        </td>
                        <td class="totals-number">&nbsp;</td>
                        <td class="totals-number">&nbsp;</td>
                        <td class="text-right totals-number">
                            <strong v-if="report.total_expense < 0">
                                    (${{ report.total_expense == null ? '0.00' : report.total_expense.toFixed(2) }})
                            </strong>
                            <strong v-else>
                                ${{ report.total_expense == null ? '0.00' : report.total_expense.toFixed(2) }}
                            </strong>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <strong>Net Income (Loss)</strong>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="totals-number" v-for="(month, index) in report.current_months" :key="index">
                            &nbsp;
                        </td>
                        <td class="totals-number">&nbsp;</td>
                        <td class="totals-number">&nbsp;</td>
                        <td class="text-right totals-number">
                            <strong v-if="report.profit < 0">
                                (${{ (-1*report.profit).toFixed(2) }})
                            </strong>
                            <strong v-else>
                                ${{ report.profit.toFixed(2) }}
                            </strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>
<style>
.custom-checkbox .custom-control-label::before {
    border-radius: .25rem;
}
.custom-control-label::before {
    position: absolute;
    top: .25rem;
    left: 0;
    display: block;
    width: 1rem;
    height: 1rem;
    pointer-events: none;
    content: "";
    user-select: none;
    background-color: #dee2e6;
}
.custom-checkbox .custom-control-input:checked~.custom-control-label::before {
    background-color: #263238;
}
.custom-control-input:checked~.custom-control-label::before {
    color: #fff;
    background-color: #263238;
}

.blank_row {
    height: 46px;
}
.bold-totals {
    font-weight: bold;
}

.borderless td, .borderless th {
    border: none;
}

table thead th{
    text-align: center !important;
}

.custom-control-label::after {
    position: absolute;
    top: .25rem;
    left: 0;
    display: block;
    width: 1rem;
    height: 1rem;
    content: "";
    background-repeat: no-repeat;
    background-position: center center;
    background-size: 50% 50%;
}
table td.totals-number {
    border-top: solid 1px #afafaf;
}
.custom-checkbox .custom-control-input:checked~.custom-control-label::after {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3E%3Cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26 2.974 7.25 8 2.193z'/%3E%3C/svg%3E")
}
@media print {
    @page {
        size: auto !important;
        margin: 0;
    }
    
    .hide-on-print {
        display: none !important; 
    }
    
    .c-wrapper {
        margin-left: 0 !important;
    }
}
</style>
<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import loading from '../../Loading.vue'
import CRMModal from '../../crm-modal.vue'
import Datepicker from 'vuejs-datepicker';
let formatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
})

export default {
    mounted() {
        this.setReportPeriod(1);
    },
    created() {
        if(!this.filters_fund_array.length) {
            $.each(this.funds, (index, fund) => {
                this.filters_fund_array.push(fund.id);
            });
        }
        let params = this.getParams()
        this.getData(params);
    },
    props: [
        'funds',
        'tenant'
    ],
    components: {
        Datepicker,
        loading,
        CRMModal,
    },
    data() {
        return {
            start_date: new Date(),
            end_date: new Date(),
            filters: 'false',
            filters_fund_array: [],
            zero_balances: false,
            range: 1,
            report: {
                income: [],
                expense: [],
                total_income: 0,
                total_expense: 0,
                profit: 0,
                current_months: []
            },
            highlighted: {
                dates: [
                    new Date()
                ]
            },
            reportPeriod: null
        }
    },
    computed: {
        ...mapGetters([
            'getIsLoadingState',
            'getCurrentDate'
        ]),
        ...mapState([
            'crmmodal',
        ]),
        customEndDateBeforeStartDate() {
            console.log('customEndDateBeforeStartDate')
            return this.start_date > this.end_date 
        },
        customPeriodIsSameYear() {
            console.log('customPeriodIsSameYear', this.start_date, this.end_date, )
            return this.start_date.getFullYear() == this.end_date.getFullYear();
        },
        customPeriodValidationError() {
            if (this.customEndDateBeforeStartDate) return "The 'End date' cannot be before the start date"
            if (!this.customPeriodIsSameYear) return "Please select dates in the same calendar year"
        },
        filterIsInvalid() {
            return !this.isDefinedPeriod && (this.customEndDateBeforeStartDate || !this.customPeriodIsSameYear)
        },
        isDefinedPeriod() {
            return this.range != 7
        },
        totalLabel() {
            switch (this.range) {
                case '3':
                case '4':
                return "Qtr Total Amount"
                break;
                case '5':
                return 'YTD Amount'
                break;
                case '6':
                return 'Year Total Amount'
                break;
                default:
                case '1':
                case '2':
                case '7':
                return 'Total Amount';
                break;
            }
        }
    },
    methods: {
        ...mapActions([
            'post',
            'get'
        ]),
        query(){
            let params = this.getParams()
            this.getData(params)
        },
        getParams(){
            
            let data = {
                start_date: $('#from').val(),
                end_date: $('#to').val(),
                range: this.range,
                show_zero: this.zero_balances,
                funds: this.filters_fund_array
            }
            return data
        },
        getData(params){
            this.$store.dispatch('setIsLoadingState', true)
            this.get({
                url: '/accounting/reports/income-statement-by-month',
                data: params
            }).then(result => {
                this.report.income = result.data.income
                this.report.expense = result.data.expense
                this.report.total_income = result.data.total_income
                this.report.total_expense = result.data.total_expense
                this.report.profit = this.report.total_income - this.report.total_expense
                this.report.current_months = result.data.current_months
                this.setReportPeriod(params.range, result.data.date_range);
                this.$store.dispatch('setIsLoadingState', false)
            })
        },
        customFormatter(date) {
            return moment(date).format('YYYY-MM-DD');
        },
        showZeroBalances(){
            if(this.zero_balances){
                console.log(this.zero_balances)
                
            }
        },
        printReport() {
            $('header.c-header, footer.c-footer, nav[aria-label="breadcrumb"], .card-header, .card-footer, .c-sidebar').addClass('hide-on-print');
            $('.card').addClass('border-0');
            window.print();
        },
        setReportPeriod(range, data) {
            var date = null;
            
            if (data) {
                var start = new Date(data.start.date);
                var end = new Date(data.end.date);
            }
            
            if (range == 1) {
                date = new Date();
                this.reportPeriod = '- ' + monthNames[date.getMonth()] + ' ' + date.getFullYear();
            } else if (range == 2) {
                this.reportPeriod = '- ' + monthNames[end.getMonth()] + ' ' + end.getFullYear();
            } else if (range == 3 || range == 4) {
                this.reportPeriod = '- ' + monthNames[start.getMonth()] + ' - ' + monthNames[end.getMonth()] + ' ' + end.getFullYear();
            } else if (range == 5 || range == 6) {
                this.reportPeriod = '- ' + end.getFullYear();
            } else if (range == 7) {
                this.reportPeriod = '- ' + monthNames[start.getMonth()] + ' ' + start.getFullYear() + ' - ' + monthNames[end.getMonth()] + ' ' + end.getFullYear();
            } else {
                this.reportPeriod = null;
            }
        }
    }
}
</script>
