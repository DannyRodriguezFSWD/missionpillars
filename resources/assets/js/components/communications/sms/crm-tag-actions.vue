<template>
    <div class="tag-actions">

            <div class="row">
                <div class="col-sm-12">
                    <h5><strong>Tag contacts based on actions taken below:</strong></h5>
                    <h6>Select an Action to Tag Contact Based on that Action</h6>
                </div>
                <div class="col-sm-12">
                    <ul class="list-group">
                        <li class="list-group-item" v-for="(action, index) in actions">
                            <input :checked="action.tag > 0" type="checkbox" :value="index" @click="showTagsModalPopup($event)"/> {{ action.text }}
                        </li>
                    </ul>
                </div>
            </div>



        <CRMModal v-if="showTagsModal" @close="showTagsModal = false">
            <h3 slot="header">Select Tag</h3>
            <div slot="body" style="max-height: 500px; overflow: auto;">
                <tree :data="treeData" :options="treeOptions">
                    <span class="tree-text" slot-scope="{ node }" @click="checkThisRadiobox($event)">
                    <template v-if="!node.hasChildren()">
                        <input type="radio" name="select-radio" :value="node.id" v-model="tag"/>
                        <i class="fa fa-tag"></i> {{ node.text }}
                    </template>

                    <template v-else>
                        <i :class="[node.expanded() ? 'icon-folder-alt' : 'icon-folder']"></i>
                        {{ node.text }}
                    </template>
                    </span>
                </tree>
            </div>
            <button slot="footer" class="modal-default-button btn btn-secondary" @click="closeTagsModalPopup()">
                Close
            </button>
            <button slot="footer" class="modal-default-button btn btn-success" @click="onClickNewTag($event)">
                New tag
            </button>
            <button slot="footer" class="modal-default-button btn btn-primary" @click="closeTagsModalPopup()">
                Select tag
            </button>
        </CRMModal>

        <CRMModal v-if="showCreateTagsModal" @close="showCreateTagsModal = false">
            <h3 slot="header">New tag</h3>
            <div slot="body" style="max-height: 500px; overflow: auto;">
                <div class="form-group">
                    <label><span class="text-danger">*</span> Tag</label>
                    <input type="text" class="form-control" v-model="tagName"/>
                    <div class="text-danger" v-if="showTagAlert">Enter the tag name</div>
                </div>
                <div class="form-group">
                    <label><span class="text-danger">*</span> Folder</label>
                    <select class="form-control" v-model="tagFolder">
                        <option v-for="folder in folders" :value="folder.id">{{ folder.name }}</option>
                    </select>
                </div>
            </div>
            <button slot="footer" class="modal-default-button btn btn-secondary" @click="showCreateTagsModal = false; showTagsModal = true;">
                Close
            </button>
            <button slot="footer" class="modal-default-button btn btn-primary" @click="onSaveTag()">
                Save tag
            </button>
        </CRMModal>

    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';
    import CRMModal from '../../crm-modal.vue';

    export default {
        name: 'CRMCommunicationsTagActions',
        components: {
            CRMModal
        },
        data: function(){
            return {
                tag : 0,
                tagName: '',
                tagFolder: 1,
                showTagAlert: false,
                showTagsModal: false,
                showCreateTagsModal: false,
                loading: false,
                values: {
                    status : []
                },
                target: null,
                treeOptions: {
                },
                actions: []
            };
        },
        mounted() {
            this.actions = this.data.actions;
        },
        computed: {
            ...mapState([
                'treeData',
                'folders',
                'data'
            ])
        },
        methods: {
            ...mapMutations([
                'IS_LOADING',
                'STATE_TREE_DATA',
                'STATE_FOLDERS',
                'STATE_ACTIONS'
            ]),
            checkThisRadiobox: function(event){
                $(event.target).find('input[type=radio]').click();
            },
            showTagsModalPopup: function(){
                this.target = $(event.target);
                this.tag = 0;
                if(this.target.prop('checked')){
                    this.showTagsModal = true;
                }
                else{
                    this.actions[this.target.val()].tag = 0;
                }
            },
            closeTagsModalPopup: function(){
                if(this.tag == 0){
                    this.target.click();
                    this.actions[this.target.val()].tag = 0;
                }
                else{
                    this.actions[this.target.val()].tag = this.tag;
                }
                this.STATE_ACTIONS(this.actions);
                this.showTagsModal = false;
            },
            onClickNewTag: function(event){
                this.showTagsModal = false;
                this.showCreateTagsModal = true;
            },
            onSaveTag: function(){
                if(this.tagName.trim() == ''){
                    this.showTagAlert = true;
                    return false;
                }
                let url = this.$store.state.baseUrl +'vue/create/tag';
                this.IS_LOADING(true);
                let params = {
                    tag: this.tagName,
                    folder: this.tagFolder
                };
                axios.post(url, params).then((response) => {
                    this.IS_LOADING(false);
                    this.STATE_TREE_DATA(response.data.tree);
                    this.STATE_FOLDERS(response.data.folders);
                    this.showCreateTagsModal = false;
                    this.showTagsModal = true;
                });
            }
        }
    }
</script>
