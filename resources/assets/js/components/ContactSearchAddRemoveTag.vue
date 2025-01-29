<template>
    <div class="modal fade" tabindex="-1" id="addRemoveTagsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Add / Remove Tags</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="fetching" class="text-center">
                        <i class="fa fa-gear fa-spin fa-4x"></i>
                    </div>
                    <div v-else>
                        <div class="row mb-4">
                            <div class="col-12">
                                <label>Select if you want to add or remove tags</label>
                                <select class="form-control" placeholder="Select Action" v-model="action">
                                    <option class="text-muted" value="">Select Action</option>
                                    <option value="add">Add Tags</option>
                                    <option value="remove">Remove Tags</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <TagsMultiSelect :multiple="true" :options="folders" :show-labels="false" v-model="associated_tags" 
                                                 @tag="confirmNewTag" placeholder="Select Tags">
                                </TagsMultiSelect>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="updateTags">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import TagsMultiSelect from "./mp/tags-multiselect";

export default {
  name: "ContactSearchRemoveTag",
  props: ['folders'],
  components: {
    TagsMultiSelect
  },
  data() {
    return {
      associated_tag_ids: [],
      associated_tags: [],
      contact_ids: [],
      fetching: false,
      action: ''
    }
  },
  mounted() {
    let that = this;
    window.LaravelDataTables['dataTableBuilder'].on('buttons-action', function (e, buttonApi, dataTable, node, config) {
      if (buttonApi.text().includes('Remove Tags')) that.openRemoveTagModal();
    });
  },
  methods: {
    async openRemoveTagModal() {
      $("#addRemoveTagsModal").modal('show')
    },
    /**
     * Handles creating new tag in multiselect component
     * @param  {[string]} tag 
     */
    confirmNewTag(tag)  {
        this.$store.dispatch('newTag', { tag, includeFolder: true, addFunction: this.addNewTag }) 
    },
    /**
     * add new tag from DB 
     * @param  {[object]} tag 
     */
    addNewTag(db_tag) {
        let tag = {
            id: db_tag.id, name: db_tag.name, 
            folder: {id: db_tag.folder_id, name: db_tag.folder.name }
        }
        
        this.associated_tags.push(tag)
        this.associated_tag_ids = this.associated_tags.map(tag => tag.id)
    },
    url(action) {
      let params = $('#dataTableBuilder').dataTable().api().ajax.params();
      params.action = action
      let url = $('#dataTableBuilder').dataTable().api().ajax.url();
      return `${url}?${$.param(params)}`;
    },
    drawTable:
        _.debounce(function () {
          window.LaravelDataTables['dataTableBuilder'].draw()
        }, 400),
    updateTags() {
        if (!this.action) {
            Swal.fire('Please select an action', '', 'warning');
            return false;
        }
        
        this.associated_tag_ids = this.associated_tags.map(tag => tag.id)
        
        if (this.associated_tag_ids.length === 0) {
            Swal.fire('Please select at least one tag', '', 'warning');
            return false;
        }
        
        Swal.fire({
            title: `Apply tag changes to filtered contacts?`,
            text: ``,
            type: 'question',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    await $.get(this.url('updateTags'), {tag_ids: this.associated_tag_ids, tag_action: this.action})
                    this.drawTable()
                } catch (e) {
                    return 'error'
                }
            }
        }).then(res => {
            if (res.value === true) {
                this.action = '';
                this.associated_tags = [];
                this.associated_tag_ids = [];
                $("#addRemoveTagsModal").modal('hide');
                Swal.fire({title: 'Tags updated successfully', type: 'success', timer:1000});
            }
            else {
                if (res.value === "error") Swal.fire('Oops!', 'Something went wrong', 'error');
            }
        })
    }
  }
}
</script>

<style scoped>

</style>
