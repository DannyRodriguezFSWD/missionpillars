<template>
    <div class="card-body">
        <div class="row">

        </div>
        <div class="row mb-2">
            <div class="col-md-2">
                <button v-if="canCreate" class="btn btn-primary" @click.prevent="showCreateFundTransferModal">Create Fund Transfer</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                  <vuetable ref="vuetable"
                            :api-url="apiUrl"
                            :fields="fields"
                            :sort-order="sortOrder"
                            :multi-sort="true"
                            data-path="data"
                            pagination-path=""
                            @vuetable:loading="() => $store.dispatch('setIsLoadingState', true)"
                            @vuetable:loaded="() => $store.dispatch('setIsLoadingState', false)"
                            @vuetable:pagination-data="data => {
                        $refs.pagination.setPaginationData(data)
                        $refs.paginationInfo.setPaginationData(data)
                      }"
                            @vuetable:cell-clicked="onCellClicked"
                  >
                  </vuetable>
                </div>
              <div class="vuetable-pagination text-center">
                <vuetable-pagination ref="pagination"
                                     @vuetable-pagination:change-page="page => $refs.vuetable.changePage(page)"
                ></vuetable-pagination>
                <vuetable-pagination-info ref="paginationInfo" info-class="pagination-info">
                </vuetable-pagination-info>
              </div>
            </div>
        </div>
        <accounting-fund-transfer-entries v-bind:maxjournalid="maxjournalid" v-bind:groups="groups" v-bind:funds="funds" :permissions="permissions"></accounting-fund-transfer-entries>
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>
<style>
.registersRowClass {
    cursor: pointer;
}
.registersRowClass:hover {
    background-color: #eceff1;
}
</style>
<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import loading from '../Loading.vue'
import Vuetable from '../MpVueTable/MpVueTable'
import VuetablePagination from '../MpVueTable/MpVueTablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
export default {
    components: {
        loading,
        Vuetable,
        VuetablePagination,
        VuetablePaginationInfo
    },
    mounted () {
      if (doCreate) this.showCreateFundTransferModal();
    },
    created () {
    },
    props: [
        'maxjournalid',
        'journalentry',
        'groups',
        'funds',
        'permissions'
    ],
    data () {
        return {
            apiUrl: '/accounting/registers/table?type=fund_transfer',
            splits: [],
            modal_data: '',
            table_data: this.journalentry,
            type: '',
            fields: [
                {
                    name: 'journal entry #',
                    title: 'Fund Transfer #',
                    sortField: 'id',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                },
                {
                    name: 'date',
                    sortField: 'date',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                },
                {
                    name: 'memo',
                    sortField: 'memo',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                },
                {
                    title: 'Type',
                    name: 'comment',
                    sortField: 'comment',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                }
            ],
            sortOrder: [
                { field: 'date', sortField: 'date', direction: 'desc'}
            ],
        }
    },
    computed: {
        ...mapGetters('JournalEntries', [
            'getCurrentRecord',
        ]),
        ...mapGetters([
            'getIsLoadingState',
        ]),
        ...mapState([

        ]),
        canCreate() {
            return this.permissions['accounting-create']
        },
    },
    methods: {
        ...mapActions('JournalEntries', [
            'setCurrentRecord',
        ]),
        ...mapActions([
            'post',
            'get',
            'put',
            'destroy'
        ]),
        showEditModal (data) {
            if (data.register_type === 'journal_entry') {
                axios.get('/accounting/registers/getSplits', {
                        params: {
                            register_id: data.id
                        }
                    })
                    .then((res) => {
                        var r = []
                        this.modal_data = res.data
                        $.each(res.data.splits, function(index, s) {
                            r.push(s);
                        })
                        this.splits = r;
                        this.type = 'je'
                        $('#edit-je-modal').modal('show')
                    })
                    .catch((err) => {
                        console.log(err)
                    })
                    this.$root.$emit('calcTotal')
            } else if (data.register_type === 'fund_transfer') {
                this.modal_data = data
                this.type = 'fund_transfer'
                $('#edit-ft-modal').modal('show')
            }
        },
        showCreateFundTransferModal() {
            this.$nextTick(function() {
                $('#create-fund-transfers-modal').modal('show')
            })
        },
        apiUri() {
            this.apiUrl ='/accounting/registers/table?type=fund_transfer'
            this.enableCreate = false
        },
        onPaginationData() {

        },
        onChangePage() {

        },
        onCellClicked(data, field, event) {
            this.get({
                url: '/accounting/registers/getSplits',
                data: {
                    register_id: data.register_id,
                    register_type: 'fund_transfer'
                }
            }).then(result => {
                // console.log(result.data)
                this.setCurrentRecord(result.data)
                this.type = 'fund_transfer'
                $('#create-fund-transfers-modal').modal('show')
            }).catch(error => {
                console.log(error)
            })
        },
    }
}
</script>
