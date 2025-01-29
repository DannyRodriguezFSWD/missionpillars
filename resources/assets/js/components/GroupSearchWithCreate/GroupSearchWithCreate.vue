<template>
    <div>
        <div class="form-group">
            <label v-if="!hide_title" for="group">Group Name</label>
            <div class="input-group">
                <multiselect @select="groupSelect" v-model="group" class="form-control p-0 border-0"
                             @remove="groupRemove"
                             label="label" track-by="id" placeholder="Type to search"
                             :options="groups" :multiple="false"
                             @tag="addNewGroup"
                             tagPlaceholder="Press enter to create new group."
                             :searchable="true" :internal-search="false" :clear-on-select="true"
                             :preserve-search="true" :options-limit="100" :limit="3"
                             :max-height="300" :show-no-results="false"
                             @search-change="asyncFind" ref="group_multiselect">
                </multiselect>
                <span class="input-group-append" @click="addNewGroup">
                    <button class="input-group-text btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;Create</button>
                </span>
            </div>
        </div>

        <div class="modal fade" id="create-new-group-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Create New Group</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="onCloseModal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveNewGroup($event)">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="group_name_field">Name <span class="text-danger">*</span></label>
                                        <input type="text" @keyup="searchExisting" required id="group_name_field" name="name" class="form-control" placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="group_description_field">Description</label>
                                        <textarea id="group_description_field" name="content" class="form-control"></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-12 d-none">
                                    <input type="hidden" name="contact_id" value="current_contact">
                                </div>
                                
                                <div class="col-12 text-right">
                                    <button role="button" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;Save</button>
                                    <button role="button" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                                <div v-show="suggested_groups.length" class="col-12">
                                    <div class="font-weight-bold text-center mb-2">Suggested Groups</div>
                                    <button class="btn btn-secondary btn-block mb-2" v-for="group in suggested_groups" :key="group.id" @click="pickSuggested(group)">{{group.label}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <input name="group_id" v-model="group_id" type="hidden">
        
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import Multiselect from "../mp/multiselect";
import loading from '../Loading.vue'

export default {
    name: 'GroupSearchWithCreate',
    components: {
        Multiselect,
        loading
    },
    props: {
        on_save_group: Boolean,
        hide_title: Boolean,
        group_id: Number,
        group: Object
    },
    data() {
        return {
            groups: [],
            suggested_groups: []
        }
    },
    mounted() {
        $('#group').autocomplete({
            source: ( request, response ) => {
                $.ajax({
                    url: "/crm/ajax/groups/autocomplete",
                    type: 'post',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function( data ) {
                        response( data );
                    }
                });
            },
            minLength: 2,
            select: ( event, ui ) => {
                this.group_id = ui.item.id
            }
        });
        
        if (this.group_id == 0) {
            this.group_id = null;
        }
    },
    computed: {
        ...mapGetters([
            'getIsLoadingState'
        ])
    },
    methods: {
        searchExisting: _.debounce(function (e) {
            if (e.target.value.length > 1) {
                axios.post("/crm/ajax/groups/autocomplete",{
                    search: e.target.value
                }).then((result) => this.suggested_groups = result.data)
            }
        }, 700),
        pickSuggested(group) {
            this.group_id = group.id;
            this.group = group;
            this.suggested_groups = [group];
            this.groups = [group];
            $('#create-new-group-modal').modal('hide');
            document.querySelector('#create-new-group-modal').querySelector('form').reset();
        },
        saveNewGroup(event) {
            this.$store.dispatch('setIsLoadingState', true)
    
            let data = new FormData(event.target);
            
            axios.post('/crm/groups', data)
                .catch(e => {
                    let response = e.response.data;
                    Swal.fire('Validation Error', parseResponseJSON(response), 'error');
                }).then(res => {
                    let group = res.data.group;
                    group.label = group.name;
                    this.group_id = group.id;
                    this.group = group;
                    this.groups = [group];
                    $('#create-new-group-modal').modal('hide');
                    event.target.reset();
                    
                    if (this.on_save_group) {
                        dispatchToast('SUCCESS', 'Group created successfully');
                        isLoading = false;
                        filterGroup();
                    }
            }).finally(() => {
                this.$store.dispatch('setIsLoadingState', false)
            })
        },
        addNewGroup(search) {
            this.suggested_groups = [];
            if (typeof search == "string") $('#name_field').val(search);
            $('#create-new-group-modal').modal('show');
        },
        groupSelect(group) {
            this.group_id = group.id
        },
        groupRemove() {
            this.group_id = null
        },
        asyncFind: _.debounce(function (query) {
            $('.multiselect__spinner').show();
            axios.post("/crm/ajax/groups/autocomplete", {
                search: query
            }).then((result) => {
                this.groups = result.data
                $('.multiselect__spinner').hide();
            })
        }, 700) ,
        ...mapActions([
            'post',
            'get',
            'put',
        ]),
        onCloseModal() {
            this.action = 'insert'
            this.$refs.group_multiselect.search = "";
            this.groups = [];
            this.group = null;
        }
    }
}
</script>
