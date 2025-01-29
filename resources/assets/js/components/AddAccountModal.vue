<template>
<div id="add-account-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Acccount</h4>
                <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form role="form">
                    <div class="row">
                        <flash-message class="flash-message-custom"></flash-message>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <strong><label for="account_name">Name</label></strong>
                                <input type="text" name="account_name" class="form-control" required='required' id='account-name' v-model="account.name">
                                <p class="error-account text-center alert alert-danger d-none error-name"></p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <strong><label>Sub-Account</label> </strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label><input type="checkbox" value="" id="sub-account-check" @click="subAccountToggle" v-model="account.sub_account"> Make this account a sub-account</label>
                                    </div>    
                                </div>
                                <div class="row sub-account-row" :class="[!account || !account.sub_account ? 'd-none' : '']">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong><label for="sub-account">Sub Account</label></strong>
                                            <select name="sub_acc" id="subacc" v-model="account.parent_account_id" class="form-control">
                                                <option v-for="s in sub" :key="s.id" v-bind:value="s.id">{{ s.name }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <strong><label for="account_number">Number</label></strong>
                                <input type="number" name="account_number" class="form-control" id="account-number" v-model="account.number" onkeydown="javascript: return event.keyCode == 69 ? false : true">
                                <p class="error-account text-center alert alert-danger d-none error-number"></p>
                            </div>
                        </div>
                        <div class="col-sm-6 account-type" v-if="chart === 'asset' || chart === 'liability'">
                            <div class="form-group">
                                <strong><label for="account_type">Type</label></strong>
                                <select name="account-group" id="account-group" class="form-control" v-model="account.account_type">
                                    <option value="">None</option>
                                    <option value="register">Use as a register</option>
                                    <option value="accounts_receivable">Accounts Receivable</option>
                                </select>
                            </div>  
                        </div>
                        <div class="col-sm-6 account-funds" v-if="chart !== 'asset' && chart !== 'liability'">
                            <div class="form-group">
                                <strong><label for="fund">Fund</label></strong>
                                <select name="fund" id="fund" class="form-control" v-model="account.account_fund_id">
                                    <option v-for="fund in funds" :key="fund.id" :value="fund.id">{{ fund.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 account-activity" v-if="chart === 'asset' || chart === 'liability'">
                            <div class="form-group">
                                <strong><label for="activity">Activity</label></strong>
                                <select name="activity" id="activity" class="form-control" v-model="account.activity">
                                    <option value="cash">Cash</option>
                                    <option value="operating">Operating</option>
                                    <option value="investing">Investing</option>
                                    <option value="financing">Financing</option>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="w-100"> </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="row">
                                    <label class="ml-3"><strong>Status</strong></label>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label><input type="radio" name="status" id="status" value="1" checked="checked"> Enable</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <label><input type="radio" name="status" id="status" value="0"> Disable</label>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <input type="button" @click.prevent="createAccounts" class="btn btn-success" value="Submit" :disabled="saveDisabled">
                <input type="button" class="btn btn-default" data-dismiss="modal" value="Close">
            </div>
        </div>
    </div>
</div>
</template>
<style>
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
    mounted() {
        // console.log('Mounted AddAccount')
    },
    props: ['group', 'groups', 'user', 'chart', 'funds', 'sub'],

    data () {
        return {
            processingPost: false,
            account: {
                name: '',
                account_group_id: '',
                number: '',
                sub_account: false,
                parent_account_id: '',
                account_type: '',
                activity: '',
                account_fund_id: ''
            }
        }
    },
    
    methods: {
        subAccountToggle () {
            this.account.sub_account = !this.account.sub_account;
        },
        createAccounts () {
            this.setGroupId 
            // console.log(this.account)
            this.processingPost  = true
            axios.post('/accounting/accounts', {account: this.account, user: this.user})
                .then((res) => {
                    // console.log(res.data)
                    this.flash("You have successfully created new account " + this.account.number + " " + this.account.name , 'success', {
                        timeout: 7000,
                        important: true
                    });
                    this.$emit('addAccount'),
                    $('#add-account-modal').modal('toggle');
                    this.groups.unshift(res.data)
                })
                .catch((err)=> {
                    console.log(err.response.status)

                    var errormessage = '';
                    if (err.response.status == 403) {
                        errormessage = 'Insufficient permissions to create accounts';
                    } else if (err.response.status == 422) {
                        for (var key in err.response.data) {
                            if (err.response.data.hasOwnProperty(key)) {
                                errormessage = errormessage ? errormessage + "<br>":'';
                                errormessage += err.response.data[key];
                            }
                        }
                    } else if (typeof err.response.data == 'string') {
                        errormessage = err.response.data;
                    } else {
                        errormessage = 'An error occurred';
                    }
                    this.flash(errormessage, 'error', {
                        timeout: 7000,
                        important: true
                    });
                    this.processingPost  = false
                })
        }
    },
    computed: {
        setGroupId: {
            get: function() {
                this.account.account_group_id = this.group
            }
        },
        saveDisabled() {
            return this.processingPost 
            || (this.account.sub_account && !this.account.parent_account_id)
            || !this.account.name
        },
        
    },
    mounted() {
        let that = this
        $('#add-account-modal').on('hidden.coreui.modal', function (e) {
            that.processingPost  = false
            
            that.account.name = '',
            that.account.number = '',
            that.parent_account_id = '',
            that.account.sub_account = false,
            that.account.account_type = '',
            that.account.activity = ''
        })
    }
}
</script>
