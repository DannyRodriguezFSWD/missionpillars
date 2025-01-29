<template>
    <div id="delete-group-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete {{ group.name }}</h4>
                    <button class="close" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="deletegroup">
                        Are you sure you want to delete <strong>{{ group.name }}</strong> group?
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-secondary" data-dismiss="modal" value="Close">
                    <input type="button" @click.prevent="deleteGroup" class="btn btn-danger" data-dismiss="modal" value="Delete">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['group', 'index', 'groups'],
    
    methods: {
        deleteGroup() {
            axios.delete('/accounting/accountgroups/' + this.group.id)
                 .then((res) => {
                    this.flash("You have successfully removed group " + this.name + " and it's accounts" , 'success', {
                        timeout: 5000,
                        important: true
                    });
                    this.$emit('groupRemoved')
                 })
                 .catch((err) => {
                     console.log(err)
                 })
        },
    }
}
</script>
