<template>
    <div class="row">
        <flash-message class="flash-message-custom"></flash-message>
        <div class="col-md-5">
            <div v-if="permissions['accounting-create']" class="card">
                <div class="card-header">Create New Fund</div>
                <div class="card-body">
                    <label for="fund-name">Name:</label>
                    <input type="text" name="fund-name" id="fund-name" class='form-control mb-2' v-model="fund.name">

                    <label for="color-picker">Color:</label>
                    <div class="input-group color-picker-component mb-2" @blur="displayPicker = false">
                        <input type="text"
                            id="color-picker"
                            class="form-control"
                            title="Color Picker"
                            :value="colors.hex"
                            @focus="showPicker"
                        />
                        <span class="input-group-addon color-picker-container">
                            <span class="current-color"
                                :style="'background-color: ' + colors.hex"
                                @click="showPicker"
                            ></span>
                                <chrome-picker :value="colors" v-model="fund.color"
                                    @input="updateValue"
                                    v-if="displayPicker"></chrome-picker>
                        </span>
                    </div>

                    <input type="button" value="Save Fund" class="btn btn-primary" @click.prevent="createFund" :disabled="saveDisabled">
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
        <div class="col-md-5 offset-md-2">
            <div class="card">
                <div class="card-header clickable" data-toggle='collapse' data-target='#funds-block'>
                    Funds
                    <i class="fa fa-caret-down"></i>
                    <i class="fa fa-caret-left"></i>
                </div>
                <div class="card-body collapse show" id="funds-block">
                    <ul class="list-group">
                        <li class="list-group-item" v-for="fund in filteredFunds" :key="fund.id">
                            {{ fund.name }}
                            <fund-buttons :fund="fund" :del_callback="!permissions['accounting-delete'] ? null : showModal" :edit_callback="!permissions['accounting-update'] ? null : editFundModal"></fund-buttons>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card">
              <div class="card-header">&nbsp;</div>
                <div class="card-body">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="" class="col-form-label">Sort Accounts By</label>
                      <vue-multiselect :searchable="false" placeholder="Sort Account By" :options="sort_by_options" :multiple="true" v-model="sort_by" @input="fetchData"></vue-multiselect>
                    </div>
                  </div>
                    <div class="card asset mt-3">
                        <div class="card-header" data-toggle="" data-target="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 class="clickable d-inline" data-toggle="collapse" data-target="#assets-block">
                                        Assets 
                                        <i class="fa fa-caret-down"></i>
                                        <i class="fa fa-caret-left"></i>
                                    </h4>
                                    <button v-if="permissions['accounting-create']" class="btn btn-primary float-right mt-1 d-none d-sm-inline-block" @click="showCreateGroupModal('asset')"><i class="fa fa-plus"></i> Create New Group</button>
                                    <button class="btn btn-primary btn-sm float-right d-sm-none" @click="showCreateGroupModal('asset')"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body collapse show" id="assets-block">
                            <div class="card" v-for="(group, index) in asset_groups" :key="group.id">
                                <account-group-buttons :group="group" :add_acccount_callback="!permissions['accounting-create'] ? null : addAccountModal" :edit_callback="!permissions['accounting-update'] ? null : showEditModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-group-buttons>
                                <div class="card-body">
                                    <div class="card-body">
                                        <ul class="list-group list-group-root well">
                                            <li class="list-group-item" v-for="account in group.accounts" :key="account.id">
                                                <!-- <span><i class="fa fa-bars"></i></span> -->
                                                <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                                <span class="mx-md-3">{{ account.name }}</span>
                                                <span class="mx-md-3" v-if="account.fund">{{ account.fund.name }}</span>
                                                <account-buttons :account="account" :group="group" :groups="asset_groups" :edit_callback="!permissions['accounting-update'] ? null : editAccountModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-buttons>
                                                <ul class="list-group" v-if="account.sub_list">
                                                    <li class="list-group-item" v-for="acc in account.sub_list" :key="acc.id">
                                                        <!-- <span><i class="fa fa-bars"></i></span> -->
                                                        <span class="mr-md-5 mr-3">{{ acc.number }}</span>
                                                        <span class="mx-md-3">{{ acc.name }}</span>
                                                        <span class="mx-md-3" v-if="acc.fund">{{ acc.fund.name }}</span>
                                                        <account-buttons :account="acc" :group="group" :groups="asset_groups" :edit_callback="!permissions['accounting-update'] ? null : editAccountModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-buttons>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card liability mt-3">
                        <div class="card-header" data-toggle="" data-target="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 class="clickable d-inline" data-toggle="collapse" data-target="#liability-block">
                                        Liability 
                                        <i class="fa fa-caret-down"></i>
                                        <i class="fa fa-caret-left"></i>
                                    </h4>
                                    <button v-if="permissions['accounting-create']" class="btn btn-primary float-right mt-1 d-none d-sm-inline-block" @click="showCreateGroupModal('liability')"><i class="fa fa-plus"></i> Create New Group</button>
                                    <button class="btn btn-primary btn-sm float-right d-sm-none" @click="showCreateGroupModal('liability')"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <!-- <draggable :list="liability_groups" :options="{animation:200, group: 'liability-block'}" class="card-body collapse show" id="liability-block" @change="updateGroupsOrder(liability_groups)"> -->
                        <div class="card-body collapse show" id="liability-block">
                            <div class="card" v-for="(group, index) in liability_groups" :key="group.id">
                                <account-group-buttons :group="group" :add_acccount_callback="!permissions['accounting-create'] ? null : addAccountModal" :edit_callback="!permissions['accounting-update'] ? null : showEditModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-group-buttons>
                                <div class="card-body">
                                    <ul class="list-group list-group-root well">
                                        <li class="list-group-item" v-for="account in group.accounts" :key="account.id">
                                            <!-- <span><i class="fa fa-bars"></i></span> -->
                                            <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                            <span class="mx-md-3">{{ account.name }}</span>
                                            <span class="mx-md-3" v-if="account.fund">{{ account.fund.name }}</span>
                                            <account-buttons :account="account" :group="group" :groups="liability_groups" :edit_callback="!permissions['accounting-update'] ? null : editAccountModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-buttons>
                                            
                                            <ul class="list-group" v-if="account.sub_list">
                                                <li class="list-group-item" v-for="acc in account.sub_list" :key="acc.id">
                                                    <!-- <span><i class="fa fa-bars"></i></span> -->
                                                    <span class="mr-md-5 mr-3">{{ acc.number }}</span>
                                                    <span class="mx-md-3">{{ acc.name }}</span>
                                                    <span class="mx-md-3" v-if="acc.fund">{{ acc.fund.name }}</span>
                                                    <account-buttons :account="acc" :group="group" :groups="liability_groups" :edit_callback="!permissions['accounting-update'] ? null : editAccountModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-buttons>
                                                </li>
                                            </ul>
                                            
                                        </li>
                                    </ul>
                                    <!-- <draggable :list="group.accounts" :options="{animation:200, group:'liabilities'}" :element="'ul'" class="list-group dragArea" @change="updateAccounts(group.accounts, group.id)">
                                        <li class="list-group-item" v-for="account in group.accounts" :key="account.id" @click="log(account)">
                                            <span><i class="fa fa-bars"></i></span>
                                            <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                            <span class="mx-md-3">{{ account.name }}</span>
                                            <span class="float-right">
                                                <button @click="editAccountModal(account, group.chart_of_account, group.id)" class="btn btn-warning btn-sm mr-2"><i class="fa fa-edit"></i></button>
                                                <button v-if="permissions['accounting-delete']" v-if="permissions['accounting-delete']" @click="showModal(account.id, account.name, 'delete-account-modal')"  class="btn btn-danger btn-sm mr-2"><i class="fa fa-trash"></i></button>
                                            </span> 
                                        </li>
                                    </draggable> -->
                                </div>
                            </div>
                        <!-- </draggable> -->
                        </div>
                    </div>
                    <div class="card equity mt-3">
                        <div class="card-header" data-toggle="" data-target="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 class="clickable d-inline" data-toggle="collapse" data-target="#equity-block">
                                        Equity 
                                        <i class="fa fa-caret-down"></i>
                                        <i class="fa fa-caret-left"></i>
                                    </h4>
                                    <!-- <button v-if="permissions['accounting-create']" class="btn btn-primary float-right mt-1 d-none d-sm-inline-block" @click="showCreateGroupModal('equity')"><i class="fa fa-plus"></i> Create New Group</button> -->
                                    <!-- <button class="btn btn-primary btn-sm float-right d-sm-none" @click="showCreateGroupModal('equity')"><i class="fa fa-plus"></i></button> -->
                                </div>
                            </div>
                        </div>
                        <!-- <draggable :list="equity_groups" :options="{animation:200, group: 'equity-block'}" class="card-body collapse show" id="equity-block" @change="updateGroupsOrder(equity_groups)"> -->
                        <div class="card-body collapse show" id="equity-block">
                            <div class="card" v-for="(group, index) in equity_groups" :key="group.id">
                                <account-group-buttons :group="group" :edit_callback="!permissions['accounting-update'] ? null : showEditModal" ></account-group-buttons>
                                <!-- <accounts :group-id="group.id"></accounts> -->
                                <div class="card-body">
                                    <ul class="list-group list-group-root well">
                                        <li class="list-group-item" v-for="account in group.accounts" :key="account.id">
                                            <!-- <span><i class="fa fa-bars"></i></span> -->
                                            <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                            <span class="mx-md-3">{{ account.name }}</span>
                                            <span class="mx-md-3" v-if="account.fund">{{ account.fund.name }}</span>
                                            <fund-buttons :fund="account.fund" :del_callback="!permissions['accounting-delete'] ? null : showModal" :edit_callback="!permissions['accounting-update'] ? null : editFundModal"></fund-buttons>
                                            <ul class="list-group" v-if="account.sub_list">
                                                <li class="list-group-item" v-for="acc in account.sub_list" :key="acc.id">
                                                    <!-- <span><i class="fa fa-bars"></i></span> -->
                                                    <span class="mr-md-5 mr-3">{{ acc.number }}</span>
                                                    <span class="mx-md-3">{{ acc.name }}</span>
                                                    <span class="mx-md-3" v-if="acc.fund">{{ acc.fund.name }}</span>
                                                    <fund-buttons :fund="acc.fund" :del_callback="!permissions['accounting-delete'] ? null : showModal" :edit_callback="!permissions['accounting-update'] ? null : editFundModal"></fund-buttons>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                    <!-- <draggable :list="group.accounts" :options="{animation:200, group:'equity'}" :element="'ul'" class="list-group dragArea" @change="updateAccounts(group.accounts, group.id)">
                                        <li class="list-group-item" v-for="account in group.accounts" :key="account.id" @click="log(account)">
                                            <span><i class="fa fa-bars"></i></span>
                                            <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                            <span class="mx-md-3">{{ account.name }}</span>
                                            <span class="float-right">
                                                <button @click="editAccountModal(account, group.chart_of_account, group.id)" class="btn btn-warning btn-sm mr-2"><i class="fa fa-edit"></i></button>
                                                <button v-if="permissions['accounting-delete']" @click="showModal(account.id, account.name, 'delete-account-modal')"  class="btn btn-danger btn-sm mr-2"><i class="fa fa-trash"></i></button>
                                            </span>
                                        </li>
                                    </draggable> -->
                                </div>
                            </div>
                        <!-- </draggable> -->
                        </div>
                    </div>
                    <div class="card income mt-3">
                        <div class="card-header" data-toggle="" data-target="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 class="clickable d-inline" data-toggle="collapse" data-target="#income-block">
                                        Income 
                                        <i class="fa fa-caret-down"></i>
                                        <i class="fa fa-caret-left"></i>
                                    </h4>
                                    <button v-if="permissions['accounting-create']" class="btn btn-primary float-right mt-1 d-none d-sm-inline-block" @click="showCreateGroupModal('income')"><i class="fa fa-plus"></i> Create New Group</button>
                                    <button class="btn btn-primary btn-sm float-right d-sm-none" @click="showCreateGroupModal('income')"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <!-- <draggable :list="income_groups" :options="{animation:200, group: 'income-block'}" class="card-body collapse show" id="income-block" @change="updateGroupsOrder(income_groups)"> -->
                        <div id="income-block" class="card-body collapse show">
                            <div class="card" v-for="(group, index) in income_groups" :key="group.id">
                                <account-group-buttons :group="group" :add_acccount_callback="!permissions['accounting-create'] ? null : addAccountModal" :edit_callback="!permissions['accounting-update'] ? null : showEditModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-group-buttons>
                                <!-- <accounts :group-id="group.id"></accounts> -->
                                <div class="card-body">
                                    <ul class="list-group list-group-root well">
                                        <li class="list-group-item" v-for="account in group.accounts" :key="account.id">
                                            <!-- <span><i class="fa fa-bars"></i></span> -->
                                            <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                            <span class="mx-md-3">{{ account.name }}</span>
                                            <span class="mx-md-3" v-if="account.fund">{{ account.fund.name }}</span>
                                            <account-buttons :account="account" :group="group" :groups="income_groups" :edit_callback="!permissions['accounting-update'] ? null : editAccountModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-buttons>
                                            
                                            <ul class="list-group" v-if="account.sub_list">
                                                <li class="list-group-item" v-for="acc in account.sub_list" :key="acc.id">
                                                    <!-- <span><i class="fa fa-bars"></i></span> -->
                                                    <span class="mr-md-5 mr-3">{{ acc.number }}</span>
                                                    <span class="mx-md-3">{{ acc.name }}</span>
                                                    <span class="mx-md-3" v-if="acc.fund">{{ acc.fund.name }}</span>
                                                    <account-buttons :account="acc" :group="group" :groups="income_groups" :edit_callback="!permissions['accounting-update'] ? null : editAccountModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-buttons>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                    <!-- <draggable :list="group.accounts" :options="{animation:200, group:'income'}" :element="'ul'" class="list-group dragArea" @change="updateAccounts(group.accounts, group.id)">
                                        <li class="list-group-item" v-for="account in group.accounts" :key="account.id" @click="log(account)">
                                            <span><i class="fa fa-bars"></i></span>
                                            <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                            <span class="mx-md-3">{{ account.name }}</span>
                                            <span class="float-right">
                                                <button @click="editAccountModal(account, group.chart_of_account, group.id)" class="btn btn-warning btn-sm mr-2"><i class="fa fa-edit"></i></button>
                                                <button v-if="permissions['accounting-delete']" @click="showModal(account.id, account.name, 'delete-account-modal')"  class="btn btn-danger btn-sm mr-2"><i class="fa fa-trash"></i></button>
                                            </span>
                                        </li>
                                    </draggable> -->
                                </div>
                            </div>
                        </div>
                        <!-- </draggable> -->
                    </div>
                    <div class="card expense mt-3">
                        <div class="card-header" data-toggle="" data-target="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 class="clickable d-inline" data-toggle="collapse" data-target="#expense-block">
                                        Expenses 
                                        <i class="fa fa-caret-down"></i>
                                        <i class="fa fa-caret-left"></i>
                                    </h4>
                                    <button v-if="permissions['accounting-create']" class="btn btn-primary float-right mt-1 d-none d-sm-inline-block" @click="showCreateGroupModal('expense')"><i class="fa fa-plus"></i> Create New Group</button>
                                    <button class="btn btn-primary btn-sm float-right d-sm-none" @click="showCreateGroupModal('expense')"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <!-- <draggable :list="expense_groups" :options="{animation:200, group: 'expense-block'}" class="card-body collapse show" id="expense-block" @change="updateGroupsOrder(expense_groups)"> -->
                        <div class="card-body collapse show" id="expense-block">
                            <div class="card" v-for="(group, index) in expense_groups" :key="group.id">
                                <account-group-buttons :group="group" :add_acccount_callback="!permissions['accounting-create'] ? null : addAccountModal" :edit_callback="!permissions['accounting-update'] ? null : showEditModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-group-buttons>
                                <!-- <accounts :group-id="group.id"></accounts> -->
                                <div class="card-body">
                                    <ul class="list-group list-group-root well">
                                        <li class="list-group-item" v-for="account in group.accounts" :key="account.id">
                                            <!-- <span><i class="fa fa-bars"></i></span> -->
                                            <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                            <span class="mx-md-3">{{ account.name }}</span>
                                            <span class="mx-md-3" v-if="account.fund">{{ account.fund.name }}</span>
                                            <account-buttons :account="account" :group="group" :groups="expense_groups" :edit_callback="!permissions['accounting-update'] ? null : editAccountModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-buttons>
                                            
                                            <ul class="list-group" v-if="account.sub_list">
                                                <li class="list-group-item" v-for="acc in account.sub_list" :key="acc.id">
                                                    <!-- <span><i class="fa fa-bars"></i></span> -->
                                                    <span class="mr-md-5 mr-3">{{ acc.number }}</span>
                                                    <span class="mx-md-3">{{ acc.name }}</span>
                                                    <span class="mx-md-3" v-if="acc.fund">{{ acc.fund.name }}</span>
                                                    <account-buttons :account="acc" :group="group" :groups="expense_groups" :edit_callback="!permissions['accounting-update'] ? null : editAccountModal" :del_callback="!permissions['accounting-delete'] ? null : showModal" ></account-buttons>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                    <!-- <draggable :list="group.accounts" :options="{animation:200, group:'expense'}" :element="'ul'" class="list-group dragArea" @change="updateAccounts(group.accounts, group.id)">
                                        <li class="list-group-item" v-for="account in group.accounts" :key="account.id" @click="log(account)">
                                            <span><i class="fa fa-bars"></i></span>
                                            <span class="mr-md-5 mr-3">{{ account.number }}</span>
                                            <span class="mx-md-3">{{ account.name }}</span>
                                            <span class="float-right">
                                                <button @click="editAccountModal(account, group.chart_of_account, group.id)" class="btn btn-warning btn-sm mr-2"><i class="fa fa-edit"></i></button>
                                                <button v-if="permissions['accounting-delete']" @click="showModal(account.id, account.name, 'delete-account-modal')"  class="btn btn-danger btn-sm mr-2"><i class="fa fa-trash"></i></button>
                                            </span>
                                        </li>
                                    </draggable> -->
                                </div>
                            </div>
                        </div>
                        <!-- </draggable> -->
                    </div>
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
        <create-group-modal :user="currentUser" :maingroup="group_chart" @addGroup="fetchData"></create-group-modal>
        <delete-account-modal :id="account_id" :name="account_name" @accountRemoved="fetchData"></delete-account-modal>
        <delete-group-modal :group="group_id" :index="group_index" :groups="groups" @groupRemoved="fetchData"></delete-group-modal>
        <add-account-modal :sub="sub_acc_list" :funds="funds" :g="group" :group="group_id" :chart='group_chart' :groups="groups" :user="currentUser" @addAccount="fetchData"></add-account-modal>
        <edit-group-modal :name="group_name" :chart='group_chart' :id="group_id" :groups="g" :user="currentUser" @groupEdited="fetchData" ></edit-group-modal>
        <edit-fund-modal :fund="edit_fund" :user="currentUser" @fundEdited="fetchData"></edit-fund-modal>
        <edit-account-modal :subaccounts="sub_acc_list" :chart="group_chart" :funds="funds" :account="account" :group="group_id" :groups="current_groups" :user="currentUser" @accountEdited="fetchData"></edit-account-modal>
    </div>
