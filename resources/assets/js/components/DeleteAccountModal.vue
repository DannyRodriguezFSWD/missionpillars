<template>
    <div id="delete-account-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete {{ name }}</h4>
                    <button class="close" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="deletegroup">
                        Are you sure you want to delete <strong>{{ name }}</strong>?
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-secondary" data-dismiss="modal" value="Close">
                    <input type="button" @click.prevent="deleteAccount" class="btn btn-danger" data-dismiss="modal" value="Delete">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['id', 'name'],
    
    methods: {
        deleteAccount() {
            axios.delete('/accounting/accounts/' + this.id)
                 .then((res) => {
                    this.flash("You have successfully removed account " + this.name , 'success', {
                        timeout: 5000,
                        important: true
                    });
                    this.$emit('accountRemoved', true)
                 })
                 .catch((err) => {
                     console.log(err)
                 })
        },
    }
}
</script>
