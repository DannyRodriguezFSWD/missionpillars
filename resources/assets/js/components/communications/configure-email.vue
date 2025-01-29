<template>
    <div>
        <button type="button" name="button" @click="updateCommunication" v-if="debug">Update Communication</button><br>
        <h5>Communication</h5>
        <div v-if="$parent.communicationType == 'email'" class="d-flex">
          <ui-switch name="cc_secondary" v-model="cc_secondary"></ui-switch>
          <label class="text-dark ml-2" for="cc_secondary">CC contacts secondary email address</label>
        </div>
        <textarea name="name" rows="8" cols="80" v-if="debug">
            {{communication}}
        </textarea>
      <div class="row">
        <div class="col-12">
          <h5>Limit Contacts</h5>
          <div class="d-flex">
            <ui-switch name="limit_emailable_contacts" v-model="limit_emailable_contacts"></ui-switch>
            <label class="ml-2 text-dark">Limit to <input class="border-left-0 border-right-0 border-top-0 text-center" name="send_number_of_emails" type="number"
                                  v-model="communication.send_number_of_emails" ref="send_number_of_emails"
                                  :required="limit_emailable_contacts"> contacts with email address <br> </label>
          </div>
          <div class="d-flex">
            <ui-switch name="exclude_recently_emailed" v-model="exclude_recently_emailed"></ui-switch>
            <label class="ml-2 text-dark">Limit to contacts who not received an email from this list in <input name="recently_emailed_days"
                                                                                       type="number" class="border-left-0 border-right-0 border-top-0 text-center"
                                                                                       v-model="recently_emailed_days"
                                                                                       ref="recently_emailed_days"
                                                                                       :required="exclude_recently_emailed"> days <br> </label>
          </div>
          <div class="d-flex">
            <ui-switch name="exclude_prior_email_recipients" v-model="exclude_prior_email_recipients"></ui-switch>
            <label for="exclude_prior_email_recipients" class="ml-2 text-dark">Do not include people who have already received this email <br> </label>
          </div>
          <div class="d-flex">
            <ui-switch name="email_exclude_printed" v-model="communication.email_exclude_printed"></ui-switch>
            <label for="email_exclude_printed" class="ml-2 text-dark">Do not include people who have already received this as print <br> </label>
          </div>
          <div class="d-flex">
            <ui-switch name="exclude_acknowledged_transactions" v-model="communication.exclude_acknowledged_transactions"></ui-switch>
            <label for="exclude_acknowledged_transactions" class="ml-2 text-dark">Exclude acknowledged transactions <br> </label>
          </div>
        </div>
      </div>
      <CTabs variant="tabs" :active-tab="0" class="mt-3">
        <CTab>
          <template v-slot:title>
            <i class="fa fa-filter"></i>&nbsp;Apply additional filters to this list
          </template>
          <div class="form-group row mt-3">
            <div class="col-12 mb-2">
              <div class="row d-md-flex align-items-baseline">
                <div class="text-md-right col-md-6 col-xl-4">
                  Only email contacts that have these tags
                </div>
                <div class="col-md-6 col-xl-8">
                  <tags-multi-select
                      :options="$store.getters.getTagOptions"
                      @tag="(tag)=>{$store.dispatch('newTag', { tag, values: communication.include_tags });}"
                      v-model='communication.include_tags'> </tags-multi-select>
                </div>
              </div>
            </div>

            <div class="col-12 mb-2">
              <div class="row d-md-flex align-items-baseline">
                <div class="text-md-right col-md-6 col-xl-4">
                  Do not send email to contacts that have these tags
                </div>
                <div class="col-md-6 col-xl-8">
                  <tags-multi-select
                      :options="$store.getters.getTagOptions"
                      @tag="(tag)=>{$store.dispatch('newTag', { tag, values: communication.exclude_tags });}"
                      v-model='communication.exclude_tags' > </tags-multi-select>
                </div>
              </div>
            </div>

            <template v-if="communication.include_transactions">
              <div class="col-12 mb-2">
                <div class="row d-md-flex align-items-baseline">
                  <div class="text-md-right col-md-6 col-xl-4">
                    Only include transactions that have these tags
                  </div>
                  <div class="col-md-6 col-xl-8">
                    <tags-multi-select
                        :options="$store.getters.getTagOptions"
                        @tag="(tag)=>{$store.dispatch('newTag', { tag, values: communication.transaction_tags });}"
                        v-model="communication.transaction_tags" > </tags-multi-select>
                  </div>
                </div>
              </div>

              <div class="col-12 mb-2">
                <div class="row d-flex align-items-baseline">
                  <div class="text-md-right col-md-6 col-xl-4">
                    Exclude transactions that have these tags
                  </div>
                  <div class="col-md-6 col-xl-8">
                    <tags-multi-select
                        :options="$store.getters.getTagOptions"
                        @tag="(tag)=>{$store.dispatch('newTag', { tag, values: communication.excluded_transaction_tags });}"
                        v-model="communication.excluded_transaction_tags" > </tags-multi-select>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </CTab>
        <CTab>
          <template v-slot:title>
            <i class="fa fa-tag"></i>&nbsp;Tag contacts based on actions taken below
          </template>
          <div class="mt-3">
            <div class="row form-group">
              <div class="col-sm-12">
                    <textarea name="name" rows="8" cols="80" v-if="debug">
                        {{ action_tags.filter(tag => { return tag.value }) }}
                    </textarea>
              </div>
              <div class="col-sm-3">
                Action taken
              </div>
              <div class="col-sm-9">
                Apply Tag to Contact
              </div>
            </div>
            <div v-for="action_tag in action_tags" class="row form-group">
              <div :title="action_tag.description" class="col-sm-3 d-flex">
                <ui-switch :name="'track_and_tag_events['+action_tag.name+']'" v-model="action_tag.value"></ui-switch>
                <label :for="'track_and_tag_events['+action_tag.name+']'" class="ml-2">{{ action_tag.label }}</label>
              </div>
              <div class="col-sm-9">
                <tags-multi-select
                    :multiple="false"
                    :options="$store.getters.getTagOptions"
                    @tag="(tag)=>{$store.dispatch('newTag', { tag, value: action_tag });}"
                    v-model="action_tag.selected" > </tags-multi-select>
                <input type="hidden" :name="'track_and_tag_events['+action_tag.name+']'" value="action_tag.selected">
              </div>
            </div>


          </div>
        </CTab>
      </CTabs>

        <div class="row">
            <div class="col text-center">
                <button type="button" name="button" class="btn btn-primary btn-lg" @click="saveConfiguration">
                    <i class="fa fa-save"></i> Save and Confirm Email
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import tagsMultiSelect from '../mp/tags-multiselect'
import uiSwitch from '../widgets/switch'
import configActionTags, { setActionTags, updateTrackAndTagEvents } from './config_actiontags.js'
import objectHelpers, { object_pick, object_pluck } from '../../object_helpers.js'
import {CTab,CTabs} from '@coreui/vue-pro'