</template>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
            

    .list-group.list-group-root .list-group {
        margin-bottom: 0;
        margin-top: 1rem;
    }
    .list-group.list-group-root .list-group-item {
        border-radius: 0;
        border-width: 1px 0 0 0;
    }
    .list-group.list-group-root .list-group li {
        border: none;
        padding: .5rem 0 0 2rem;
    }

    .color-picker-component .current-color {
            display: inline-block;
            width: 16px;
            height: 16px;
            background-color: #000;
            cursor: pointer;
        }

        .color-picker-component .vc-chrome {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            z-index: 100;
        }
        .dragArea {
            min-height: 10px;
        }
        .flash-message-custom {
            width: 100%;
            padding: 0 15px;
        }
        .multiselect__spinner:after,.multiselect__spinner:before {
          border-color: var(--primary) transparent transparent;
        }
        .multiselect__tag {
          background: var(--primary);
        }

        .multiselect__option--highlight {
          background: var(--primary);
        }

        .multiselect__option--highlight:after {
          background: var(--primary);
        }
</style>

<script>
    import createGroupModal from './CreateGroupModal'
    import deleteGroupModal from './DeleteGroupModal'
    import addAccountModal from './AddAccountModal'
    import deleteAccountModal from './DeleteAccountModal'
    import editGroupModal from './EditGroupModal'
    import editFundModal from './EditFundModal'
    import editAccountModal from './EditAccountModal'
    import draggable from 'vuedraggable'
    import {Chrome} from 'vue-color'
    import VueMultiselect from 'vue-multiselect';
    import AccountButtons from './accounting/accounts/AccountButtons'
    import FundButtons from './accounting/accounts/FundButtons'
    import AccountGroupButtons from './accounting/accounts/AccountGroupButtons'
    
    let defaultProps = {
        hex: '#FFFFFF',
        hsl: {
            h: 150,
            s: 0.5,
            l: 0.2,
            a: 0.9
        },
        hsv: {
            h: 150,
            s: 0.66,
            v: 0.30,
            a: 0.9
        },
        rgba: {
            r: 25,
            g: 77,
            b: 51,
            a: 0.9
        },
        a: 0.9
    }
    export default {
        components: {
            draggable,
            'chrome-picker': Chrome,
            'create-group-modal': createGroupModal,
            'delete-group-modal': deleteGroupModal,
            'add-account-modal': addAccountModal,
            'delete-account-modal': deleteAccountModal,
            'edit-group-modal': editGroupModal,
            'edit-account-modal': editAccountModal,
            editFundModal,
            VueMultiselect,
            AccountButtons,
            FundButtons,
            AccountGroupButtons,
        },
        mounted () {
            this.fetchData()
        },
        data () {
            return {
                g: [],
                funds: [],
                groups: [],
                asset_groups:[],
                liability_groups:[],
                equity_groups:[],
                income_groups:[],
                expense_groups:[],
                current_groups:[],
                accounts: [],
                accounts_sorted: [],
                sub_acc_list: [],
                group: {
                    name: '', 
                    chart_of_account: ''
                },
                group_id: '',
                group_index: '',
                group_name: '',
                group_chart: '',
                account_id: '',
                account_name: '',
                account: '',
                fund: {
                    name: '',
                    color: defaultProps.hex
                },
                edit_fund: {},
                displayPicker: false,
                colors: defaultProps,
                processingPost: false,
              sort_by_options:[
                  'name',
                  'number'
              ],
              sort_by:[
                'number',
                'name'
              ]
            }
        },
        props:  ['currentUser','permissions'],

        methods: {
            updateGroupsOrder(data) {
                data.map((group, index) => {
                    group.order = index + 1;
                })
                axios.post('/accounting/accountgroups/sort/order', {account: data, user: this.currentUser})
                    .then((res) => {
                        // console.log(res.data)
                    })
                    .catch((err) => {
                        console.log(err)
                    })
            },
            updateAccounts(data, group_id, account_id = null) {
                console.log(data);
                data.map((account, index) => {
                    account.order = index + 1;
                    account.account_group_id = group_id;
                })
                axios.post('/accounting/accounts/sortorder', {account: data, user: this.currentUser})
                    .then((res) => {
                        // console.log(res.data)
                    })
                    .catch((err) => {
                        console.log(err)
                    })
            },
            createFund() {
                axios.post('/accounting/funds', {fund: this.fund, user: this.currentUser})
                     .then((res) => {
                        this.fetchData()
                        // console.log(res.data.group);
                        this.flash('We have automatically added new account in Equity inside<br>Group: ' + res.data.group + '<br>Name: ' + res.data.account.name + '<br>Number: ' + res.data.account.number, 'success', {
                            timeout: 5000,
                            important: true
                        });
                        this.fund.name = ''
                        this.fund.color = '#FFFFFF'
                     })
                     .catch((err)=> {
                        console.log(err)
                        var errormessage;
                        if (err.response.status == 403) errormessage = 'Insufficient permissions to edit a new group';
                        else errormessage = 'An error occurred';
                        
                        this.flash(errormessage, 'error', {
                            timeout: 7000,
                            important: true
                        });
                     })

            },
            showPicker() {
                this.displayPicker = !this.displayPicker
            },
            onOk() {
                console.log('ok')
            },
            onCancel() {
                console.log('cancel')
            },
            updateValue (value) {
                this.fund.color = value.hex
                this.colors = value
            },
            showCreateGroupModal(mg) {
                this.group_chart = mg
                $('#create-group-modal').modal('show');
            },
            showEditModal: function(name, chart, id) {
                this.group_name = name;
                this.group_chart = chart;
                this.group_id = id
                $('#edit-group-modal').modal('show');
            },
            showModal: function(id, index, modal) {
                if (modal == 'delete-group-modal') {
                    this.group_id = id
                    this.group_index = index
                } else if (modal == 'delete-account-modal') {
                    this.account_id = id,
                    this.account_name = index
                }
                $('#'+modal).modal('show');
            },
            hide () {
                this.$modal.hide('test')
            },
            editFundModal (fund) {
              fund.account_number = this.accounts.find(account => account.account_fund_id == fund.id).number
              this.edit_fund = fund
              $("#edit-fund-modal").modal('show')
            },
            addAccountModal (id, main_group, sub_acc_list) {
                this.group_id = id
                this.group_chart = main_group
                this.sub_acc_list = sub_acc_list
                $("#add-account-modal").modal('show');
            },
            editAccountModal (account, main_group, id, groups, subaccounts) {
                this.account = account
                this.group_chart = main_group
                this.group_id = id
                this.g = groups
                this.current_groups = groups
                this.sub_acc_list = subaccounts
                $("#edit-account-modal").modal('show')
            },
            fetchData () {
                this.processingPost = true
                axios.get('/accounting/accountgroups', {
                  params: {
                    sort_by: this.sort_by
                  }
                }).then((res) => {
                         this.processingPost = false
                        this.groups = res.data.groups
                        var asset_groups = []
                        var liability_groups = []
                        var equity_groups = []
                        var income_groups = []
                        var expense_groups = []

                        $.each(this.groups, function(index, val) {
                            if(val.chart_of_account == 'asset') {
                                asset_groups.push(val);
                            } else if (val.chart_of_account == 'liability') {
                                liability_groups.push(val);
                            } else if (val.chart_of_account == 'equity') {
                                equity_groups.push(val);
                            } else if (val.chart_of_account == 'income') {
                                income_groups.push(val);
                            } else if (val.chart_of_account == 'expense') {
                                expense_groups.push(val);
                            }
                        })
                        this.asset_groups = asset_groups
                        this.liability_groups = liability_groups
                        this.equity_groups = equity_groups
                        this.income_groups = income_groups
                        this.expense_groups = expense_groups
                        this.funds = res.data.funds
                        this.accounts = res.data.accounts
                        // console.log(res.data);
                     })
                     .catch((err)=> {
                         this.processingPost = false
                        console.log(err)
                     })
            }
        },
        computed: {
            bgc () {
                return this.colors.hex
            },
            saveDisabled() {
                return this.processingPost || !this.fund.name
            },
          filteredFunds() {
            return this.funds.filter(fund => fund.id > 0);
          }
        },
    }
</script>
<style scoped>
    .clickable{
        cursor: pointer;
    }

    .clickable.collapsed>i.fa-caret-down, .clickable>i.fa-caret-left{
        display: none;
    }
    .clickable.collapsed>i.fa-caret-left{
        display: initial;
    }
    
    #assets-block .list-group-item > span,
    #liability-block .list-group-item > span,
    #equity-block .list-group-item > span,
    #income-block .list-group-item > span,
    #expense-block .list-group-item > span {
        min-width: 5ch;
        display: inline-block;
    }

</style>
