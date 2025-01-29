<template>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">Account Starting Balances</div>
                <div class="card-body">
                    <div class="row">
                        <flash-message class="flash-message-custom"></flash-message>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label for="fund" class="control-label">Select fund: </label>
                            <select name="fund" id="fund" class="form-control" v-model="fund_list" @change="getColumns($event.target.value)">
                                <option value="all">All Funds</option>
                                <option v-for="fund in funds" :key="fund.id" :value="fund.id">
                                    {{ fund.account.number }} - {{ fund.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-sm-12 table-responsive">
                            <table class="table table-bordered" v-if="show_table">
                                <thead>
                                    <tr v-if="columns.length < 1">
                                        <th v-for="column in columns" class="text-center">
                                            {{ column[2] }}
                                            <br>
                                            {{ column[0] }}
                                        </th>
                                    </tr>
                                    <tr v-else>
                                        <th>&nbsp;</th>
                                        <th>Group</th>
                                        <th v-for="column in columns" class="text-center">
                                            {{ column[2] }}
                                            <br>
                                            {{ column[0] }}
                                        </th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(account, index) in accounts" :key="index">
                                        <td :style="{background: AssetOrLiability(groups_arr[account.id])}">&nbsp;</td>
                                        <td v-if="accounts[index - 1] !== undefined && groups_arr[accounts[index - 1].id] == groups_arr[account.id]" :style="{display: 'none', background: AssetOrLiability(groups_arr[account.id])}"></td>
                                        <td v-else :rowspan="a[groups_arr[account.id]]">{{ groups_arr[account.id] }}</td>
                                        <td>{{ account.number }}</td>
                                        <td>{{ account.name }}</td>
                                        <td v-for="col in dynamic_columns">
                                            <input type="number" step="0.01" class="form-control" 
                                            @change="handler($event.target.value, account.id, col[1])" 
                                            v-for="sb in balances" 
                                            :key="sb.id" 
                                            v-if="permissions['accounting-update'] && sb.account_id == account.id && sb.fund_id == col[1]" 
                                            :value="sb.balance" 
                                            onkeydown="javascript: return event.keyCode == 69 ? false : true">
                                            <span v-for="sb in balances" v-if="!permissions['accounting-update'] && sb.account_id == account.id && sb.fund_id == col[1]"> ${{ sb.balance }} </span>
                                        </td>
                                        <td v-if="total.length > 0 && total != undefined" class="text-right bg-default" style="font-weight: bold;background-color:#f8f8f8;">
                                            ${{ total[account.id] }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <div style="height: 10px; width: 10px; background: #9fce8b; display: inline-block;"></div><span style="vertical-align: text-bottom;"> - Assets</span>
                    <div style="height: 10px; width: 10px; background: #de9292; display: inline-block;" class="ml-2"></div><span style="vertical-align: text-bottom;"> - Liabilities</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.flash-message-custom {
    width: 100%;
    padding: 0 15px;
}

.table td {
    vertical-align: middle;
}
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0; 
}
</style>


<script>
export default {
     mounted () {
        // console.log('mounted');
    },
    props: [
        'currentUser',
        'groups',
        'funds',
        'sb',
        'permissions',
    ],
    data () {
        return {
            accounts: [],
            static_columns: [['Account Number'], ['Account Name']],
            show_table: false,
            fund_list: 'all',
            columns: [],
            dynamic_columns: [],
            balances: [],
            options: {
                headings: {
                    number: 'Account Number',
                    name: 'Account Name',
                },
                filterable: false,
                texts: {
                    count: ''
                }
            },
            total: [],
            input_data: {
                balance: '',
                account_id: '',
                fund_id: ''
            },
            groups_arr: [],
            a: [],
            b: [],
            counter: 0,
            bgColorRed: 'red',
            bgColorGreen: 'green',
            bgColor: '#fff'
        }
    },
    created () {
        this.getAccounts()
    },
    methods: {
        handler (input, acc_id, f_id) {
            this.createOrUpdateStartingBalance(input, acc_id, f_id);
            this.calcTotal(input, acc_id, f_id);
        },
        AssetOrLiability (group_name) {
            var bgColor;
            $.each(this.groups, function(index, group) {
                if (group.name == group_name) {
                    if (group.chart_of_account == 'asset') {
                        bgColor = '#9fce8b'
                        return false
                    } else if (group.chart_of_account == 'liability') {
                        bgColor = '#de9292'
                        return false
                    } else if (group.chart_of_account !== undefined){
                        bgColor = '#fff'
                        return false
                    }
                }
            })
            return bgColor;
        },
        calcTotal (val, acc_id, f_id) {
            var fl = this.fund_list
            var total = 0
            $.each(this.balances, function(index, bal) {
                if (bal.account_id == acc_id) {
                    if(bal.fund_id == f_id) {
                        bal.balance = parseFloat(val);
                        if (fl !== 'all') {
                            total += bal.balance
                        }
                    }
                    if (fl == 'all') {
                        total += bal.balance
                    }
                    
                }
            })
            this.total[acc_id] = total.toFixed(2)
        },
        createOrUpdateStartingBalance (input, acc_id, f_id) {
            this.input_data.balance = input
            this.input_data.account_id = acc_id
            this.input_data.fund_id = f_id
            axios.put('sb/'+acc_id, {sb: this.input_data, user: this.currentUser})
                .then((res) => {
                    this.flash('<div class="alert alert-success">'+res.data+'</div>', 'success', {
                        timeout: 3000,
                        important: true
                    });
                })
                .catch((err)=> {
                    this.flash('<div class="alert alert-danger">'+err.response.data+' Please reload page and try again!</div>', 'error', {
                        timeout: 5000,
                        important: true
                    });
                });
        },
        getAccounts () {
            var sb = [];
            var accounts = [];
            var total = this.total
            var g = this.groups_arr
            var group_count = [];
            $.each(this.groups, function(index, group) {
                group_count[group.name] = group.accounts.length;
                if (group.accounts !== undefined || group.accounts.length != 0) {
                    $.each(group.accounts, function(i, account) {
                        g[account.id] = group.name
                        var t = 0;
                        accounts.push(account);
                        $.each(account.starting_balance, function(ind, starting_bal) {
                            sb.push(starting_bal);
                            t += starting_bal.balance
                        })
                        total[account.id] = t.toFixed(2);
                    })
                }
            })

            this.balances = sb;
            this.accounts = accounts;
            this.a = group_count;
            this.getColumns(this.fund_list)
        },
        getColumns (fund_list) {
            // console.log(this.funds)
            this.columns = [];
            var total = this.total
            var accounts = this.accounts

            if (fund_list == 'all') {
                var columns = [];
                $.each(this.funds, function(index, fund) {
                    columns.push([fund.name, fund.id, fund.account.number]);
                    $.each(accounts, function(ind, account) {
                        var t = 0
                        $.each(account.starting_balance, function(i, sb) {
                           t += sb.balance
                        })
                        total[account.id] = t.toFixed(2)
                    })
                })
            } else {
                var columns = []
                $.each(this.funds, function(index, fund) {
                    if (fund_list == fund.id) {
                        columns.push([fund.name, fund.id, fund.account.number]);
                        $.each(accounts, function(ind, account) {
                            $.each(account.starting_balance, function(i, sb) {
                                if (sb.fund_id == fund.id) {
                                    total[account.id] = sb.balance.toFixed(2)
                                }
                            })
                        })
                    }
                })
            }
            this.columns = this.static_columns.concat(columns);
            this.dynamic_columns = columns;
            this.show_table = true
            if (fund_list == '') {
                this.show_table = false
            }
        }
    }
    
}
</script>
