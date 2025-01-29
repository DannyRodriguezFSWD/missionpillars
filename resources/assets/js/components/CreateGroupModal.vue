<template>
<div id="create-group-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Group</h4>
                <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form role="form">
                    <flash-message class="flash-message-custom"></flash-message>
                    <div class="form-group">
                        <label for="group-name">Group Name</label>
                        <input type="text" class="form-control mb-2" v-model="group.name" placeholder="Group Name" id="group-name1">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <input type="button" class="btn btn-success" value="Submit" @click.prevent="create()" :disabled="saveDisabled">
                <input type="button" class="btn btn-default" data-dismiss="modal" value="Close">
            </div>
        </div>
    </div>
</div>
</template>
<script>
export default {
    props:['user', 'maingroup'],
    data () {
        return {
            group: {
                name: '',
                chart_of_account: ''
            },
        }
    },
    methods: {
        create: function() {
            this.setMainGroup
            axios.post('/accounting/accountgroups', {group: this.group, user: this.user})
                .then((res) => {
                    this.$emit('addGroup')
                    this.flash('You have successfully created group: ' + this.group.name + ' as ' + this.group.chart_of_account, 'success', {
                        timeout: 7000,
                        important: true
                    });
                    $('#create-group-modal').modal('toggle');
                })
                .catch((err)=> {
                    console.log(err)
                    var errormessage;
                    if (err.response.status == 403) errormessage = 'Insufficient permissions to create a new group';
                    else if (err.response.status == 422) {
                        for (var key in err.response.data) {
                            if (err.response.data.hasOwnProperty(key)) {
                                errormessage = errormessage ? errormessage + "<br>":'';
                                errormessage += err.response.data[key];
                            }
                        }
                    } else errormessage = 'An error occurred';
                    this.flash(errormessage, 'error', {
                        timeout: 7000,
                        important: true
                    });
                })
        }
    },
    computed: {
        setMainGroup: {
            get: function() {
                this.group.chart_of_account = this.maingroup
            }
        },
        saveDisabled() {
            return !this.group.name
        }
    },
    mounted() {
        let that = this
        $('#create-group-modal').on('hidden.coreui.modal', function (e) {
            that.group.name = '',
            that.group.chart_of_account = ''
        })
    },
}
</script>
