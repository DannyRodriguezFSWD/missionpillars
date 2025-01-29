<template>
    <div class="card-body">
        <div class="row hide-on-print">
            <div class="col-md-12 text-right">
                <button class="btn btn-secondary" target="_blank" @click="printReport()">
                    <i class="fa fa-download"></i> Print/Download as PDF
                </button>
            </div>
        </div>
        <div class="row hide-on-print">
            <div class="col-md-3">
              <label>Select Date</label>
              <v-date-picker v-model="date" mode="date" :model-config="{
                type: 'string',
                mask: 'YYYY-MM-DD', // Uses 'iso' if missing
              }">
                <template v-slot="{ inputValue, togglePopover }">
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <button @click="togglePopover({ placement: 'auto-start' })" class="btn fa fa-calendar btn-outline-secondary"></button>
                    </div>
                    <input :value="inputValue" readonly type="text" class="form-control" placeholder="" aria-label="" aria-describedby="basic-addon1">
                  </div>
                </template>
              </v-date-picker>
            </div>
            <div class="form-group col-md-2">
                <label for="filters">Select Filter</label>
                <select name="filters" id="filters" class="form-control" v-model="filters" @change="createUrl()">
                    <option value="false">Report Filters</option>
                    <option value="true">Funds</option>
                </select>
            </div>
        </div>
        <div class="row mt-3 hide-on-print">
            <div class="custom-control custom-checkbox col-md-2 ml-3 mr-0">
                <input type="checkbox" name="zero_balances" id="zero_balances" class="custom-control-input"
                       v-model="zero_balances" @change="createUrl()">
                <label class="custom-control-label" for="zero_balances">Show zero balance</label>
            </div>
        </div>
        <div class="row mt-3 hide-on-print" v-if="filters === 'true'">
            <div class="custom-control custom-checkbox col-md-2 ml-3 mr-0" v-for="(fund, index) in funds" :key="index">
                <input type="checkbox" :name="'fund-' + index" :id="'fund-' + index" class="custom-control-input"
                       :value="fund.id" v-model="filters_fund_array">
                <label class="custom-control-label" :for="'fund-' + index">{{ fund.name }}</label>
            </div>
        </div>
        <div class="row mt-3 hide-on-print" v-if="filters === 'true'">
            <div class="col-md-2">
                <input type="button" value="Apply" class='btn btn-success' @click.prevent="ReportWithFundFilters()">
            </div>
        </div>
        <div class="row mt-5">
            
            <div class="col-md-12 text-center">
                <h3>Balance Sheet as of {{ date | formatDate }}</h3>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table borderless">
                <thead class="thead-inverse">
                    <tr>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                <tr class="table-title">
                    <td colspan="3"><h4>Assets</h4></td>
                </tr>
                <tr v-for="(account, index) in report_data.asset" :key="index"
                    v-if="account.balance !== '$0.00' || (account.balance === '$0.00' && zero_balances === true)">
                    <td>{{ account.number }}</td>
                    <td>{{ account.name }}</td>
                    <td class="text-right">{{ account.balance }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="bold-totals">Total assets</td>
                    <td class="bold-totals text-right totals-number" v-if="totals.assets.positive">{{ totals.assets.amount }}</td>
                    <td class="bold-totals text-right totals-number" v-else>({{ totals.assets.amount }})</td>
                </tr>
                <tr class="blank_row">
                    <td colspan="3"></td>
                </tr>
                <tr class="table-title">
                    <td colspan="3"><h4>Liabilities</h4></td>
                </tr>
                <tr v-for="(account, index) in report_data.liability" :key="index"
                    v-if="account.balance !== '$0.00' || (account.balance === '$0.00' && zero_balances === true)">
                    <td>{{ account.number }}</td>
                    <td>{{ account.name }}</td>
                    <td class="text-right">{{ account.balance }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="bold-totals">Total liabilities</td>
                    <td class="bold-totals text-right totals-number" v-if="totals.liabilities.positive">{{ totals.liabilities.amount }}</td>
                    <td class="bold-totals text-right totals-number" v-else>({{ totals.liabilities.amount }})</td>
                </tr>
                <tr class="blank_row">
                    <td colspan="3"></td>
                </tr>
                <tr class="table-title">
                    <td colspan="3"><h4>Equity</h4></td>
                </tr>
                <tr v-for="(account, index) in report_data.equity" :key="index"
                    v-if="account.balance !== '$0.00' || (account.balance === '$0.00' && zero_balances === true)">
                    <td>{{ account.number }}</td>
                    <td>{{ account.name }}</td>
                    <td class="text-right">{{ account.balance }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="bold-totals">Total equity</td>
                    <td class="bold-totals text-right totals-number" v-if="totals.equity.positive">{{ totals.equity.amount }}</td>
                    <td class="bold-totals text-right totals-number" v-else>({{ totals.equity.amount }})</td>
                </tr>
                <tr>
                    <td colspan="2" class="bold-totals">Total Liabilities + Total Equity</td>
                    <td class="bold-totals text-right totals-number" v-if="totals.liabilitiesEquity.positive">{{ totals.liabilitiesEquity.amount }}</td>
                    <td class="bold-totals text-right totals-number" v-else>({{ totals.liabilitiesEquity.amount }})</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style>
    .borderless .table-title {
        border-bottom: #afafaf solid 1px;
        border-top: #afafaf solid 1px;
    }

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

    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #263238;
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        color: #fff;
        background-color: #263238;
    }

    .blank_row {
        height: 46px;
    }

    .bold-totals {
        font-weight: bold;
    }

    table th {
        text-align: center;
    }

    .borderless td, .borderless th {
        border: none;
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

    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::after {
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
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    })
    export default {
        mounted() {
            console.log('Reports Mounted')
            var that = this

            if(! that.filters_fund_array.length) {
                $.each(this.funds, function (index, fund) {
                    that.filters_fund_array.push(fund.id);
                });
            }
            this.ReportWithFundFilters()
        },
        created() {
            var that = this
            this.calcTotals();
            this.$nextTick(function () {
                that.createUrl();
            })
        },
        props: [
            'funds'
        ],
        data() {
            return {
                date: $.datepicker.formatDate('yy-mm-dd', new Date()),
                filters: 'false',
                filters_fund_array: [],
                report_data: [],
                totals: {
                    assets: {
                        amount: '',
                        positive: true
                    },
                    liabilities: {
                        amount: '',
                        positive: true
                    },
                    equity: {
                        amount: '',
                        positive: true
                    },
                    liabilitiesEquity: {
                        amount: '',
                        positive: true
                    }
                },
                zero_balances: false,
                url: ''
            }
        },
        methods: {
            createUrl() {
                var href = 'bs-report-download?';
                var params = 'date=' + this.date + '&' + 'show_zero=' + this.zero_balances + '&';
                if (this.filters == 'true') {
                    for (var i = 0; i < this.filters_fund_array.length; i++) {
                        params = params + 'fund_ids[]=' + this.filters_fund_array[i] + '&'
                    }
                }
                this.url = href + params.slice(0, -1)
            },
            calcTotals() {
                var assetsTotal = 0.00
                var equityTotal = 0.00
                var liabilitiesTotal = 0.00
                var liabilitiesEquityTotal = 0.00
                $.each(this.report_data.asset, function (index, val) {
                    assetsTotal += Number(val.balance)
                    if (val.balance < 0) {
                        val.balance = '(' + formatter.format(Math.abs(val.balance)) + ')'
                    } else {
                        val.balance = formatter.format(Math.abs(val.balance))
                    }
                })
                $.each(this.report_data.liability, function (index, val) {
                    liabilitiesTotal += Number(val.balance)
                    if (val.balance < 0) {
                        val.balance = '(' + formatter.format(Math.abs(val.balance)) + ')'
                    } else {
                        val.balance = formatter.format(Math.abs(val.balance))
                    }
                })
                $.each(this.report_data.equity, function (index, val) {
                    equityTotal += Number(val.balance)
                    if (val.balance < 0) {
                        val.balance = '(' + formatter.format(Math.abs(val.balance)) + ')'
                    } else {
                        val.balance = formatter.format(Math.abs(val.balance))
                    }
                })
                this.totals.assets.amount = formatter.format(Math.abs(assetsTotal))
                if(assetsTotal < 0) {
                    this.totals.assets.positive = false
                } else {
                    this.totals.assets.positive = true
                }
                this.totals.liabilities.amount = formatter.format(Math.abs(liabilitiesTotal))
                if(liabilitiesTotal < 0) {
                    this.totals.liabilities.positive = false
                } else {
                    this.totals.liabilities.positive = true
                }
                this.totals.equity.amount = formatter.format(Math.abs(equityTotal))
                if(equityTotal < 0) {
                    this.totals.equity.positive = false
                } else {
                    this.totals.equity.positive = true
                }
                liabilitiesEquityTotal = liabilitiesTotal + equityTotal
                this.totals.liabilitiesEquity.amount = formatter.format(Math.abs(liabilitiesEquityTotal))
                if(liabilitiesEquityTotal < 0) {
                    this.totals.liabilitiesEquity.positive = false
                } else {
                    this.totals.liabilitiesEquity.positive = true
                }
            },
            ReportWithFundFilters() {
                $('#overlay').fadeIn();
                axios.get('/accounting/reports/balance-sheet', {
                    params: {
                        fund_ids: this.filters_fund_array,
                        date: moment(this.date).format('YYYY-MM-DD')
                    }
                })
                .then((res) => {
                    $('#overlay').fadeOut();
                    this.report_data = res.data
                    this.calcTotals()
                    this.createUrl()
                })
                .catch((err) => {
                    $('#overlay').fadeOut();
                    console.log(err)
                })
            },
            printReport() {
                $('header.c-header, footer.c-footer, nav[aria-label="breadcrumb"], .card-header, .card-footer, .c-sidebar').addClass('hide-on-print');
                $('.card').addClass('border-0');
                window.print();
            }
        },
      watch:{
          date(){
            this.ReportWithFundFilters()
          }
      }
    }
</script>