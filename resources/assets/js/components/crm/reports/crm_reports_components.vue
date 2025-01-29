<template>
    <div id="reports">
        <div v-if="reportsList == 'hide'" class="card-header">
            <div class="text-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle mt-1" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-upload" aria-hidden="true"></i>
                        Export <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" :href="download+'&format=pdf'">
                            PDF Format .pdf
                        </a>
                        <a class="dropdown-item" :href="download+'&format=excel'">
                            EXCEL Format .xlsx
                        </a>
                    </div>
                </div>
                
                <button id="edit" class="btn btn-primary mt-1" type="button" v-on:click="run('settings', settings.id)">
                    <i class="fa fa-cog"></i>
                    Edit
                </button>
            </div>
        </div>
        
        <div class="card table-responsive" v-if="reportsList == 'show'">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Report</th>
                        <th>Category</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(report, index) in reports">
                        <td>
                            <button class="btn btn-link pl-0 pr-0" type="button" v-on:click="run('reports', index)" data-toggle="modal" data-target="#show-report-settings">
                                {{ report.name }}
                            </button>
                        </td>
                        <td>{{ report.category }}</td>
                        <td>{{ report.description }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="card-footer">&nbsp;</div>
        </div>

        <div class="modal fade" id="show-report-settings" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">{{ crmmodal.modal.header }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    
                    <div class="modal-body" style="max-height: calc(100vh - 235px); overflow-y: auto;}">
                        <p>{{ settings.description }}</p>
                            <label v-if="[0, 2, 3, 4].includes(settings.id)" for="">Date Range</label>
                            <label v-if="[1].includes(settings.id)" for="">First enter date range for the <strong>NON</strong> giving period.</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <datepicker v-model="settings.date_range.from" data-id="from" ref="from" :format="customFormatter" :bootstrap-styling="true" input-class="bg-white"
                                        ></datepicker>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <datepicker v-model="settings.date_range.to" data-id="to" ref="to" :format="customFormatter" :bootstrap-styling="true" input-class="bg-white"
                                        ></datepicker>
                                    </div>
                                </div>
                            </div>
                        
                            <label v-show="[1].includes(settings.id)" for="">Next enter date range for the date range they gave.</label>
                            <div class="row" v-show="[1].includes(settings.id)">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <datepicker v-model="settings.date_range.from2" data-id="from2" ref="from2" :format="customFormatter" :bootstrap-styling="true" input-class="bg-white"
                                        ></datepicker>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <datepicker v-model="settings.date_range.to2" data-id="to2" ref="to2" :format="customFormatter" :bootstrap-styling="true" input-class="bg-white"
                                        ></datepicker>
                                    </div>
                                </div>
                            </div>

                            <div v-if="[3, 4].includes(settings.id)">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>Amount Ranges</h6>
                                        <p>The report will have {{ settings.amount_ranges.length }} columns. You can choose the amount ranges for each column.</p>
                                    </div>
                                </div>
                                <div class="row mb-2" v-for="(item, index) in settings.amount_ranges">
                                    <div class="col-md-12">
                                        <label for="">Column {{ index+1 }}</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" :key="'min-range'+index" v-model="item.min" :min="item.min" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" :key="'max-range'+index" v-model="item.max" class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="[0, 3, 4].includes(settings.id)">
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6>Transaction Type</h6>
                                        <label for="transaction_type">Selected transaction type <b>should</b> be included in report</label>
                                        <select v-model="transaction_type" name="transaction_type" id="transaction_type" class="form-control">
                                            <option value="all">All</option>
                                            <option value="donation">Donation</option>
                                            <option value="purchase">Purchase</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                                
                            <div v-if="[3, 4].includes(settings.id)">
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6>Online / Offline</h6>
                                        <select v-model="transaction_online_offline" class="form-control" id="transaction_online_offline" name="transaction_online_offline">
                                            <option value="all">All</option>
                                            <option value="online">Online Transactions</option>
                                            <option value="offline">Offline Transactions</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6>Channel</h6>
                                        <label for="transaction_channel">Selected transaction channel <b>should</b> be included in report</label>
                                        <Multiselect :multiple="true" :options="channels" :show-labels="false" @input="channelsChange"
                                                        :close-on-select="true"
                                                        placeholder="Select Channel"
                                                        v-model="channels2">
                                        </Multiselect>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4 mt-4">
                                <div class="col-md-12">
                                    <h6>Lists</h6>
                                    <label for="">Selected list <strong>should</strong> be included in report</label>
                                    <select v-model="list" name="list" id="list" class="form-control">
                                        <option v-for="(item, index) in lists" :value="item.id">
                                            {{ item.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row" v-if="crmmodal.modal.show">
                                <div class="col-md-12">
                                    <h6>Tags</h6>
                                    <label for="">Selected tags <strong>should</strong> be included in report</label>
                                    <div style="max-height: 200px; overflow: auto; border: 1px solid rgba(0, 0, 0, 0.15);">
                                        <tree ref="inNodesTree" :data="treeData" :options="treeOptions" @node:unchecked="onInNodeUnSelected" @node:checked="onInNodeSelected">
                                            <span class="tree-text" slot-scope="{ node }">
                                                <template v-if="!node.hasChildren() && in_tags_as_array.includes(node.id.toString()) && node.check()">
                                                    <i class="fa fa-tag"></i> {{ node.text }}
                                                </template>

                                                <template v-if="!node.hasChildren() && !in_tags_as_array.includes(node.id.toString())">
                                                    <i class="fa fa-tag"></i> {{ node.text }}
                                                </template>

                                                <template v-if="node.hasChildren()">
                                                    <i :class="[node.expanded() ? 'icon-folder-alt' : 'icon-folder']"></i>
                                                    {{ node.text }}
                                                </template>
                                            </span>
                                        </tree>
                                    </div>
                                </div>
                            </div>

                            <div class="row" v-if="crmmodal.modal.show">
                                <div class="col-md-12">
                                    <label class="pt-4" for="">Selected tags <strong>shouldn't</strong> be included in report</label>
                                    <div style="max-height: 200px; overflow: auto; border: 1px solid rgba(0, 0, 0, 0.15);">
                                        <tree ref="outNodesTree" :data="treeData" :options="treeOptions" @node:unchecked="onOutNodeUnSelected" @node:checked="onOutNodeSelected">
                                            <span class="tree-text" slot-scope="{ node }">
                                                <template v-if="!node.hasChildren() && out_tags_as_array.includes(node.id.toString()) && node.check()">
                                                    <i class="fa fa-tag"></i> {{ node.text }}
                                                </template>

                                                <template v-if="!node.hasChildren() && !out_tags_as_array.includes(node.id.toString())">
                                                    <i class="fa fa-tag"></i> {{ node.text }}
                                                </template>

                                                <template v-if="node.hasChildren()">
                                                    <i :class="[node.expanded() ? 'icon-folder-alt' : 'icon-folder']"></i>
                                                    {{ node.text }}
                                                </template>
                                            </span>
                                        </tree>
                                    </div>
                                </div>
                            </div>
                    </div>
                    
                    <div class="modal-footer">
                        

                        <button id="close" class="modal-button btn btn-secondary mr-4" data-dismiss="modal">
                            Close
                        </button>
                        
                        <button class="modal-default-button btn btn-primary" v-on:click="report()" data-dismiss="modal">
                            Make report
                        </button>
                    </div>
                </div>
                
            </div>
        </div>
        
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>
<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    import loading from '../../Loading.vue'
    import CRMModal from '../../crm-modal.vue'
    import Datepicker from 'vuejs-datepicker'
    import moment, { locale, utc } from 'moment'
    import Multiselect from '../../mp/multiselect';
    // TODO uncomment this and related lines in components and mounted and use tags-multi-select-widget
    // import tagsMultiSelect from '../../mp/tags-multiselect.vue'

    
    export default {
        props: {
            reportsList: String, 
            title: String, 
            id: Number, 
            base: String, 
            from: String, 
            to: String, 
            from2: String, 
            to2: String,
            amount_ranges: Array,
            in_list: {
                type: Number,
                default: 0,
            },
            in_tags: String,
            out_tags: String,
        },
        mounted() {
            // console.log('mounted')
            // TODO uncomment this and related import line and use tags-multi-select-widget
            // this.$store.dispatch('setTagOptions')
            
            if(this.amount_ranges.length > 0){
                this.settings.amount_ranges = this.amount_ranges
            }
            this.list = this.in_list
            if(this.reportsList == 'show'){
                this.get({
                    url: this.base+'/crmreports/create',
                    data: {}
                }).then(response => {
                    this.reports = response.data
                })
            }

            this.$store.dispatch('setCurrentDate').then(result => {
                this.settings.date_range.from = result
                this.settings.date_range.to = result
                this.settings.date_range.from2 = result
                this.settings.date_range.to2 = result
            })
            
            this.download = this.base+'/crmreports/'+this.id+'?from='+this.from+'&to='+this.to+'&from2='+this.from2+'&to2='+this.to2
            +'&list='+this.list
            +'&in_tags='+this.in_tags
            +'&out_tags='+this.out_tags
            +'&amount_ranges='+JSON.stringify(this.amount_ranges)
            +'&download=true'
            
            this.get({
                url: this.base+'/folders',
                data: {

                }
            }).then(response => {
                this.treeInitialData = response.data.tree
            })

            this.get({
                url: this.base+'/lists',
                data: {}
            }).then(response => {
                this.lists = response.data
            })

            var year = new Date().getFullYear() + 4
            var component = this

            this.tags_in = this.in_tags
            this.tags_out = this.out_tags

            if(this.in_tags != ''){
                this.in_tags_as_array = this.in_tags.split(',')
            }

            if(this.out_tags != ''){
                this.out_tags_as_array = this.out_tags.split(',')
            }

            var that = this
            $('.vue-datepicker').datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: '1910:'+year,
                dateFormat: 'mm/dd/yy',
            })
        },
        components: {
            loading,
            CRMModal,
            Datepicker,
            Multiselect
            // tagsMultiSelect,
        },
        data: function(){
            return {
                download: '',
                reports: [],
                lists: [],
                list: 0,
                settings: {
                    id: null,
                    description: null,
                    date_range:{
                        from: this.getCurrentDate,
                        to: this.getCurrentDate,
                        from2: this.getCurrentDate,
                        to2: this.getCurrentDate
                    },
                    amount_ranges: [
                        {min: 0, max: 100},
                        {min: 100.01, max: 500},
                        {min: 500.01, max: 1000},
                        {min: 1000.01, max: 1000000}
                    ]
                },
                treeOptions: {
                    checkOnSelect: true,
                    checkbox: true
                },
                treeData: [],
                tags_in : [],
                tags_out : [],
                treeInitialData : [],
                loadTagsFromURL: true,
                in_tags_as_array: [],
                out_tags_as_array: [],
                transaction_type: 'all',
                transaction_channel: [],
                transaction_online_offline: 'all',
                channels: [
                    {
                        id: 'face_to_face',
                        name: 'Face to face'
                    },
                    {
                        id: 'mail',
                        name: 'Mail'
                    },
                    {
                        id: 'ncf',
                        name: 'Appreciated Stock Through NCF'
                    },
                    {
                        id: 'event',
                        name: 'Event'
                    },
                    {
                        id: 'other',
                        name: 'Other'
                    },
                    {
                        id: 'unknown',
                        name: 'Unknown'
                    },
                    {
                        id: 'ctg_direct',
                        name: 'CTG - Direct'
                    },
                    {
                        id: 'ctg_embed',
                        name: 'CTG - Website Embedded Form'
                    },
                    {
                        id: 'ctg_text_link',
                        name: 'CTG - Text For Link'
                    },
                    {
                        id: 'ctg_text_give',
                        name: 'CTG - Text To Give'
                    },
                    {
                        id: 'website',
                        name: '(deprecated) Website'
                    }
                ],
                channels2: [],
            }
        },
        computed: {
            ...mapGetters([
                'getIsLoadingState',
                'getCurrentDate'
            ]),
            ...mapState([
                'crmmodal',
            ]),
        },
        methods: {
            ...mapActions([
                'post',
                'get',
                'setIsLoadingState'
            ]),
            customFormatter(date) {
                // TODO remove
                return moment(date).format('MM/DD/YYYY');
            },
            run: function(task, index){
                if(this.reportsList == 'show'){
                    this.$store.dispatch('crmmodal/setModalHeader', this.reports[index].name)
                    this.settings.id = this.reports[index].id
                    this.settings.description = this.reports[index].description
                }
                else{
                    this.$store.dispatch('crmmodal/setModalHeader', this.title)
                    this.settings.id = this.id,
                    this.settings.date_range.from = this.from,
                    this.settings.date_range.to = this.to
                    this.settings.date_range.from2 = this.from2,
                    this.settings.date_range.to2 = this.to2
                }
                
                this.$store.dispatch('crmmodal/showModalAction', true)

                if(task == 'settings'){
                    
                    $('#show-report-settings').modal('show')
                }
                
            },
            report: function(){
                this.settings.date_range.from = this.$refs.from.value
                this.settings.date_range.to = this.$refs.to.value

                if(this.settings.id == 1){
                    this.settings.date_range.from2 = this.$refs.from2.value
                    this.settings.date_range.to2 = this.$refs.to2.value
                }

                var data = { 
                    from: this.settings.date_range.from,
                    to: this.settings.date_range.to,
                    from2: this.settings.date_range.from2,
                    to2: this.settings.date_range.to2,
                    list: this.list,
                    in_tags: this.tags_in,
                    out_tags: this.tags_out,
                    amount_ranges: JSON.stringify(this.settings.amount_ranges),
                    transaction_type: this.transaction_type,
                    transaction_channel: this.transaction_channel,
                    transaction_online_offline: this.transaction_online_offline
                }
                // change object to string values if needed
                for (const i of ['from','to','from2','to2']) {
                    if(typeof data[i] == 'object') data[i] = this.customFormatter(data[i])
                }
                
                var _url = this.base+'/crmreports/'+this.settings.id;
                var params = Object.keys(data).map(function(key) {
                    return key + '=' + data[key];
                }).join('&');
                window.location.href = _url+'?'+params

                this.get({
                    url: _url,
                    data: data
                }).then(result => {
                    this.$store.dispatch('crmmodal/showModalAction', false)
                })
            },
            showTags: function(){
                console.log(this.tags_in, this.tags_out, this.list)
            },
            onInNodeSelected(node) {
                if(node.checked()){
                    var index = this.in_tags_as_array.indexOf(node.id.toString())
                    if(index < 0){
                        this.in_tags_as_array.push(node.id.toString())
                        node.check()
                    }
                }
                this.tags_in = this.in_tags_as_array.join(',')
            },
            onInNodeUnSelected(node) {
                if(!node.checked()){
                    var index = this.in_tags_as_array.indexOf(node.id.toString())
                    if(index >= 0){
                        this.in_tags_as_array.splice(index, 1)
                        node.uncheck()
                    }
                }
                this.tags_in = this.in_tags_as_array.join(',')
            },
            onOutNodeSelected(node) {
                if(node.checked()){
                    var index = this.out_tags_as_array.indexOf(node.id.toString())
                    if(index < 0){
                        this.out_tags_as_array.push(node.id.toString())
                        node.check()
                    }
                }
                this.tags_out = this.out_tags_as_array.join(',')
            },
            onOutNodeUnSelected(node) {
                if(!node.checked()){
                    var index = this.out_tags_as_array.indexOf(node.id.toString())
                    if(index >= 0){
                        this.out_tags_as_array.splice(index, 1)
                        node.uncheck()
                    }
                }
                this.tags_out = this.out_tags_as_array.join(',')
            },
            channelsChange(value, id) {
                this.transaction_channel = this.channels2.map(p => p.id)
            }
        },
        watch: {
            treeInitialData( newData ) {
                this.treeData = newData
                // do data transformations etc
                // trigger UI refresh
                this.$forceUpdate() 
            }
        }
    }
</script>
