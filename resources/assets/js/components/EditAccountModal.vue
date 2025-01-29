<template>
<div id="edit-account-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Account</h4>
                <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form role="form">
                    <div class="row">
                        <flash-message class="flash-message-custom"></flash-message>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <strong><label for="account_name">Name</label></strong>
                                <input type="text" name="account_name" class="form-control" required='required' id='account-name' v-model="name">
                                <p class="error-account text-center alert alert-danger d-none error-name"></p>
                            </div>
                        </div>
                        <div class="col-sm-6 all-groups">
                            <div class="form-group">
                                <strong><label for="groups">Group</label></strong>
                                <select name="account_group_id" id="groups" class="form-control" v-model="accountGroupId">
                                    <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6" v-if="!isParentAccount">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <strong><label>Sub-Account</label> </strong>
                                    </div>
                                </div>
                                <div class="row" v-if="accountGroupHasChanged">
                                    <div class="col-sm-12 alert-warning alert">
                                        The group has been changed. If needed, click Submit and then edit this account againt to update the sub-account information
                                    </div>    
                                </div>
                                <div class="row" v-if="!accountGroupHasChanged">
                                    <div class="col-sm-12">
                                        <label><input type="checkbox" id="sub-account-check" v-model="subAccount">Make this account a sub-account</label>
                                    </div>    
                                </div>
                                <div class="row sub-account-row" v-if="!accountGroupHasChanged && subAccount">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <strong><label for="sub-account">Sub Account</label></strong>
                                            <select name="sub-account" id="sub-account" v-model="parentAccountId" class="form-control" required>
                                                <option value=""></option>
                                                <option v-if="subaccounts && s.id != account.id" v-for="s in subaccounts" :key="s.id" v-bind:value="s.id">{{ s.name }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <strong><label for="account_number">Number</label></strong>
                                <input type="text" name="account_number" class="form-control" id="account-number" v-model="number">
                                <p class="error-account text-center alert alert-danger d-none error-number"></p>
                            </div>
                        </div>
                        <div class="col-sm-6 account-type" v-if="chart === 'asset' || chart === 'liability'">
                            <div class="form-group">
                                <strong><label for="account_type">Type</label></strong>
                                <select name="account-group" id="account-group" class="form-control" v-model="accountType">
                                    <option value="">None</option>
                                    <option value="register">Use as a register</option>
                                    <option value="accounts_receivable">Accounts Receivable</option>
                                </select>
                            </div>  
                        </div>
                        <div class="col-sm-6 account-funds" v-if="chart !== 'asset' && chart !== 'liability'">
                            <div class="form-group">
                                <strong><label for="fund">Fund</label></strong>
                                <select name="fund" id="fund" class="form-control" v-model="fundId">
                                    <option v-for="fund in funds" :key="fund.id" :value="fund.id">{{ fund.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 account-activity" v-if="chart === 'asset' || chart === 'liability'">
                            <div class="form-group">
                                <strong><label for="activity">Activity</label></strong>
                                <select name="activity" id="activity" class="form-control" v-model="activity">
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
                                        <label><input type="radio" name="status" id="status" value="1" v-model="status"> Enable</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <label><input type="radio" name="status" id="status" value="0" v-model="status"> Disable</label>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <input type="button" @click.prevent="editAccount" class="btn btn-success" value="Submit" :disabled="submitDisabled">
                <input type="button" class="btn btn-default" data-dismiss="modal" value="Close">
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
    mounted() {
        // console.log('Mounted Edit Account')
    },
    props: ['account', 'chart', 'group', 'user', 'funds', 'groups', 'subaccounts'],

    data () {
        return {
            acc: {
                account_fund_id: '',
                account_group_id: '',
                account_type: '',
                activity: '',
                name: '',
                number: '',
                parent_account_id: '',
                status: '',
                sub_account: '',
            }
        }
    },
    
    methods: {
        editAccount () {
            axios.patch('/accounting/accounts/' + this.account.id, {account: this.acc, user: this.user})
                 .then((res) => {
                    this.flash("You have successfully updated account " + this.account.name , 'success', {
                        timeout: 5000,
                        important: true
                    });
                    this.$emit('accountEdited')
                    $('#edit-account-modal').modal('toggle');
                 })
                 .catch((err) => {
                     console.log(err)

                     this.flash(err.response.data, 'error', {
                         timeout: 5000,
                         important: true
                     });
                 })
        }
    },
    computed: {
        name: {
            get: function() {
                return this.acc.name === '' ? this.account.name
                : this.acc.name
            },
            set: function(newVal) {
                this.acc.name = newVal
            }
        },
        accountGroupId: {
            get: function() {
                return this.acc.account_group_id === '' && this.account.account_group_id
                || this.acc.account_group_id
            },
            set: function(newVal) {
                if (newVal == this.account.account_group_id) {
                    this.parentAccountId = this.account.parent_account_id
                }
                else {
                    this.subAccount = false
                }
                this.acc.account_group_id = newVal
            }
        },
        accountGroupHasChanged() {
            return this.acc.account_group_id !== '' 
            && this.account.account_group_id != this.acc.account_group_id
        },
        number: {
            get: function() {
                return this.acc.number === '' ? this.account.number
                : this.acc.number
            },
            set: function(newVal) {
                this.acc.number = newVal
            }
        },
        subAccount: {
            get: function() {
                return this.acc.sub_account === '' ? this.account.sub_account // initial value ...
                : this.acc.sub_account // ... or if set, the set value
            },
            set: function(newVal) {
                if (!newVal) {
                    this.parentAccountId = null
                    this.acc.sub_account = false
                }
                 else {
                     this.acc.sub_account = true
                 }
            }
        },
        parentAccountId: {
            get: function() {
                return this.acc.parent_account_id === '' ? this.account.parent_account_id
                : this.acc.parent_account_id
            },
            set: function(newVal) {
                this.acc.sub_account = true
                this.acc.parent_account_id = newVal ? newVal : null
            }
        },
        activity: {
            get: function() {
                return this.acc.activity === '' ? this.account.activity
                : this.acc.activity
            },
            set: function(newVal) {
                this.acc.activity = newVal
            }
        },
        accountType: {
            get: function() {
                return this.acc.account_type === '' ? this.account.account_type
                : this.acc.account_type
            },
            set: function(newVal) {
                this.acc.account_type = newVal
            }
        },
        fundId: {
            get: function() {
              if (this.account.account_fund_id === null) return 0;
                return this.acc.account_fund_id === '' ? this.account.account_fund_id
                : this.acc.account_fund_id
            },
            set: function(newVal) {
                this.acc.account_fund_id = newVal
            }
        },
        status: {
            get: function() {
                return this.acc.status === '' ? this.account.status 
                : this.acc.status
            },
            set: function(newVal) {
                this.acc.status = newVal
            }
        },
        submitDisabled() {
            // console.log('pai', this.parentAccountId, this.account.sub_account, this.acc.sub_account)
            return this.subAccount && !this.parentAccountId ? true : false
        },
        isParentAccount() {
            return this.account.sub_list && this.account.sub_list.length > 0
        }
    },
    mounted() {
        let that = this
        $('#edit-account-modal').on('hidden.coreui.modal', function (e) {
            
            that.acc.account_fund_id = ''
            that.acc.account_group_id = ''
            that.acc.account_type = ''
            that.acc.activity = ''
            that.acc.name = ''
            that.acc.number = ''
            that.acc.parent_account_id = ''
            that.acc.status = ''
            that.acc.sub_account = ''
        })
    }
}
</script>
