<template>
    <div id="edit-fund-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Fund</h4>
                    <button class="close" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <flash-message class="flash-message-custom"></flash-message>
                  <form>
                    <label for="fund-name">Fund Name:</label>
                    <input type="text" class="form-control mb-2" v-model="name" placeholder="Fund Name"
                           id="fund-name">
                    <label for="account_number">Account Number:</label>
                    <input type="text" class="form-control mb-2" v-model="account_number" placeholder="Account Number"
                           id="account_number">
                  </form>
                </div>
                <div class="modal-footer">
                    <input type="button" @click="editFund" class="btn btn-primary" value="Edit" :disabled="saveDisabled">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Close">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
  props: ['fund'],
  data() {
    return {
      name: '',
      account_number: '',
    }
  },

  methods: {
    editFund() {
      axios.put('/accounting/funds/' + this.fund.id, {name: this.name, account_number: this.account_number})
          .then((res) => {
            //  console.log(res.data)
            this.flash("You have successfully updated the " + this.name + " fund", 'success', {
              timeout: 7000,
              important: true
            });
            this.$emit('fundEdited')
            $('#edit-fund-modal').modal('toggle');
          })
          .catch((err) => {
            var errormessage;
            if (err.response.status == 403) errormessage = 'Insufficient permissions to edit a new fund';
            if (err.response.status == 409) errormessage = err.response.data
            else errormessage = 'An error occurred';

            this.flash(errormessage, 'error', {
              timeout: 7000,
              important: true
            });
          })
    }
  },
  watch: {
    fund: function (val) {
      this.name = val.name
      this.account_number = val.account_number
    },
  },
  computed: {
    saveDisabled() {
      return this.fund.name == this.name && this.fund.account_number == this.account_number
    }
  }
}
</script>
