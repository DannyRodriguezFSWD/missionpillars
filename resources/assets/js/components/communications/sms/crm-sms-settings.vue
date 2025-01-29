<template>
    <div class="settings">
        <div id="accordion">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            SMS Settings
                        </button>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        <CRMCommunicationsAudience />
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Tag Contacts
                        </button>
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                    <div class="card-body">
                        <CRMCommunicationsTagActions />
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" id="headingThree">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Filter By Tags
                        </button>
                    </h5>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                    <div class="card-body">
                        <CRMCommunicationsTagsFilter />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';
    import CRMCommunicationsAudience from './crm-audience.vue';
    import CRMCommunicationsTagActions from './crm-tag-actions.vue';
    import CRMCommunicationsTagsFilter from './crm-tags-filter.vue';
    export default {
        name: 'CRMCommunicationsSMSSettings',
        data: function(){
            return {
                selected : 0,
                showModal: false,
                testPhoneNumber: "",
                message: "Enter a valid phone number",
                showMessage: false,
                loading: false,
                inputs: {
                    send_number_of_messages: {
                        value: 0,
                        disabled: false
                    },
                    do_not_send_within_number_of_days: {
                        value: 5,
                        disabled: false
                    },
                    send_all: {
                        value: false,
                        disabled: false
                    }
                }
            };
        },
        components: {
            CRMCommunicationsAudience,
            CRMCommunicationsTagActions,
            CRMCommunicationsTagsFilter
        }
    }
</script>
