<template>
  <div class="card-body">
    <form id="filtersForm" class="row">
      <div class="col-md-4">
        <div id="dateFilters" class="card border-primary">
        <div class="card-header">
          Transaction Dates
          <div class="btn-group float-right">
            <button @click="show_date_filter = !show_date_filter" type="button"
                    class="btn btn-transparent p-0 text-success">
              <i class="fa" :class="{'fa-chevron-down': show_date_filter, 'fa-chevron-left': !show_date_filter}"></i>
            </button>
          </div>
        </div>
        <div class="card-body" :class="{'d-none': !show_date_filter}">
          <div class="form-group">
            <label for="transaction_date_min">Starting</label>
            <input v-model="dates.start_date" @change="drawTable" name="transaction_date_min" id="transaction_date_min"
                   class="form-control"
                   type="date">
          </div>
          <div class="form-group">
            <label for="transaction_date_max">Ending</label>
            <input v-model="dates.end_date" @change="drawTable" name="transaction_date_max" id="transaction_date_max"
                   class="form-control"
                   type="date">
          </div>
        </div>
      </div>
      </div>
      <div class="col-md-4"  v-if="canViewTransactions">
        <div id="amountFilters" class="card border-primary">
        <div class="card-header">
          Transaction Amount
          <div class="btn-group float-right">
            <button @click="show_money_filter = !show_money_filter" type="button"
                    class="btn btn-transparent p-0 text-success">
              <i class="fa" :class="{'fa-chevron-down': show_money_filter, 'fa-chevron-left': !show_money_filter}"></i>
            </button>
          </div>
        </div>
        <div class="card-body" :class="{'d-none': !show_money_filter}">
          <div class="form-group">
            <label for="transaction_amount_min">Min</label>
            <input v-model="money.a" @input="drawTable" name="transaction_amount_min" id="transaction_amount_min"
                   class="form-control"
                   type="number">
          </div>
          <div class="form-group">
            <label for="transaction_amount_max">Max</label>
            <input v-model="money.b" @input="drawTable" name="transaction_amount_max" id="transaction_amount_max"
                   class="form-control"
                   type="number">
          </div>
          <label for="transaction_amount_use_sum_1">Use Sum</label>
          <input @change="drawTable" type="radio" id="transaction_amount_use_sum_1"
                 name="transaction_amount_use_sum" value="1" checked> Yes
          <input @change="drawTable" type="radio" id="transaction_amount_use_sum_0"
                 name="transaction_amount_use_sum" value="0"> No
        </div>
      </div>
      </div>
      <div class="col-md-4">
        <div id="purposeFilters" class="card border-primary">
        <div class="card-header">
          Purposes
          <div class="btn-group float-right">
            <button @click="show_purpose_filter = !show_purpose_filter" type="button"
                    class="btn btn-transparent p-0 text-success">
              <i class="fa" :class="{'fa-chevron-down': show_purpose_filter, 'fa-chevron-left': !show_purpose_filter}"></i>
            </button>
          </div>
        </div>
        <div class="card-body" v-if="show_purpose_filter">
          <div class="form-group">
            <select name="transaction_purposes" class="form-control d-none" multiple v-model="selected_purposes2">
              <option v-for="option in purposesWithParent" :value="option.id">{{ option.name }}</option>
            </select>
            <Multiselect :multiple="true" :options="purposesWithParent" @input="purposes_change" :show-labels="false"
                         placeholder="Select Purpose"
                         v-model="selected_purposes">
              <template slot="option" slot-scope="props">
                <div class="option__desc"><b>{{ props.option.name }}</b> <span
                    class="helptext float-right">{{ props.option.parent }}</span></div>
              </template>
            </Multiselect>
          </div>
        </div>
      </div>
      </div>
      <div class="col-md-4">
        <div id="campaignFilters" class="card border-primary">
        <div class="card-header">
          Fundraisers
          <div class="btn-group float-right">
            <button @click="show_campaigns_filter = !show_campaigns_filter" type="button"
                    class="btn btn-transparent p-0 text-success">
              <i class="fa" :class="{'fa-chevron-down': show_campaigns_filter, 'fa-chevron-left': !show_campaigns_filter}"></i>
            </button>
          </div>
        </div>
        <div class="card-body" v-if="show_campaigns_filter">
          <div class="form-group">
            <select name="transaction_campaigns" class="form-control d-none" multiple v-model="selected_campaigns2">
              <option v-for="option in campaigns" :value="option.id">{{ option.name }}</option>
            </select>
            <Multiselect :multiple="true" :options="campaigns" @input="campaigns_change" :show-labels="false"
                         placeholder="Select Fundraisers"
                         v-model="selected_campaigns">
              <template slot="option" slot-scope="props">
                <div class="option__desc"><b>{{ props.option.name }}</b></div>
              </template>
            </Multiselect>
          </div>
        </div>
      </div>
      </div>
      <div class="col-md-4">
        <div id="tagFilters" class="card border-primary">
        <div class="card-header">
          Included Tags
          <div class="btn-group float-right">
            <button @click="show_include_tag_filter = !show_include_tag_filter" type="button"
                    class="btn btn-transparent p-0 text-success">
              <i class="fa"
                 :class="{'fa-chevron-down': show_include_tag_filter, 'fa-chevron-left': !show_include_tag_filter}"></i>
            </button>
          </div>
        </div>
        <div class="card-body" v-if="show_include_tag_filter">
          <div class="form-group">
            <select class="form-control d-none" name="contact_tags" multiple v-model="included_tags2">
              <option v-for="option in folders" :value="option.id">{{ option.name }}</option>
            </select>
            <TagsMultiSelect :multiple="true" :options="folders" @input="included_tags_change" :show-labels="false"
                             placeholder="Include Tags"
                             v-model="included_tags">
            </TagsMultiSelect>
          </div>
        </div>
      </div>
      </div>
      <div class="col-md-4">
        <div id="tagFilters" class="card border-primary">
        <div class="card-header">
          Excluded Tags
          <div class="btn-group float-right">
            <button @click="show_exclude_tag_filter = !show_exclude_tag_filter" type="button"
                    class="btn btn-transparent p-0 text-success">
              <i class="fa"
                 :class="{'fa-chevron-down': show_exclude_tag_filter, 'fa-chevron-left': !show_exclude_tag_filter}"></i>
            </button>
          </div>
        </div>
        <div class="card-body" v-if="show_exclude_tag_filter">
          <div class="form-group">
            <select class="form-control d-none" name="contact_excluded_tags" multiple v-model="excluded_tag2">
              <option v-for="option in folders" :value="option.id">{{ option.name }}</option>
            </select>
            <TagsMultiSelect :multiple="true" :options="folders" @input="excluded_tags_change" :show-labels="false"
                             placeholder="Exclude Tags"
                             v-model="excluded_tag">
            </TagsMultiSelect>
          </div>
        </div>
      </div>
      </div>
        <div class="col-md-4">
            <div id="transactionTagFilters" class="card border-primary">
                <div class="card-header">
                    Included Transaction Tags
                    <div class="btn-group float-right">
                        <button @click="show_include_transaction_tag_filter = !show_include_transaction_tag_filter" type="button"
                                 class="btn btn-transparent p-0 text-success">
                            <i class="fa"
                               :class="{'fa-chevron-down': show_include_transaction_tag_filter, 'fa-chevron-left': !show_include_transaction_tag_filter}"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" v-if="show_include_transaction_tag_filter">
                    <div class="form-group">
                        <select class="form-control d-none" name="transaction_tags" multiple v-model="included_transaction_tags2">
                            <option v-for="option in folders" :value="option.id">{{ option.name }}</option>
                        </select>
                        <TagsMultiSelect :multiple="true" :options="folders" @input="included_transaction_tags_change" :show-labels="false"
                                         placeholder="Include Tags"
                                         v-model="included_transaction_tags">
                        </TagsMultiSelect>
                    </div>
                </div>
            </div>
        </div>
      <div class="col-md-4">
        <div id="groupFilter" class="card border-primary">
            <div class="card-header">
              Small Groups
              <div class="btn-group float-right">
                <button @click="show_group_filter = !show_group_filter" type="button"
                        class="btn btn-transparent p-0 text-success">
                  <i class="fa"
                     :class="{'fa-chevron-down': show_group_filter, 'fa-chevron-left': !show_group_filter}"></i>
                </button>
              </div>
            </div>
            <div class="card-body" v-if="show_group_filter">
                <div class="form-group">
                    <select name="groups" class="form-control d-none" multiple v-model="selected_groups2">
                        <option v-for="option in groups" :value="option.id">{{ option.name }}</option>
                    </select>
                    <Multiselect :multiple="true" :options="groups" @input="groups_change" :show-labels="false"
                                   placeholder="Select Groups"
                                   v-model="selected_groups">
                        <template slot="option" slot-scope="props">
                            <div class="option__desc"><b>{{ props.option.name }}</b></div>
                        </template>
                    </Multiselect>
                </div>
            </div>
          </div>
      </div>
      <div class="col-md-4">
        <div id="eventFilter" class="card border-primary">
        <div class="card-header">
          Event Registration
          <div class="btn-group float-right">
            <button @click="show_event_registration_filter = !show_event_registration_filter" type="button"
                    class="btn btn-transparent p-0 text-success">
              <i class="fa"
                 :class="{'fa-chevron-down': show_event_registration_filter, 'fa-chevron-left': !show_event_registration_filter}"></i>
            </button>
          </div>
        </div>
        <div class="card-body" v-if="show_event_registration_filter">
          <div class="form-group">
            <select class="form-control d-none" name="event_registration" multiple v-model="event_registration2">
              <option v-for="event in events" :value="event.id">{{ event.name }}</option>
            </select>
            <Multiselect v-model="event_registration" :multiple="true" :options="events" @input="event_registration_change"></Multiselect>
          </div>
          <div v-if="event_registration.length" class="switch-group d-flex">
            <input v-if="event_registration_paid" class="d-none" type="checkbox" name="event_registration_paid" checked>
            <div class="mr-2">Paid Only</div>
            <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
              <input v-model="event_registration_paid" type="checkbox" class="c-switch-input">
              <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
            </label>
          </div>
          <div v-if="event_registration.length" class="switch-group d-flex">
            <input v-if="event_registration_checked_in" class="d-none" type="checkbox" name="event_registration_checked_in" checked>
            <div class="mr-2">Checked In Only</div>
            <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
              <input v-model="event_registration_checked_in" type="checkbox" class="c-switch-input">
              <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
            </label>
          </div>
          <div v-if="event_registration.length" class="switch-group d-flex">
            <input v-if="event_registration_released_ticket" class="d-none" type="checkbox" name="event_registration_released_ticket" checked>
            <div class="mr-2">Released Tickets Only</div>
            <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
              <input v-model="event_registration_released_ticket" type="checkbox" class="c-switch-input">
              <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
            </label>
          </div>
        </div>
      </div>
      </div>
      <div class="col-md-4">
        <div id="recurringDonor" class="card border-primary">
            <div class="card-header">
              Recurring Donors
              <div class="btn-group float-right">
                <button @click="show_recurring_donors_filter = !show_recurring_donors_filter" type="button"
                        class="btn btn-transparent p-0 text-success">
                  <i class="fa"
                     :class="{'fa-chevron-down': show_recurring_donors_filter, 'fa-chevron-left': !show_recurring_donors_filter}"></i>
                </button>
              </div>
            </div>
            <div class="card-body" v-if="show_recurring_donors_filter">
                <div class="form-group">
                    <div class="switch-group d-flex">
                        <input v-if="recurring_donors" class="d-none" type="checkbox" name="recurring_donors" v-model="recurring_donors2">
                        <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
                            <input v-model="recurring_donors" type="checkbox" class="c-switch-input">
                            <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                        </label>
                        <div class="ml-2">Recurring Donors <i class="fa fa-question-circle-o text-info" v-tooltip title="Has a recurring donation that has not been canceled or ran out of cycles"></i></div>
                    </div>
                    <div class="switch-group d-flex mt-3">
                        <input v-if="ex_recurring_donors" class="d-none" type="checkbox" name="ex_recurring_donors" v-model="ex_recurring_donors2">
                        <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
                            <input v-model="ex_recurring_donors" type="checkbox" class="c-switch-input">
                            <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                        </label>
                        <div class="ml-2">Ex Recurring Donors <i class="fa fa-question-circle-o text-info" v-tooltip title="Had a recurring donation at one time but no longer does"></i></div>
                    </div>
                    <div class="switch-group d-flex mt-3">
                        <input v-if="non_recurring_donors" class="d-none" type="checkbox" name="non_recurring_donors" v-model="non_recurring_donors2">
                        <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
                            <input v-model="non_recurring_donors" type="checkbox" class="c-switch-input">
                            <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                        </label>
                        <div class="ml-2">Non Recurring Donors <i class="fa fa-question-circle-o text-info" v-tooltip title="Never had a recurring donation"></i></div>
                    </div>
                </div>
            </div>
          </div>
      </div>
      <div class="col-md-4">
        <div id="latentDonor" class="card border-primary">
            <div class="card-header">
                Latent Givers
                <div class="btn-group float-right">
                    <button @click="show_latent_donors_filter = !show_latent_donors_filter" type="button" class="btn btn-transparent p-0 text-success">
                        <i class="fa" :class="{'fa-chevron-down': show_latent_donors_filter, 'fa-chevron-left': !show_latent_donors_filter}"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" v-if="show_latent_donors_filter">
                <label>Date range for the <b>NON</b> giving period</label>
                <div class="row">
                    <div class="form-group mr-2">
                        <input v-model="dates.latent_not_give_from_date" @change="drawTable" name="latent_not_give_from_date" id="latent_not_give_from_date" class="form-control w-100" type="date">
                    </div>

                    <div class="form-group">
                        <input v-model="dates.latent_not_give_to_date" @change="drawTable" name="latent_not_give_to_date" id="latent_not_give_to_date" class="form-control w-100" type="date">
                    </div>
                </div>
                
                <label>Date range for the date range they gave</label>
                <div class="row">
                    <div class="form-group mr-2">
                        <input v-model="dates.latent_gave_from_date" @change="drawTable" name="latent_gave_from_date" id="latent_gave_from_date" class="form-control w-100" type="date">
                    </div>

                    <div class="form-group">
                        <input v-model="dates.latent_gave_to_date" @change="drawTable" name="latent_gave_to_date" id="latent_gave_to_date" class="form-control w-100" type="date">
                    </div>
                </div>
            </div>
          </div>
      </div>
      <div class="col-md-4">
        <div id="primaryContact" class="card border-primary">
            <div class="card-header">
                Primary Contacts
                <div class="btn-group float-right">
                    <button @click="show_primary_contacts_filter = !show_primary_contacts_filter" type="button" class="btn btn-transparent p-0 text-success">
                        <i class="fa" :class="{'fa-chevron-down': show_primary_contacts_filter, 'fa-chevron-left': !show_primary_contacts_filter}"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" v-if="show_primary_contacts_filter">
                <div class="form-group">
                    <div class="switch-group d-flex">
                        <input v-if="primary_contacts" class="d-none" type="checkbox" name="primary_contacts" v-model="primary_contacts2">
                        <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
                            <input v-model="primary_contacts" type="checkbox" class="c-switch-input">
                            <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                        </label>
                        <div class="ml-2">Primary Contacts Only <i class="fa fa-question-circle-o text-info" v-tooltip title="Search only for primary contacts. You will search only for contacts that are primary contacts in their fmailies or contacts that are not in a family."></i></div>
                    </div>
                </div>
            </div>
          </div>
      </div>
      <div class="col-md-4">
        <div id="contactDates" class="card border-primary">
            <div class="card-header">
                Created/Updated Date
                <div class="btn-group float-right">
                    <button @click="show_contact_date_filter = !show_contact_date_filter" type="button" class="btn btn-transparent p-0 text-success">
                        <i class="fa" :class="{'fa-chevron-down': show_contact_date_filter, 'fa-chevron-left': !show_contact_date_filter}"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" v-if="show_contact_date_filter">
                <label>Created Date</label>
                <div class="row">
                    <div class="form-group mr-2">
                        <input v-model="dates.created_at_from" @change="drawTable" name="created_at_from" id="created_at_from" class="form-control w-100" type="date">
                    </div>

                    <div class="form-group">
                        <input v-model="dates.created_at_to" @change="drawTable" name="created_at_to" id="created_at_to" class="form-control w-100" type="date">
                    </div>
                </div>
                
                <label>Updated Date</label>
                <div class="row">
                    <div class="form-group mr-2">
                        <input v-model="dates.updated_at_from" @change="drawTable" name="updated_at_from" id="updated_at_from" class="form-control w-100" type="date">
                    </div>

                    <div class="form-group">
                        <input v-model="dates.updated_at_to" @change="drawTable" name="updated_at_to" id="updated_at_to" class="form-control w-100" type="date">
                    </div>
                </div>
            </div>
          </div>
      </div>
      <div class="col-md-4">
        <div id="contactType" class="card border-primary">
            <div class="card-header">
                Person or Organization
                <div class="btn-group float-right">
                    <button @click="show_contact_type_filter = !show_contact_type_filter" type="button" class="btn btn-transparent p-0 text-success">
                        <i class="fa" :class="{'fa-chevron-down': show_contact_type_filter, 'fa-chevron-left': !show_contact_type_filter}"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" v-if="show_contact_type_filter">
                <select class="form-control" name="contact_type" v-model="contact_type" @change="drawTable">
                    <option value="all">All</option>
                    <option value="person">Person</option>
                    <option value="organization">Organization</option>
                </select>
            </div>
          </div>
      </div>
      <div class="col-md-4" v-if="custom_fields.length > 0">
        <div id="customFields" class="card border-primary">
            <div class="card-header">
                Custom Fields
                <div class="btn-group float-right">
                    <button @click="show_custom_fields_filter = !show_custom_fields_filter" type="button" class="btn btn-transparent p-0 text-success">
                        <i class="fa" :class="{'fa-chevron-down': show_custom_fields_filter, 'fa-chevron-left': !show_custom_fields_filter}"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" v-if="show_custom_fields_filter">
                <div class="form-group">
                    <select name="custom_field" class="form-control d-none" v-model="custom_field">
                        <option value=""></option>
                        <option v-for="option in custom_fields" :value="option.id">{{ option.name }}</option>
                    </select>
                    <Multiselect :multiple="false" :options="custom_fields" @input="custom_fields_change" :show-labels="false"
                                 placeholder="Select Custom Field"
                                 v-model="selected_custom_field">
                        <template slot="option" slot-scope="props">
                            <div class="option__desc"><b>{{ props.option.name }}</b></div>
                        </template>
                    </Multiselect>
                </div>
                
                <div class="form-group" v-if="custom_field">
                    <input v-model="custom_field_value" @input="custom_field_value_change" name="custom_field_value" id="custom_field_value" class="form-control w-100" type="text" placeholder="Enter value for custom field">
                </div>
            </div>
          </div>
      </div>
      <div class="col-md-12">
        <div class="card border-primary column_filtering_wrapper">
          <div class="card-header">Filter By Column
            <div class="btn-group float-right">
              <button @click="col_filter_status = !col_filter_status" type="button"
                      class="btn btn-transparent p-0 text-success">
                <i class="fa"
                   :class="{'fa-chevron-down': col_filter_status, 'fa-chevron-left': !col_filter_status}"></i>
              </button>
            </div>
          </div>
          <div class="card-body" :class="{'d-none': !col_filter_status}">
            <div id="column_filtering" class="row">
              <div v-for="col in columns" :key="col.index" class="col-xl-3 col-md-4 col-sm-6 mb-2">
                <input v-if="!col.blank && !col.no_blank" class="form-control form-control-sm" @keyup="searchColumn(col)" v-model="col.value" :placeholder="col.text">
                <input v-else-if="col.blank" :value="'Only Blanks'" class="form-control form-control-sm" disabled>
                <input v-else-if="col.no_blank" :value="'Removed Blanks'" class="form-control form-control-sm" disabled>
                <div class="m-1 d-flex text-right">
                    <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
                        <input type="checkbox" class="c-switch-input" v-model="col.blank" :value="col.name" @change="searchColumn(col, 'yes')">
                        <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                    </label>
                  <div class="mr-2" style="font-size: smaller">&nbsp;{{ col.text }} (Only Blanks)</div>
                </div>
                <div class="m-1 d-flex text-right">
                    <label class="c-switch-sm mb-0 c-switch-label c-switch-success">
                        <input type="checkbox" class="c-switch-input" v-model="col.no_blank" :value="col.name" @change="searchColumn(col, 'no')">
                        <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                    </label>
                  <div class="mr-2" style="font-size: smaller">&nbsp;{{ col.text }} (No Blanks)</div>
                </div>
              </div>
            </div>
          </div>
          <select class="d-none" name="column_blanks" multiple v-model="column_blanks">
            <option v-for="option in columns" :value="option.name">{{ option.name }}</option>
          </select>
          <select class="d-none" name="column_no_blanks" multiple v-model="column_no_blanks">
            <option v-for="option in columns" :value="option.name">{{ option.name }}</option>
          </select>
        </div>
      </div>
    </form>
  </div>
