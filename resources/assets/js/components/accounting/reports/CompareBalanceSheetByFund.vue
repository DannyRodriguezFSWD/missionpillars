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
                <div class="input-group mb-3" @click="togglePopover({ placement: 'auto-start' })" >
                  <div class="input-group-prepend">
                    <button class="btn fa fa-calendar btn-outline-secondary"></button>
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
                <h3>Compare Balance Sheet by Fund as of {{ date | formatDate }}</h3>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table borderless">
                <thead class="thead-inverse">
                <tr>
                    <th>Account Number</th>
                    <th>Account Name</th>
                    <th v-for="fund in funds" v-if="fundsToShow.includes(fund.id)">{{ fund.name }}</th>
                </tr>
                </thead>
                <tbody>
                <tr class="table-title">
                    <td v-bind:colspan="2 + fundsToShow.length"><h4>Assets</h4></td>
                </tr>
                <tr v-for="(account, index) in report_data.asset" :key="index"
                    v-if="account.balance !== 0 || (account.balance === 0 && zero_balances === true)">
                    <td>{{ account.number }}</td>
                    <td>{{ account.name }}</td>
                    <td v-for="(fund, idx) in account.funds" :key="idx" class="text-right" v-if="fundsToShow.includes(fund.id)">
                        {{ fund.balance }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="bold-totals">Total assets</td>
                    <template v-for="fund in funds" v-if="fundsToShow.includes(fund.id)">
                        <td class="bold-totals text-right totals-number" v-if="totals.assets[fund.id] && totals.assets[fund.id].positive">{{ totals.assets[fund.id].amount }}</td>
                        <td class="bold-totals text-right totals-number" v-if="totals.assets[fund.id] && !totals.assets[fund.id].positive">({{ totals.assets[fund.id].amount }})</td>
                    </template>
                </tr>
                <tr class="blank_row">
                    <td v-bind:colspan="2 + fundsToShow.length"></td>
                </tr>
                <tr class="table-title">
                    <td v-bind:colspan="2 + fundsToShow.length"><h4>Liabilities</h4></td>
                </tr>
                <tr v-for="(account, index) in report_data.liability" :key="index"
                    v-if="account.balance !== 0 || (account.balance === 0 && zero_balances === true)">
                    <td>{{ account.number }}</td>
                    <td>{{ account.name }}</td>
                    <td v-for="(fund, idx) in account.funds" :key="idx" class="text-right" v-if="fundsToShow.includes(fund.id)">
                        {{ fund.balance }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="bold-totals">Total liabilities</td>
                    <template v-for="fund in funds" v-if="fundsToShow.includes(fund.id)">
                        <td class="bold-totals text-right totals-number" v-if="totals.liabilities[fund.id] && totals.liabilities[fund.id].positive">{{ totals.liabilities[fund.id].amount }}</td>
                        <td class="bold-totals text-right totals-number" v-if="totals.liabilities[fund.id] && !totals.liabilities[fund.id].positive">({{ totals.liabilities[fund.id].amount }})</td>
                    </template>
                </tr>
                <tr class="blank_row">
                    <td v-bind:colspan="2 + fundsToShow.length"></td>
                </tr>
                <tr class="table-title">
                    <td v-bind:colspan="2 + fundsToShow.length"><h4>Equity</h4></td>
                </tr>
                <tr v-for="(account, index) in report_data.equity" :key="index"
                    v-if="account.balance !== 0 || (account.balance === 0 && zero_balances === true)">
                    <td>{{ account.number }}</td>
                    <td>{{ account.name }}</td>
                    <template v-for="fund in funds" v-if="fundsToShow.includes(fund.id)">
                        <td v-for="(f, idx) in account.funds" :key="idx" class="text-right">
                            <span v-if="f.id == fund.id">
                                {{ f.balance }}
                            </span>
                            <span v-else>
                                $0.00
                            </span>
                        </td>
                    </template>
                </tr>
                <tr>
                    <td colspan="2" class="bold-totals">Total equity</td>
                    <template v-for="fund in funds" v-if="fundsToShow.includes(fund.id)">
                        <td class="bold-totals text-right totals-number" v-if="totals.equity[fund.id] && totals.equity[fund.id].positive">{{ totals.equity[fund.id].amount }}</td>
                        <td class="bold-totals text-right totals-number" v-if="totals.equity[fund.id] && !totals.equity[fund.id].positive">({{ totals.equity[fund.id].amount }})</td>
                    </template>
                </tr>
                <tr>
                    <td colspan="2" class="bold-totals">Total Liabilities + Total Equity</td>
                    <template v-for="fund in funds" v-if="fundsToShow.includes(fund.id)">
                        <td class="bold-totals text-right totals-number" v-if="totals.liabilitiesEquity[fund.id] && totals.liabilitiesEquity[fund.id].positive">{{ totals.liabilitiesEquity[fund.id].amount }}</td>
                        <td class="bold-totals text-right totals-number" v-if="totals.liabilitiesEquity[fund.id] && !totals.liabilitiesEquity[fund.id].positive">({{ totals.liabilitiesEquity[fund.id].amount }})</td>
                    </template>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    })

    export default {
        mounted() {
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
                    assets: [],
                    liabilities: [],
                    equity: [],
                    liabilitiesEquity: []
                },
                zero_balances: false,
                url: '',
                fundsToShow: this.funds
            }
        },
        methods: {
            createUrl() {
                var href = 'bs-report-download?';
                var params = 'date=' + this.date + '&' + 'show_zero=' + this.zero_balances + '&';
                if (this.filters == 'true') {
                    for (var i = 0; i < this.filters_fund_array.length; i++) {
                        params = params + 'by_funds=1&fund_ids[]=' + this.filters_fund_array[i] + '&'
                    }
                }
                this.url = href + params.slice(0, -1)
            },
            calcTotals() {
                var that = this
                var assetsTotal = []
                var equityTotal = []
                var liabilitiesTotal = []
                var liabilitiesEquityTotal = []
                $.each(this.report_data.asset, function (index, account) {
                    $.each(account.funds, function (idx, val) {
                        if (typeof assetsTotal[val.id] === 'undefined') {
                            assetsTotal[val.id] = 0.00
                        }
                        
                        if(idx > 0 && val.id == account.funds[idx-1].id){
                            assetsTotal[val.id] += Number(val.balance)
                        }
                        else{
                            assetsTotal[val.id] += Number(val.balance)
                        }

                        if (val.balance < 0) {
                            val.balance = '(' + formatter.format(Math.abs(val.balance)) + ')'
                        } else {
                            val.balance = formatter.format(Math.abs(val.balance))
                        }
                    })
                })

                $.each(this.report_data.liability, function (index, account) {
                    $.each(account.funds, function (idx, val) {
                        if (typeof liabilitiesTotal[val.id] === 'undefined') {
                            liabilitiesTotal[val.id] = 0.00
                        }
                        
                        if(idx > 0 && val.id == account.funds[idx-1].id){
                            liabilitiesTotal[val.id] += Number(val.balance)
                        }
                        else{
                            liabilitiesTotal[val.id] += Number(val.balance)
                        }
                        
                        if (val.balance < 0) {
                            val.balance = '(' + formatter.format(Math.abs(val.balance)) + ')'
                        } else {
                            val.balance = formatter.format(Math.abs(val.balance))
                        }
                    })
                })

                $.each(this.report_data.equity, function (index, account) {
                    $.each(account.funds, function (idx, val) {
                        if (typeof equityTotal[val.id] === 'undefined') {
                            equityTotal[val.id] = 0.00
                        }
                        
                        if(idx > 0 && val.id == account.funds[idx-1].id){
                            equityTotal[val.id] += Number(val.balance)
                        }
                        else{
                            equityTotal[val.id] += Number(val.balance)
                        }

                        if (val.balance < 0) {
                            val.balance = '(' + formatter.format(Math.abs(val.balance)) + ')'
                        } else {
                            val.balance = formatter.format(Math.abs(val.balance))
                        }
                    })
                })

                assetsTotal.forEach(function(balance, index) {
                    if (typeof that.totals.assets[index] === 'undefined') {
                        that.totals.assets[index] = {
                            amount: 0,
                            positive: true
                        }
                    }
                    that.totals.assets[index].amount = formatter.format(Math.abs(balance))
                    if(balance < 0) {
                        that.totals.assets[index].positive = false
                    } else {
                        that.totals.assets[index].positive = true
                    }
                    
                })
                liabilitiesTotal.forEach(function(balance, index) {
                    if (typeof that.totals.liabilities[index] === 'undefined') {
                        that.totals.liabilities[index] = {
                            amount: 0,
                            positive: true
                        }
                    }
                    that.totals.liabilities[index].amount = formatter.format(Math.abs(balance))
                    if(balance < 0) {
                        that.totals.liabilities[index].positive = false
                    } else {
                        that.totals.liabilities[index].positive = true
                    }
                })
                equityTotal.forEach(function(balance, index) {
                    if (typeof that.totals.equity[index] === 'undefined') {
                        that.totals.equity[index] = {
                            amount: 0,
                            positive: true
                        }
                    }
                    that.totals.equity[index].amount = formatter.format(Math.abs(balance))
                    if(balance < 0) {
                        that.totals.equity[index].positive = false
                    } else {
                        that.totals.equity[index].positive = true
                    }
                })

                this.filters_fund_array.forEach(function(index) {
                    if (typeof liabilitiesEquityTotal[index] === 'undefined') {
                        if(liabilitiesTotal[index] === undefined) liabilitiesTotal[index] = 0;
                        if(equityTotal[index] === undefined) equityTotal[index] = 0;
                        liabilitiesEquityTotal[index] = liabilitiesTotal[index] + equityTotal[index]
                    }
                    if (typeof that.totals.liabilitiesEquity[index] === 'undefined') {
                        that.totals.liabilitiesEquity[index] = {
                            amount: 0,
                            positive: true
                        }
                    }
                    that.totals.liabilitiesEquity[index].amount = formatter.format(Math.abs(liabilitiesEquityTotal[index]))
                    if(liabilitiesEquityTotal[index] < 0) {
                        that.totals.liabilitiesEquity[index].positive = false
                    } else {
                        that.totals.liabilitiesEquity[index].positive = true
                    }
                })
            },
            ReportWithFundFilters() {
                $('#overlay').fadeIn();
                axios.get('/accounting/reports/compare-balance-sheet-by-fund', {
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
                    this.fundsToShow = this.filters_fund_array;
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