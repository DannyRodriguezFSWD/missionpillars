<template>
    <div>
        <div class="form-group row">
            <div class="col">
                <h5>Limit Contacts</h5>
                <div class="d-flex">
                    <ui-switch class="mr-2" name="print_only_paper_statement_contacts" v-model="communication.print_only_paper_statement_contacts"></ui-switch>
                    <label class="text-dark" for="print_only_paper_statement_contacts">Only send to people marked to receive paper contributions</label>
                </div>
                <div class="d-flex">
                    <ui-switch class="mr-2" name="limit_printable_contacts" v-model="limit_printable_contacts"></ui-switch>
                    <label class="text-dark" for="">Limit to <input name="print_max_contacts" type="number" class="border-top-0 border-right-0 border-left-0 text-center" v-model="communication.print_max_contacts" ref="print_max_contacts"> contacts</label>
                </div>
                <div class="d-flex">
                    <ui-switch class="mr-2" name="include_non_addressed" v-model="communication.print_include_non_addressed"></ui-switch>
                    <label class="text-dark" for="include_non_addressed">Include contacts without mailing addresses</label>
                </div>
                <div class="d-flex">
                    <ui-switch class="mr-2" name="" v-model="exclude_recently_printed"></ui-switch>
                    <label class="text-dark" for="">Limit to contacts who been included in a print communication from this list in
                      <input name="communication.print_exclude_recent_ndays" type="number" class="border-top-0 border-right-0 border-left-0 text-center" v-model="communication.print_exclude_recent_ndays" ref="print_exclude_recent_ndays"> days</label>
                </div>
                <div class="d-flex">
                    <ui-switch class="mr-2" name="print_exclude_emailed" v-model="communication.print_exclude_emailed"></ui-switch>
                  <label class="text-dark" for="print_exclude_emailed">Do not include people who have already received this email</label>
                </div>
                <div class="d-flex">
                    <ui-switch class="mr-2" name="print_exclude_printed" v-model="communication.print_exclude_printed"></ui-switch>
                  <label class="text-dark" for="print_exclude_printed">Do not include people who have already received this as print</label>
                </div>
                <div class="d-flex">
                    <ui-switch class="mr-2" name="exclude_acknowledged_transactions" v-model="communication.exclude_acknowledged_transactions"></ui-switch>
                  <label class="text-dark" for="exclude_acknowledged_transactions">Exclude acknowledged transactions</label>
                </div>
            </div>
        </div>


        <CTabs class="mt-3" variant="tabs" :active-tab="0">
          <CTab>
            <template v-slot:title>
              <i class="fa fa-filter"></i> Apply additional filters to this list
            </template>
            <div class="mt-3">
              <div class="form-group row">
                <div class="col-12">
                  <div class="row d-md-flex align-items-baseline">
                    <div class="text-right col-md-6 col-xl-4">
                      Only print contacts that have these tags
                    </div>
                    <div class="col-md-6 col-xl-8">
                      <tags-multi-select
                          :options="$store.getters.getTagOptions"
                          @tag="(tag)=>{$store.dispatch('newTag', { tag, values: communication.print_include_tags });}"
                          v-model='communication.print_include_tags'></tags-multi-select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group row">
                <div class="col-12 mb-2">
                  <div class="row d-md-flex align-items-baseline">
                    <div class="text-right col-md-6 col-xl-4">
                      Do not print contacts that have these tags
                    </div>
                    <div class="col-md-6 col-xl-8">
                      <tags-multi-select
                          :options="$store.getters.getTagOptions"
                          @tag="(tag)=>{$store.dispatch('newTag', { tag, values: communication.print_exclude_tags });}"
                          v-model='communication.print_exclude_tags' > </tags-multi-select>
                    </div>
                  </div>
                </div>

                <template v-if="communication.include_transactions">
                  <div class="col-12 mb-2">
                    <div class="row d-md-flex align-items-baseline">
                      <div class="text-right col-md-6 col-xl-4">
                        Only include transactions that have these tags
                      </div>
                      <div class="col-md-6 col-xl-8">
                        <tags-multi-select
                            :options="$store.getters.getTagOptions"
                            @tag="(tag)=>{$store.dispatch('newTag', { tag, values: communication.transaction_tags });}"
                            v-model="communication.transaction_tags"></tags-multi-select>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 mb-2">
                    <div class="row d-md-flex align-items-baseline">
                      <div class="text-right col-md-6 col-xl-4">
                        Exclude transactions that have these tags
                      </div>
                      <div class="col-md-6 col-xl-8">
                        <tags-multi-select
                            :options="$store.getters.getTagOptions"
                            @tag="(tag)=>{$store.dispatch('newTag', { tag, values: communication.excluded_transaction_tags });}"
                            v-model="communication.excluded_transaction_tags"></tags-multi-select>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </CTab>
          <CTab>
            <template v-slot:title>
              <i class="fa fa-tag"></i>&nbsp;Tag contacts based on actions taken below
            </template>
            <div class="mt-3">
              <div class="row form-group">
                <div class="col-sm-3">
                  Action taken
                </div>
                <div class="col-sm-9">
                  Apply Tag Contact
                </div>
              </div>
              <div v-for="action_tag in action_tags" class="row form-group">
                <div :title="action_tag.description" class="col-sm-3 d-flex">
                  <ui-switch class="mr-2" :name="'track_and_tag_events['+action_tag.name+']'" v-model="action_tag.value"></ui-switch>
                  <label :for="'track_and_tag_events['+action_tag.name+']'">{{ action_tag.label }}</label>
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
                    <i class="fa fa-save"></i> Save and Confirm Print
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import tagsMultiSelect from '../mp/tags-multiselect.vue'
import uiSwitch from '../widgets/switch'
import configActionTags, { setActionTags, updateTrackAndTagEvents } from './config_actiontags.js'
import objectHelpers, { object_pick, object_pluck } from '../../object_helpers.js'
import {CTab,CTabs} from '@coreui/vue-pro'
export default {
    props: [ 'data','update_communication_route' ],
    components: {
        tagsMultiSelect,
        uiSwitch,
        CTab,
        CTabs
    },
    data() { return {
        tag_options: [],
        communication: this.setCommunication(),

        limit_printable_contacts: communication.print_limit_contacts,
        exclude_recently_printed: communication.print_exclude_recent_ndays != null,
        transactionlimits: [],

        action_tags: [
            {name: 'printed', label: 'Printed', description: 'The message has been generated as a PDF for printing.' ,value:false,selected:[]},
        ],
        additionalFiltersIsHidden: true,
        tagContactsIsHidden: true
    }},
    mounted() {
        this.setActionTags()

        this.$store.dispatch('setTagOptions')
    },
    watch: {
        limit_printable_contacts(newValue) {
            if(newValue) {
                if(!this.communication.print_max_contacts) this.communication.print_max_contacts = 100
                this.$refs.print_max_contacts.focus()
            }
            else this.communication.print_max_contacts = 0
        },
        exclude_recently_printed(newValue) {
            if (newValue) {
                this.communication.print_exclude_recent_ndays = 5
                this.$refs.print_exclude_recent_ndays.focus()
            }
            else this.communication.print_exclude_recent_ndays = 0
        },
    },
    methods: {
        ...configActionTags,
        ...objectHelpers,
        setCommunication() {
            var data = communication

            if (data.print_limit_contacts && !data.print_max_contacts) data.print_max_contacts = 100
            if (data.print_include_tags == null) data.print_include_tags = []
            if (data.print_exclude_tags == null) data.print_exclude_tags = []
            if (data.track_and_tag_events == null) data.track_and_tag_events = {}

            return data
        },
        updateCommunication() {
            this.communication.print_limit_contacts = this.limit_printable_contacts
            if(!this.exclude_recently_printed) this.communication.print_exclude_recent_ndays = null

            this.updateTrackAndTagEvents()
        },
        saveConfiguration() {
            this.$store.dispatch('setIsLoadingState',true);
            this.updateCommunication()

            // sanitize data to to updated
            var data = {communication: this.object_pick(this.communication, [
                'id','tenant_id',
                'print_limit_contacts','print_include_non_addressed','print_max_contacts','print_only_paper_statement_contacts','print_exclude_emailed','print_exclude_printed', 'print_exclude_recent_ndays', 'exclude_acknowledged_transactions',
            ])}
            data.print_include_tags = this.object_pluck(communication.print_include_tags, 'id')
            data.print_exclude_tags = this.object_pluck(communication.print_exclude_tags, 'id')
            data.transaction_tags = communication.include_transactions
            ? this.object_pluck(communication.transaction_tags, 'id') : []
            data.excluded_transaction_tags = communication.include_transactions
            ? this.object_pluck(communication.excluded_transaction_tags, 'id') : []
            data.communication.track_and_tag_events = this.object_pick(communication.track_and_tag_events, ['printed'])

            // Ajax Call
            var self = this
            axios.put(this.update_communication_route, data)
            .then(response => {
                // console.log(response)
                this.$emit('configuration-saved',{
                    communication_type: 'print',
                    component: this
                });
            })
            .catch(function (error) {
                console.log(error)
                Swal.fire('Configuration not saved.','Please reload and try again','error')
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
</style>