export default {
    components: {
      tagsMultiSelect,
      uiSwitch,
      CTab,
      CTabs
    },
    props: [ 'data','update_communication_route' ],
    data() { return {
        debug: false,

        tag_options: [],
        communication: this.setCommunication(),
        cc_secondary: false,
        limit_emailable_contacts: !communication.send_to_all, // reversing logic for UI
        exclude_recently_emailed: communication.do_not_send_within_number_of_days != null,
        recently_emailed_days: communication.do_not_send_within_number_of_days,
        exclude_prior_email_recipients: communication.do_not_send_to_previous_receivers,
        transactionlimits: [],


        action_tags: [
            {name: 'sent', label: 'Sent', description: 'The message has been sent.',value:false,selected:[]},
            {name: 'error', label: 'Error', description: 'The message has not been sent because of malformed email.',value:false,selected:[]},
            {name: 'accepted', label: 'Accepted', description: 'The message has been placed in queue.',value:false,selected:[]},
            {name: 'rejected', label: 'Rejected', description: 'The message has been rejected by the recipient email server.',value:false,selected:[]},
            {name: 'delivered', label: 'Delivered', description: 'The email was sent and it was accepted by the recipient email server.',value:false,selected:[]},
            {name: 'failed', label: 'Failed', description: 'The email could not be delivered to the recipient email server.',value:false,selected:[]},
            {name: 'opened', label: 'Opened', description: 'The email recipient opened the email.',value:false,selected:[]},
            {name: 'clicked', label: 'Clicked', description: 'The email recipient clicked on a link in the email.',value:false,selected:[]},
            {name: 'unsubscribed', label: 'Unsubscribed', description: 'The email recipient clicked on the unsubscribe link.',value:false,selected:[]},
            {name: 'complained', label: 'Complained', description: 'The email recipient clicked on the spam complaint button within their email client.',value:false,selected:[]},
        ],
        additionalFiltersIsHidden: true,
        tagContactsIsHidden: true
    }},
    computed: {
    },
    watch: {
        limit_emailable_contacts(newValue) {
            if(newValue) {
                if(!this.communication.send_number_of_emails) this.communication.send_number_of_emails = 100
                this.$refs.send_number_of_emails.focus()
            }
            else this.communication.send_number_of_emails = 0
        },
        exclude_recently_emailed(newValue) {
            if (newValue) {
                this.recently_emailed_days = 5
                this.$refs.recently_emailed_days.focus()
            }
            else this.recently_emailed_days = 0
        },
    },
    mounted() {
        this.setActionTags()
        this.cc_secondary = this.communication.cc_secondary
        this.$store.dispatch('setTagOptions')
    },
    methods: {
        ...configActionTags,
        ...objectHelpers,
        setCommunication() {
            var data = communication

            if (!data.send_to_all && !data.send_number_of_emails) data.send_number_of_emails = 100
            if (data.include_tags == null) data.include_tags = []
            if (data.exclude_tags == null) data.exclude_tags = []
            if (data.track_and_tag_events == null) data.track_and_tag_events = {}

            return data
        },
        updateCommunication() {
            this.communication.send_to_all = !this.limit_emailable_contacts
            this.communication.do_not_send_within_number_of_days = this.exclude_recently_emailed ? this.recently_emailed_days : null
            this.communication.do_not_send_to_previous_receivers = this.exclude_prior_email_recipients

            // this.communication.include_tags = this.communication.include_tags.map((tag)=>{return tag.id}).join(',')
            // this.communication.exclude_tags = this.communication.exclude_tags.map((tag)=>{return tag.id}).join(',')
            // this.communication.transactionfilter_tags = this.transactionlimits.map((tag)=>{return tag.id}).join(',')

            this.updateTrackAndTagEvents()
        },
        saveConfiguration() {
          this.$store.dispatch('setIsLoadingState', true);
            this.updateCommunication()

            // sanitize data to to updated
            var data = {communication: this.object_pick(this.communication, [
                'id','tenant_id',
                "do_not_send_within_number_of_days", "send_number_of_emails", "send_to_all", "do_not_send_to_previous_receivers", "email_exclude_printed", "exclude_acknowledged_transactions",
            ])}
            data.include_tags = this.object_pluck(communication.include_tags, 'id')
            data.exclude_tags = this.object_pluck(communication.exclude_tags, 'id')
            data.transaction_tags = communication.include_transactions
            ? this.object_pluck(communication.transaction_tags, 'id') : []
            data.excluded_transaction_tags = communication.include_transactions
            ? this.object_pluck(communication.excluded_transaction_tags, 'id') : []
            data.communication.track_and_tag_events = this.object_pick(communication.track_and_tag_events,
                this.object_pluck(this.action_tags,'name'))
            data.communication.cc_secondary = this.cc_secondary ? 1 : null;
            // Ajax Call
            var self = this
            axios.put(this.update_communication_route, data)
            .then(response => {
                // console.log(response.data)
                this.$emit('configuration-saved',{
                    communication_type: 'email',
                    component: self
                });
            })
            .catch(function (error) {
                console.log(error)
                Swal.fire('Configuration not saved','Please reload and try again','error')
            })
        },
    },
}
</script>

<style scoped lang="css">
h3 {
    margin-top: 15px;
}
h5 {
    font-style: italic;
}
label {
    background: none !important;
    padding-left: 0 !important;
    color: gray;
    cursor: pointer;
}
dfn {
    display: inline-block;
    width: 12ch;
    color: black;
    font-weight: bold;
}

.tag_wrapper {
    margin-left: 5ch;
}

</style>
