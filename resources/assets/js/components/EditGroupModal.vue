<template>
    <div id="edit-group-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Group</h4>
                    <button class="close" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <flash-message class="flash-message-custom"></flash-message>
                    <form>
                        <label for="name">Group Name:</label>
                        <input type="text" class="form-control mb-2" v-model="setName" placeholder="Group Name" id="group-name">

                        <!-- <label for="accountlist">Chart of Account:</label>
                        <select required name="accountlist" id="acc-dropdown mb-2" v-model="setChart" class="form-control">
                            <option value="asset">Asset</option>
                            <option value="liability" selected="selected">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select> -->
                    </form>
                </div>
                <div class="modal-footer">
                    <input type="button" @click="editGroup" class="btn btn-primary" value="Edit" :disabled="saveDisabled">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Close">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['name', 'chart', 'id', 'user'], 
    data() {
        return {
            g: {
                name : '',
                chart_of_account : '',
            },
        }
    },

    methods: {
        editGroup() {
            axios.patch('/accounting/accountgroups/' + this.id, {name: this.g.name, chart: this.g.chart_of_account, user: this.user})
                 .then((res) => {
                    //  console.log(res.data)
                    this.flash("You have successfully updated group " + this.name , 'success', {
                        timeout: 5000,
                        important: true
                    });
                     this.$emit('groupEdited')
                     $('#edit-group-modal').modal('toggle');
                 })
                 .catch((err) => {
                     console.log(err)
                     var errormessage;
                     if (err.response.status == 403) errormessage = 'Insufficient permissions to edit a new group';
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
        setName: {
            get: function() {
                return this.g.name === '' ? this.name : this.g.name
            },
            set: function(newVal) {
                this.g.name = newVal
            }
        },
        setChart: {
            get: function() {
                return this.g.chart_of_account === '' ? his.chart
                : this.g.chart_of_account
            },
            set: function(newVal) {
                this.g.chart_of_account = newVal
            }
        },
        saveDisabled() {
            return !this.g.name && !this.name
        }
    },
    mounted() {
        let that = this
        $('#edit-group-modal').on('hidden.coreui.modal', function (e) {
            that.g.chart_of_account = ''
            that.g.name = ''
        })
    },
}
</script>
