<template>
    <div class="row">
        <div class="col-md-12 mt-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="contact_id">Contact</label>
                        <input type="text" name="contact" id="contact_id" class="form-control ui-autocomplete-input autocomplete" @keyup="findContact" autocomplete="off" placeholder="Contact's Name">
                        <input type="hidden" name="contact_id" :value="registers.contact_id">
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- <select name="account" id="account" class="form-control" v-model="registers.account_id">
                        <option value="">Select Account</option>
                        <optgroup v-for="group in groups" :key="group.id" :label="group.name" v-if='group.accounts.length > 0'>
                            <option v-for='account in group.accounts' :key="account.id" :value="account.id">{{ account.name }}</option>
                        </optgroup>
                    </select> -->
                    <label for="account_id">Account</label>
                    <input type="text" name="account" id="account_id" class="form-control ui-autocomplete-input" @keyup="findAccount" autocomplete="off" placeholder="Account Name">
                </div>
                <div class="col-md-4">
                    <!-- <select :name="'fund'" :id="'fund'" class="form-control" v-model="registers.fund_id">
                        <option value="">Select Fund</option>
                        <option v-for="fund in funds" :key="fund.id" :value="fund.id">{{ fund.name }}</option>
                    </select> -->
                    <label for="fund_id">Fund</label>
                    <input type="text" name="fund" id="fund_id" class="form-control ui-autocomplete-input" @keyup="findFund" autocomplete="off" placeholder="Fund Name">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" class="form-control" v-model="registers.comment" placeholder="Fund Name">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6"><strong>BANK DETAIL</strong> {{ rowData.description }}</div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <button class="btn btn-success" @click="addToRegisters()">Add</button>
                </div>
            </div>
        </div>
    </div>
    
</template>
<script>
let contact_id =''
export default {
    props: {
        rowData: {
            type: Object,
            required: true
        },
        rowIndex: {
            type: Number
        }
    },
    watch: {
        rowData: function() {
            this.row = this.rowData
        }
    },
    data () {
        return {
            row: this.rowData,
            funds: this.$parent.$parent.funds,
            groups: this.$parent.$parent.groups,
            registers: {
                amount: this.rowData.amount,
                comment: this.rowData.description,
                date: this.rowData.date,
                contact_id: '',
                account_id: '',
                fund_id: ''
            },
        }
    },
    methods: {
        addToRegisters() {
            // this.registers.contact_id =  contact_id
            axios.post('/accounting/bank/transactions/map', {transaction: this.row, registers: this.registers})
                .then((res) => {
                    this.$parent.$parent.$refs.vuetable.refresh()
                    console.log(res.data)
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        findAccount() {
            var that = this
            $('#account_id').autocomplete({
                source: function( request, response ) {
                    axios.post('/accounting/ajax/accounts/autocomplete', {search: request.term})
                        .then((res) => {
                            var data = {
                                        value: '',
                                        label: '+ Create New Fund',
                                        uri: '/accounting/accounts'
                                    }
                                res.data.unshift(data)
                                response( res.data )
                        })
                },
                minLength: 3,
                select: function( event, ui ) {
                    if (ui.item.data) {
                        that.registers.account_id = ui.item.data
                    } else {
                        var win = window.open(ui.item.uri, '_blank')
                        win.focus()
                    }
                }
            });
        },
        findFund() {
            var that = this
            $('#fund_id').autocomplete({
                source: function( request, response ) {
                    axios.post('/accounting/ajax/funds/autocomplete', {search: request.term})
                        .then((res) => {
                             var data = {
                                        value: '',
                                        label: '+ Create New Fund',
                                        uri: '/accounting/accounts'
                                    }
                                res.data.unshift(data)
                                response( res.data )
                        })
                },
                minLength: 3,
                select: function( event, ui ) {
                    if (ui.item.data) {
                        that.registers.fund_id = ui.item.data
                    } else {
                        var win = window.open(ui.item.uri, '_blank')
                        win.focus()
                    }
                }
            });
        },
        findContact: function() {
            var that = this
            $('.autocomplete').autocomplete({
                source: function( request, response ) {
                    axios.post('/crm/ajax/contacts/autocomplete', {search: request.term})
                        .then((res) => {
                            var data = {
                                        value: '',
                                        label: '+ Create New Fund',
                                        uri: '/crm/contacts/create'
                                    }
                                res.data.unshift(data)
                                response( res.data )
                        })
                },
                minLength: 2,
                select: function( event, ui ) {
                    that.registers.contact_id = ui.item.data
                }
            });
        },
    }
}
</script>

