<template>
    <div class="resume">
        <div>
            <h3>
                This message will be sent to list:
                <span class="badge-pill badge-primary text-white">{{ summary.list.name }}</span>
            </h3>
            <div class="row justify-content-center" v-if="data.message.datatable_state_id > 0">
                <div class="col-4">
                    <h3><a :href="'/crm/search/contacts/state/'+data.message.datatable_state_id" target="_blank">View Saved Search</a></h3>
                </div>
            </div>
        </div>
        
        <hr/>
       
        <div class="row">
            <div class="col-sm-6">
                <p>Contacts who <b>will</b> receive this message</p>
                <v2-lazy-list :height="250" :data="list" :threshold="10" @reach-threshold="appendData">
                    <template slot-scope="item">
                        <span>{{item}}</span>
                    </template>
                </v2-lazy-list>
            </div>

            <div class="col-sm-6">
                <p>Contacts who <b>will not</b> receive this message</p>
                <v2-lazy-list :height="250" :data="list2" :threshold="10" @reach-threshold="appendData">
                    <template slot-scope="item">
                        <span>{{item}}</span>
                    </template>
                </v2-lazy-list>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';

    export default {
        name: 'CRMCommunicationsSummary',
        data: function(){
            return {
                list: [],
                list2: []
            };
        },
        mounted() {
            //this.list = this.summary.in;
            //this.list2 = this.summary.out;
            this.appendData();
        },
        computed: {
            ...mapState([
                'summary',
                'data'
            ])
        },
        methods: {
            ...mapMutations([
                'DATA_PAGE',
                'DATA_PAGES'
            ]),
            ...mapActions([
                'getSummary'
            ]),
            appendData: function(){
                if(this.$store.state.data.page <= this.$store.state.data.pages){
                    this.getSummary(this.$store.state).then((data)=>{
                        var inList = this.list;
                        var outList = this.list2;
                        this.list = inList.concat(data.in);
                        this.list2 = outList.concat(data.out);
                        this.DATA_PAGE();
                        this.DATA_PAGES(data.pages);
                    });
                }
            }
        }
    }
</script>

<style>
.lazy-list-item{
    height: auto!important;
    padding: 10px !important;
}
</style>
