<template>
    <span class="float-sm-right">
        <button v-if="edit_callback" @click="edit_callback(account, group.chart_of_account, group.id, groups, group.accounts)" class="btn btn-warning btn-sm mr-2"><i class="fa fa-edit"></i></button>
        <button v-if="del_callback" @click="del_callback(account.id, account.name, 'delete-account-modal')"  class="btn btn-danger btn-sm mr-2"><i class="fa fa-trash"></i></button>
    </span>
</template>

<script>
export default {
    props: {
        account: Object,
        group: Object,
        groups: Array,
        edit_callback: Function,
        del_callback: Function,
    },
}
</script>

<style lang="css">
</style>
