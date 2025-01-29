<template>
    <div>
        <div class="form-group">
            <label v-if="!hide_title" for="family">Family Name</label>
            <div class="input-group">
                <multiselect @select="familySelect" v-model="family" class="form-control p-0 border-0"
                             @remove="familyRemove"
                             label="label" track-by="id" placeholder="Type to search"
                             :options="families" :multiple="false"
                             @tag="addNewFamily"
                             tagPlaceholder="Press enter to create new family."
                             :searchable="true" :internal-search="false" :clear-on-select="true"
                             :preserve-search="true" :options-limit="100" :limit="3"
                             :max-height="300" :show-no-results="false"
                             @search-change="asyncFind" ref="family_multiselect">
                </multiselect>
                <span class="input-group-append" @click="addNewFamily">
                    <button class="input-group-text btn btn-primary" type="button"><i class="fa fa-plus"></i>&nbsp;Create</button>
                </span>
            </div>
        </div>

        <div class="modal fade" id="create-new-family-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Create New Family</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="onCloseModal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveNewFamily($event)">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="name_field">Name <span class="text-danger">*</span></label>
                                        <input type="text" @keyup="searchExisting" required id="name_field" name="name" class="form-control" placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-12 text-right">
                                    <button role="button" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;Save</button>
                                    <button role="button" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                                <div v-show="suggested_families.length" class="col-12">
                                    <div class="font-weight-bold text-center mb-2">Suggested Families</div>
                                    <button class="btn btn-secondary btn-block mb-2" v-for="family in suggested_families" :key="family.id" @click="pickSuggested(family)">{{family.label}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <input name="family_id" v-model="family_id" type="hidden">
        
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import Multiselect from "../mp/multiselect";
import loading from '../Loading.vue'

export default {
    name: 'FamilySearchWithCreate',
    components: {
        Multiselect,
        loading
    },
    props: {
        on_save_family: Boolean,
        hide_title: Boolean,
        family_id: Number,
        family: Object
    },
    data() {
        return {
            families: [],
            suggested_families: []
        }
    },
    mounted() {
        $('#family').autocomplete({
            source: ( request, response ) => {
                $.ajax({
                    url: "/crm/ajax/families/autocomplete",
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
                this.family_id = ui.item.id
            }
        });
        
        if (this.family_id == 0) {
            this.family_id = null;
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
                axios.post("/crm/ajax/families/autocomplete",{
                    search: e.target.value
                }).then((result) => this.suggested_families = result.data)
            }
        }, 700),
        pickSuggested(family) {
            this.family_id = family.id;
            this.family = family;
            this.suggested_families = [family];
            this.families = [family];
            $('#create-new-family-modal').modal('hide');
            document.querySelector('#create-new-family-modal').querySelector('form').reset();
        },
        saveNewFamily(event) {
            this.$store.dispatch('setIsLoadingState', true)
    
            let data = new FormData(event.target);
            
            axios.post('/crm/families', data)
                .catch(e => {
                    let response = e.response.data;
                    Swal.fire('Validation Error', parseResponseJSON(response), 'error');
                }).then(res => {
                    let family = res.data
                    family.label = family.name;
                    this.family_id = family.id;
                    this.family = family;
                    this.families = [family];
                    $('#create-new-family-modal').modal('hide');
                    event.target.reset();
                    
                    if (this.on_save_family) {
                        dispatchToast('SUCCESS', 'Family created successfully');
                        isLoading = false;
                        filterFamilies();
                    }
            }).finally(() => {
                this.$store.dispatch('setIsLoadingState', false)
            })
        },
        addNewFamily(search) {
            this.suggested_families = [];
            if (typeof search == "string") $('#name_field').val(search);
            $('#create-new-family-modal').modal('show');
        },
        familySelect(family) {
            this.family_id = family.id
        },
        familyRemove() {
            this.family_id = null
        },
        asyncFind: _.debounce(function (query) {
            $('.multiselect__spinner').show();
            axios.post("/crm/ajax/families/autocomplete", {
                search: query
            }).then((result) => {
                this.families = result.data
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
            this.$refs.family_multiselect.search = "";
            this.families = [];
            this.family = null;
        }
    }
}
</script>
