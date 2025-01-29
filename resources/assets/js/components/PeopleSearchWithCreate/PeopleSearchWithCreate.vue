<template>
    <div>
        <div class="form-group">
            <label v-if="!hide_title" for="contact">Contact's Name</label>
            <div class="input-group">
                <multiselect @select="contactSelect" v-model="contact" class="form-control p-0 border-0"
                             @remove="contactRemove"
                             label="label" track-by="id" placeholder="Type to search"
                             :options="contacts" :multiple="false"
                             @tag="addNewContact"
                             tagPlaceholder="Press enter to create new contact."
                             :searchable="true" :internal-search="false" :clear-on-select="true"
                             :preserve-search="true" :options-limit="100" :limit="3"
                             :max-height="300" :show-no-results="false"
                             @search-change="asyncFind" ref="contact_multiselect">
                </multiselect>
                <span class="input-group-append" @click="addNewContact">
                    <button class="input-group-text btn btn-primary"><i class="fa fa-plus"></i>&nbsp;Create</button>
                </span>
            </div>
        </div>

        <div class="modal fade" :id="create_contact_modal_id">
            <div :class="(show_group_search || show_family_search) ? 'modal-dialog modal-lg' : 'modal-dialog'">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Create New Contact</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="onCloseModal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveNewContact($event)">
                            <div class="row">
                                <div :class="(show_group_search || show_family_search) ? 'col-lg-6 col-md-12' : 'col-12'">
                                    <div class="form-group">
                                        <label for="first_name_field">First Name <span class="text-danger">*</span></label>
                                        <input type="text" @keyup="searchExisting" required id="first_name_field" name="first_name" class="form-control" placeholder="First Name">
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name_field">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" @keyup="searchExisting" required id="last_name_field" name="last_name" class="form-control" placeholder="Last Name">
                                    </div>
                                    <div class="form-group">
                                        <label for="email_field">Email</label>
                                        <input type="email" @keyup="searchExisting" id="email_field" name="email_1" class="form-control" placeholder="example@example.com">
                                    </div>
                                    <div class="form-group">
                                        <label for="mailing_address_1">Mailing Address</label>
                                        <input type="text" @keyup="searchExisting" id="mailing_address_1" name="mailing_address_1" class="form-control" placeholder="Mailing Address">
                                    </div>
                                </div>
                                
                                <div v-if="(show_group_search || show_family_search)" class="col-lg-6 col-md-12">
                                    <div class="form-group" v-if="show_group_search">
                                        <label for="checkin_groups">Add To Small Groups</label>
                                        
                                        <div style="overflow-y: auto; max-height: 230px;">
                                            <div v-for="g in groups" class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" :id="'checkin_group_' + g.id" :name="'checkin_group_' + g.id" :checked="groupUuid === g.uuid" @change="addToGroup(g.uuid, $event)">
                                                <label class="form-check-label" :for="'checkin_group_' + g.id">
                                                    {{ g.name }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="show_family_search">
                                        <p>Add this contact to a <a href="#" @click="showFamilyForm">family</a>.</p>

                                        <div id="familyForm" style="display: none;">
                                            <div class="form-group">
                                                <div id="family-search-with-create">
                                                    <label>Family</label>
                                                    <family-search-with-create
                                                        :on_save_contact="true"
                                                        :hide_title="true"
                                                        :family_id="0"
                                                    ></family-search-with-create>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="family">Family Position</label>
                                                <select id="family_position" name="family_position" class="form-control">
                                                    <option value=""></option>
                                                    <option value="Primary Contact">Primary Contact</option>
                                                    <option value="Spouse">Spouse</option>
                                                    <option value="Child">Child</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div id="relativeForm" style="display: none;">

                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 text-right">
                                    <button role="button" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;Save</button>
                                    <button role="button" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                                <div v-show="suggested_contacts.length" class="col-12">
                                    <div class="font-weight-bold text-center mb-2">Suggested Contacts</div>
                                    <button class="btn btn-secondary btn-block mb-2" v-for="contact in suggested_contacts" :key="contact.id" @click="pickSuggested(contact)">{{contact.label}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <input name="contact_id" v-model="contact_id" type="hidden">
        
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import Multiselect from "../mp/multiselect";
import loading from '../Loading.vue';
import FamilySearchWithCreate from "../FamilySearchWithCreate/FamilySearchWithCreate";

export default {
    name: 'PeopleSearchWithCreate',
    components: {
        Multiselect,
        loading
    },
    props: {
        add_to_group: Boolean,
        on_save_contact: Boolean,
        hide_title: Boolean,
        show_group_search: Boolean,
        show_family_search: Boolean,
        create_contact_modal_id: {
            type: String,
            default: 'create-new-contact-modal'
        },
        groups: Object
    },
    data() {
        return {
            contacts: [],
            suggested_contacts: [],
            contact: null,
            contact_id: null,
            familyOrRelative: 'family',
            groupUuid: null,
            addToGroups: []
        }
    },
    mounted() {
        $('#contact').autocomplete({
            source: ( request, response ) => {
                $.ajax({
                    url: "/crm/ajax/contacts/autocomplete",
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
                this.contact_id = ui.item.id
            }
        });
        
        if (group) {
            this.groupUuid = group;
            this.addToGroups.push(group);
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
                axios.post("/crm/ajax/contacts/autocomplete",{
                    search: e.target.value
                }).then((result) => this.suggested_contacts = result.data)
            }
        }, 700),
        pickSuggested(contact) {
            this.contact_id = contact.id;
            this.contact = contact;
            this.suggested_contacts = [contact];
            this.contacts = [contact];
            $('#'+this.create_contact_modal_id).modal('hide');
            document.querySelector('#'+this.create_contact_modal_id).querySelector('form').reset();
            
            if (this.add_to_group && group) {
                customAjax({
                    url: '/crm/groups/'+group+'/members/sync-uuid',
                    data: {
                        action: 'add',
                        contact_id: contact.id
                    },
                    success: function (data) {
                        $('#'+this.create_contact_modal_id).modal('hide');
                        dispatchToast('SUCCESS', 'Contact added to group created successfully');
                        isLoading = false;
                        filterContacts();
                    }
                });
            }
        },
        saveNewContact(event) {
            this.$store.dispatch('setIsLoadingState', true)
    
            let data = new FormData(event.target);
            
            if (this.add_to_group && group) {
                data.append('addToGroups', this.addToGroups);
            }
            
            axios.post('/crm/contacts', data)
                .catch(e => {
                    let response = e.response.data;
                    if (response.email_1) {
                        response.email_1 = ['The email already exists in your contacts.'];
                    }
                    Swal.fire('Validation Error', parseResponseJSON(response), 'error');
                }).then(res => {
                    let contact = res.data
                    contact.label = contact.first_name + ' ' + contact.last_name + ' ('+ contact.email_1 +')';
                    this.contact_id = contact.id;
                    this.contact = contact;
                    this.contacts = [contact];
                    $('#'+this.create_contact_modal_id).modal('hide');
                    event.target.reset();
                    
                    if (this.on_save_contact) {
                        dispatchToast('SUCCESS', 'Contact created successfully');
                        isLoading = false;
                        filterContacts();
                    }
            }).finally(() => {
                this.$store.dispatch('setIsLoadingState', false)
            })
        },
        addNewContact(search) {
            this.suggested_contacts = [];
            if (typeof search == "string") $('#first_name_field').val(search);
            $('#'+this.create_contact_modal_id).modal('show');
        },
        contactSelect(contact) {
            this.contact_id = contact.id
    
            if (this.add_to_group && group) {
                customAjax({
                    url: '/crm/groups/'+group+'/members/sync-uuid',
                    data: {
                        action: 'add',
                        contact_id: contact.id
                    },
                    success: function (data) {
                        $('#'+this.create_contact_modal_id).modal('hide');
                        dispatchToast('SUCCESS', 'Contact added to group created successfully');
                        isLoading = false;
                        filterContacts();
                    }
                });
            }
        },
        contactRemove() {
            this.contact_id = null
        },
        asyncFind: _.debounce(function (query) {
            $('.multiselect__spinner').show();
            axios.post("/crm/ajax/contacts/autocomplete", {
                search: query
            }).then((result) => {
                this.contacts = result.data
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
            this.$refs.contact_multiselect.search = "";
            this.contacts = [];
            this.contact = null;
        },
        showFamilyForm() {
            $('#familyForm').fadeIn();
            $('#relativeForm').fadeOut;
        },
        showRelativeForm() {
            $('#relativeForm').fadeIn();
            $('#familyForm').fadeOut();
        },
        addToGroup(uuid, e) {
            if (e.target.checked) {
                this.addToGroups.push(uuid)
            } else {
                this.addToGroups.splice(this.addToGroups.indexOf(uuid), 1);
            }
        }
    }
}
</script>
