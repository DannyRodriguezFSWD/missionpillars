<template>
  <div class="form-group">
    <label>{{ label }} </label>
    <select @change="selectionChanged" name="dateRange" v-model="selectedRange" class="form-control"
            ref="humanDateRangeSelect">
      <option value="Select Date Range">Select Date Range</option>
      <option value="Custom">Custom</option>
      <option value="Today">Today</option>
      <option value="Yesterday">Yesterday</option>
      <option value="This Week">This Week</option>
      <option value="Last Week">Last Week</option>
      <option value="This Month">This Month</option>
      <option value="Last Month">Last Month</option>
      <option value="This Year">This Year</option>
      <option value="Last Year">Last Year</option>
    </select>
    <div v-show="selectedRange == 'Custom'" class="row mt-2">
      <div class="col-md-6">
        <div class="form-group">
          <label for="">From:</label>
          <datepicker calendar-button-icon="fa fa-calendar" :calendar-button="true" :typeable="true" @input="dateChanged" v-model="start"
                      :format="customFormatter" :bootstrap-styling="true"
                      name="start" placeholder="Choose date"></datepicker>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label for="">To:</label>
          <datepicker calendar-button-icon="fa fa-calendar" :calendar-button="true" :typeable="true" @input="dateChanged" v-model="end" :format="customFormatter"
                      :bootstrap-styling="true"
                      name="end" placeholder="Choose date"></datepicker>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import moment from "moment";
import Datepicker from 'vuejs-datepicker'

export default {
  props: {
    propSelectedRange: String, propStart:String, propEnd:String, dateFormat:{
      type:String,
      default: 'YYYY-MM-DD'
    },
    label: {
        type: String,
        default: 'Date Range:'
    }
  },
  components: {
    Datepicker
  },
  data() {
    return {
      start: '',
      end: '',
      selectedRange: 'Select Date Range',
    }
  },
  mounted() {
    if (this.propSelectedRange) {
      this.selectedRange = this.propSelectedRange
      this.selectionChanged()
    }
    if (this.propStart) this.start = this.propStart
    if (this.propEnd) this.end = this.propEnd

    if (this.propStart && this.propEnd){
      this.selectedRange = 'Custom'
      this.selectionChanged()
    }
  },
  methods: {
    dateChanged() {
      let start = this.start ? moment(this.start).format(this.dateFormat) : ''
      let end = this.end ? moment(this.end).format(this.dateFormat) : ''
      this.$emit('update:propStart', start)
      this.$emit('update:propEnd', end)
      this.$emit('dateChanged', {
        start: start,
        end: end,
      })
    },
    customFormatter(date) {
      return moment(date).format(this.dateFormat);
    },
    selectionChanged() {
      switch (this.selectedRange) {
        case 'Today':
          this.start = humanDateRange_today;
          this.end = humanDateRange_today;
          break;
        case 'Yesterday':
          this.start = humanDateRange_yesterday;
          this.end = humanDateRange_yesterday;
          break;
        case 'This Week':
          this.start = humanDateRange_this_week_start;
          this.end = humanDateRange_this_week_end;
          break;
        case 'Last Week':
          this.start = humanDateRange_last_week_start;
          this.end = humanDateRange_last_week_end;
          break;
        case 'This Month':
          this.start = humanDateRange_this_month_start;
          this.end = humanDateRange_this_month_end;
          break;
        case 'Last Month':
          this.start = humanDateRange_last_month_start;
          this.end = humanDateRange_last_month_end;
          break;
        case 'This Year':
          this.start = humanDateRange_this_year_start;
          this.end = humanDateRange_this_year_end;
          break;
        case 'Last Year':
          this.start = humanDateRange_last_year_start;
          this.end = humanDateRange_last_year_end;
          break;
        case 'Custom':
          this.start = "";
          this.end = "";
          break;
        case 'Select Date Range':
          this.start = "";
          this.end = "";
          break;
      }
      this.dateChanged()
    }
  },
}
</script>
