// thanks https://github.com/ratiw/vuetable-2-tutorial/wiki/lesson-11#__componentname
<template>
    <div class="custom-actions">
        <button class="btn btn-primary btn-sm" @click="view(rowData)">Link</button>
        <button v-if="canUpdate" class="btn btn-sm btn-primary" @click="itemAction('hide-item', rowData, rowIndex)">Hide</button>
    </div>
</template>

<script>
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
    methods: {
        async itemAction (action, data, index) {
          let res = await Swal.fire({
            title: 'Are you sure',
            text: "Are you sure you want to hide this bank transaction?",
            type: 'question',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: async (result) => {
              await this.hideRow(data, index);
            }
          })
        },
        view(data){
          this.$parent.$emit('on-view-row',data);
        },
        async hideRow(data, index){
            await axios.put("/accounting/ajax/bank-transactions/"+data.id, { hidden: true })
            .then(response => {
                this.$parent.$parent.$refs.vuetable.refresh()
                // this.$refs.vuetable.refresh()
                // console.log (response)
            })
            .catch(error => { console.log ('error', error) })
        }
    },
    computed: {
        canUpdate() {
            return $('#permissions').data('permissions')['accounting-update']
        }
    }
}
</script>

<style>
</style>