</template>
<script>

import Multiselect from "./mp/multiselect";
import TagsMultiSelect from "./mp/tags-multiselect";

export default {
  props: ['purposes', 'folders','campaigns','events', 'permissions', 'groups', 'custom_fields'],
  computed: {
      canViewTransactions() {
          return this.permissions['transaction-view']
      }
  },
  components: {Multiselect, TagsMultiSelect},
  directives: {
    'tooltip': function (el, binding) {
        $(el).tooltip({
            title: binding.value,
            placement: binding.arg,
            trigger: 'hover'             
        });
    }
  },
  async mounted() {
    let datatable = $('#dataTableBuilder').dataTable();
    let that = this
    let resetFilter = this.resetFilters;
    window.LaravelDataTables['dataTableBuilder'].on('buttons-action', function (e, buttonApi, dataTable, node, config) {
      if (buttonApi.text().includes('Reset')) resetFilter();
    });
    this.purposesWithParent = this.purposes.map(purpose => {
      let purpose_in = {id: purpose.id, name: purpose.name, parent: null}
      if (purpose.parent_purpose) purpose_in.parent = purpose.parent_purpose.name;
      return purpose_in;
    })
    let {data} = await axios.get(`/crm/search/contacts/state/${(new URL(window.location.href)).searchParams.get('state_id')}`)
    let column_blanks = !_.isEmpty(data) ? !_.isUndefined(data.search.column_blanks) ? data.search.column_blanks : [] : [];
    let column_no_blanks = !_.isEmpty(data) ? !_.isUndefined(data.search.column_no_blanks) ? data.search.column_no_blanks : [] : [];
    datatable.api().columns().every(function (val) {
      if (!['link', 'tags', 'preferred_name', 'family_name', 'family_envelope_name', 'merged_name', 'created_at', 'updated_at', 'notes', 'background_info'].includes(this.dataSrc())) {
        if (!_.isEmpty(data) && !_.isEmpty(data.columns) && !_.isEmpty(data.columns[val]) && data.columns[val].search.search) this.search(data.columns[val].search.search)
        that.columns.push({
          name: this.dataSrc(),
          index: val,
          text: this.column(val).header().innerHTML,
          value: (!_.isEmpty(data) && !_.isEmpty(data.columns) && !_.isEmpty(data.columns[val])) ? data.columns[val].search.search : '',
          blank: column_blanks.includes(this.dataSrc()),
          no_blank: column_no_blanks.includes(this.dataSrc())
        });
      }
    });
    if (!_.isEmpty(data)) {
      let search = data.search
      if (search.transaction_date_min) {
        this.dates.start_date = search.transaction_date_min;
      }if (search.transaction_date_max) {
        this.dates.end_date = search.transaction_date_max;
      }if (search.transaction_amount_min) {
        this.money.a = search.transaction_amount_min;
      }if (search.transaction_amount_max) {
        this.money.b = search.transaction_amount_max;
      }if (search.transaction_purposes) {
        this.selected_purposes2 = search.transaction_purposes
        search.transaction_purposes.forEach(purposes_id => {
          let purpose_in = this.purposesWithParent.find(p => p.id == purposes_id)
          this.selected_purposes.push(purpose_in)
        })
      }if (search.transaction_campaigns) {
        this.selected_campaigns2 = search.transaction_campaigns
        search.transaction_campaigns.forEach(purposes_id => {
          let purpose_in = this.campaigns.find(p => p.id == purposes_id)
          this.selected_campaigns.push(purpose_in)
        })
      }
      if (search.contact_excluded_tags) {
        this.excluded_tag2 = search.contact_excluded_tags
        search.contact_excluded_tags.forEach(tag_id => {
          let tag_in = this.folders.find(t => t.id == tag_id)
          this.excluded_tag.push(tag_in)
        })
      }
      if (search.contact_tags) {
        this.included_tags2 = search.contact_tags
        search.contact_tags.forEach(tag_id => {
          let tag_in = this.folders.find(t => t.id == tag_id)
          this.included_tags.push(tag_in)
        })
      }
      if (search.transaction_tags) {
        this.included_transaction_tags2 = search.transaction_tags
        search.transaction_tags.forEach(tag_id => {
          let tag_in = this.folders.find(t => t.id == tag_id)
          this.included_transaction_tags.push(tag_in)
        })
      }
      if (search.groups) {
        this.selected_groups2 = search.groups
        search.groups.forEach(group_id => {
          let group_in = this.groups.find(g => g.id == group_id)
          this.selected_groups.push(group_in)
        })
      }
      if (search.event_registration) {
            search.event_registration.forEach(ids => {
                let ev = this.events.find(e => e.id == ids || e.id.split(',').includes(ids))
                this.event_registration.push(ev)
                this.event_registration2.push(ev.id)
            })
      }
      if (search.event_registration_paid) this.event_registration_paid = true
      if (search.event_registration_checked_in) this.event_registration_checked_in = true
      if (search.event_registration_released_ticket) this.event_registration_released_ticket = true
      if (search.recurring_donors) {
          this.recurring_donors = true
          this.recurring_donors2 = true
      }
      if (search.ex_recurring_donors) {
          this.ex_recurring_donors = true
          this.ex_recurring_donors2 = true
      }
      if (search.non_recurring_donors) {
          this.non_recurring_donors = true
          this.non_recurring_donors2 = true
      }
      if (search.latent_not_give_from_date) {
        this.dates.latent_not_give_from_date = search.latent_not_give_from_date;
      }
      if (search.latent_not_give_to_date) {
        this.dates.latent_not_give_to_date = search.latent_not_give_to_date;
      }
      if (search.latent_gave_from_date) {
        this.dates.latent_gave_from_date = search.latent_gave_from_date;
      }
      if (search.latent_gave_to_date) {
        this.dates.latent_gave_to_date = search.latent_gave_to_date;
      }
      if (search.primary_contacts) {
          this.primary_contacts = true
          this.primary_contacts2 = true
      }
      if (search.created_at_from) {
        this.dates.created_at_from = search.created_at_from;
      }
      if (search.created_at_to) {
        this.dates.created_at_to = search.created_at_to;
      }
      if (search.updated_at_from) {
        this.dates.updated_at_from = search.updated_at_from;
      }
      if (search.updated_at_to) {
        this.dates.updated_at_to = search.updated_at_to;
      }
      if (search.contact_type) {
        this.contact_type = search.contact_type;
      }
      if (search.custom_field) {
        this.custom_field = search.custom_field
        this.custom_field_value = search.custom_field_value
        this.selected_custom_field = this.custom_fields.find(c => c.id == search.custom_field)
      }
      
      let state = data
      window.LaravelDataTables['dataTableBuilder'].columns().every(function (val) {
        if (state && !_.isEmpty(state.columns)) {
          let columns = state.columns.map(col => !!col.search.search)
          that.col_filter_status = columns.some(col => col === true) || !!column_blanks.length || !!column_no_blanks.length
        }
      })
      this.drawTable();
    }

  },
  data() {
    return {
      column_blanks: [],
      column_no_blanks: [],
      columns: [],
      included_tags2: [],
      included_tags: [],
      excluded_tag2: [],
      excluded_tag: [],
      included_transaction_tags2: [],
      included_transaction_tags: [],
      selected_purposes: [],
      selected_purposes2: [],
      selected_campaigns: [],
      selected_campaigns2: [],
      selected_groups: [],
      selected_groups2: [],
      purposesWithParent: [],
      event_registration:[],
      event_registration2:[],
      event_registration_paid:false,
      event_registration_checked_in:false,
      event_registration_released_ticket:false,
      recurring_donors: false,
      recurring_donors2: false,
      ex_recurring_donors: false,
      ex_recurring_donors2: false,
      non_recurring_donors: false,
      non_recurring_donors2: false,
      primary_contacts: false,
      primary_contacts2: false,
      dates: {
        start_date: '',
        end_date: '',
        latent_not_give_from_date: '',
        latent_not_give_to_date: '',
        latent_gave_from_date: '',
        latent_gave_to_date: '',
        created_at_from: '',
        created_at_to: '',
        updated_at_from: '',
        updated_at_to: ''
      },
      money: {
        a: '',
        b: '',
      },
      show_date_filter: false,
      show_money_filter: false,
      show_purpose_filter: false,
      show_campaigns_filter: false,
      show_include_tag_filter: false,
      show_exclude_tag_filter: false,
      show_include_transaction_tag_filter: false,
      show_group_filter: false,
      show_event_registration_filter: false,
      show_recurring_donors_filter: false,
      show_latent_donors_filter: false,
      show_primary_contacts_filter: false,
      show_contact_date_filter: false,
      show_contact_type_filter: false,
      show_custom_fields_filter: false,
      contact_type: 'all',
      selected_custom_field: '',
      custom_field: '',
      custom_field_value: '',
      col_filter: [],
      col_filter_status: false
    }
  },
  methods: {
    searchColumn: _.debounce(function (col, blanks) {
        if (blanks === 'yes' && col.blank) {
            col.value = '';
            col.no_blank = false;
            if (this.column_no_blanks.indexOf(col.name) > -1) {
                this.column_no_blanks.splice(this.column_no_blanks.indexOf(col.name), 1);
            }
            setTimeout(function () {
                window.LaravelDataTables['dataTableBuilder'].column(col.index).search(col.value, false, false, true).draw();
            }, 250)
        } else if (blanks === 'no' && col.no_blank) {
            col.value = '';
            col.blank = false;
            if (this.column_blanks.indexOf(col.name) > -1) {
                this.column_blanks.splice(this.column_blanks.indexOf(col.name), 1);
            }
            setTimeout(function () {
                window.LaravelDataTables['dataTableBuilder'].draw()
            }, 250)
        } else {
            window.LaravelDataTables['dataTableBuilder'].column(col.index).search(col.value, false, false, true).draw();
        }
    }, 400),
    purposes_change(value, id) {
      this.selected_purposes2 = this.selected_purposes.map(p => p.id)
      this.drawTable()
    },
    campaigns_change(value, id) {
      this.selected_campaigns2 = this.selected_campaigns.map(p => p.id)
      this.drawTable()
    },
    included_tags_change(value, id) {
      this.included_tags2 = this.included_tags.map(p => p.id)
      this.drawTable()
    },
    excluded_tags_change(value, id) {
      this.excluded_tag2 = this.excluded_tag.map(p => p.id)
      this.drawTable()
    },
    included_transaction_tags_change(value, id) {
      this.included_transaction_tags2 = this.included_transaction_tags.map(p => p.id)
      this.drawTable()
    },
    groups_change(value, id) {
      this.selected_groups2 = this.selected_groups.map(p => p.id)
      this.drawTable()
    },
    custom_fields_change(value, id) {
      this.custom_field = value.id
      this.custom_field_value = null;
    },
    custom_field_value_change() {
      this.drawTable()
    },
    drawTable:
        _.debounce(function () {
          window.LaravelDataTables['dataTableBuilder'].draw()
        }, 400),
    resetFilters() {
      this.included_tags2 = [];
      this.included_tags = [];
      this.excluded_tag2 = [];
      this.excluded_tag = [];
      this.included_transaction_tags2 = [];
      this.included_transaction_tags = [];
      this.selected_purposes = [];
      this.selected_purposes2 = [];
      this.selected_campaigns = [];
      this.selected_campaigns2 = [];
      this.selected_groups = [];
      this.selected_groups2 = [];
      this.column_blanks = [];
      this.column_no_blanks = [];
      this.columns.forEach(col => {
        col.blank = false
        col.no_blank = false
        col.value = ''
      });
      this.event_registration = []
      this.event_registration2 = []
      this.event_registration_paid = false
      this.event_registration_checked_in = false
      this.event_registration_released_ticket = false
      this.recurring_donors = false
      this.recurring_donors2 = false
      this.ex_recurring_donors = false
      this.ex_recurring_donors2 = false
      this.non_recurring_donors = false
      this.non_recurring_donors2 = false
      this.primary_contacts = false
      this.primary_contacts2 = false
      this.dates.start_date = null;
      this.dates.end_date = null;
      this.money.a = null;
      this.money.b = null;
      this.dates.latent_not_give_from_date = null;
      this.dates.latent_not_give_to_date = null;
      this.dates.latent_gave_from_date = null;
      this.dates.latent_gave_to_date = null;
      this.show_primary_contacts_filter = null;
      this.dates.created_at_from = null;
      this.dates.created_at_to = null;
      this.dates.updated_at_from = null;
      this.dates.updated_at_to = null;
      this.contact_type = 'all';
      this.selected_custom_field = null;
      this.custom_field = null;
      this.custom_field_value = null;
    },
    event_registration_change(value,id){
      this.event_registration2 = this.event_registration.map(p => p.id)
      this.drawTable()
    }
  },
  watch: {
    event_registration_checked_in(){
      this.drawTable();
    },
    event_registration_released_ticket(){
      this.drawTable();
    },
    event_registration_paid(){
      this.drawTable();
    },
    selected_campaigns2() {
      this.show_campaigns_filter = !!this.selected_campaigns2.length
    },
    selected_purposes2() {
      this.show_purpose_filter = !!this.selected_purposes2.length
    },
    excluded_tag2() {
      this.show_exclude_tag_filter = !!this.excluded_tag2.length
    },
    included_tags2() {
      this.show_include_tag_filter = !!this.included_tags2.length
    },
    included_transaction_tags2() {
      this.show_include_transaction_tag_filter = !!this.included_transaction_tags2.length
    },
    selected_groups2() {
      this.show_group_filter = !!this.selected_groups2.length
    },
    event_registration2() {
      this.show_event_registration_filter = !!this.event_registration2.length
    },
    recurring_donors() {
      this.drawTable();
    },
    recurring_donors2() {
      this.show_recurring_donors_filter = !!this.recurring_donors2
    },
    ex_recurring_donors() {
      this.drawTable();
    },
    ex_recurring_donors2() {
      this.show_recurring_donors_filter = !!this.ex_recurring_donors2
    },
    non_recurring_donors() {
      this.drawTable();
    },
    non_recurring_donors2() {
      this.show_recurring_donors_filter = !!this.non_recurring_donors2
    },
    primary_contacts() {
      this.drawTable();
    },
    primary_contacts2() {
      this.show_primary_contacts_filter = !!this.primary_contacts2
    },
    contact_type() {
      this.show_contact_type_filter = !!this.contact_type
    },
    custom_field() {
      this.show_custom_fields_filter = !!this.custom_field
    },
    dates: {
      handler() {
        this.show_date_filter = !!(this.dates.start_date || this.dates.end_date);
        this.show_latent_donors_filter = !!(this.dates.latent_not_give_from_date || this.dates.latent_not_give_to_date || this.dates.latent_gave_from_date || this.dates.latent_gave_to_date);
        this.show_contact_date_filter = !!(this.dates.created_at_from || this.dates.created_at_to || this.dates.updated_at_from || this.dates.updated_at_to);
      },
      deep: true
    },
    money: {
      handler() {
        this.show_money_filter = !!(this.money.a || this.money.b);
      },
      deep: true
    },
    columns: {
      handler() {
        this.column_blanks = this.columns.filter(col => col.blank).map(col => col.name);
        this.column_no_blanks = this.columns.filter(col => col.no_blank).map(col => col.name);
      },
      deep: true
    },
  }
}
</script>
<style scoped>

</style>
