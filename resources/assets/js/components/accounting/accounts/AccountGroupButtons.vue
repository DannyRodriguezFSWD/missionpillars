<template>
    <div class="component__account_group_buttons">
        <div class="card-header">
            <i class="fa fa-bars"></i><span>{{ group.name }}</span>
            <button v-if="del_callback" @click="del_callback(group, index, 'delete-group-modal')"  class="btn btn-danger delete-group float-right d-none d-md-inline-block"><i class="fa fa-trash"></i> Delete Group</button>
            <button v-if="edit_callback" @click="edit_callback(group.name, group.chart_of_account, group.id)" class="btn btn-warning edit-group float-right mr-1 d-none d-md-inline-block"><i class="fa fa-edit"></i> Edit Group</button>
            <button v-if="add_acccount_callback" @click="add_acccount_callback(group.id, group.chart_of_account, group.accounts)" class="btn btn-primary add-account-group float-right mr-1 d-none d-md-inline-block"><i class="fa fa-plus"></i> Create Account</button>
            <button v-if="del_callback" @click="del_callback(group, index, 'delete-group-modal')"  class="btn btn-danger delete-group btn-sm float-right d-md-none"><i class="fa fa-trash"></i></button>
            <button v-if="edit_callback" @click="edit_callback(group.name, group.chart_of_account, group.id)" class="btn btn-warning btn-sm edit-group float-right mr-1 d-md-none"><i class="fa fa-edit"></i></button>
            <button v-if="add_acccount_callback" @click="add_acccount_callback(group.id, group.chart_of_account, group.accounts)" class="btn btn-primary btn-sm add-account-group float-right mr-1 d-md-none"><i class="fa fa-plus"></i></button>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        group: Object,
        load_modals: {
            type: Boolean,
            default: true, // TODO make the default false and add the components to the page (see Groups.vue)
        },
        
        add_acccount_callback: {
            type: Function,
            default: null,
        },
        edit_callback: {
            type: Function,
            default: null,
        },
        del_callback: {
            type: Function,
            default: null,
        },
    },
}
</script>

<style lang="css">
</style>
