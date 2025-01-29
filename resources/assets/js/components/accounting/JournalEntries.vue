<template>
    <div class="card-body">
        <div class="row">
            <flash-message class="flash-message-custom"></flash-message>
        </div>
        <div class="row mb-2">
            <div class="col-md-2">
                <button v-if="canCreate" class="btn btn-primary" @click.prevent="showCreateModal">Create Journal Entry</button>
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
        <CreateJournalEntryModal v-bind:registers="register" v-bind:rows="register.splits" v-bind:maxjournalid="max_journal_entry_id" v-bind:groups="groups" v-bind:funds="funds" @flashMessage="setMessage" :permissions="permissions"></CreateJournalEntryModal>
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>

<style>
    .flash-message-custom {
        width: 100%;
        padding: 0 15px;
    }
</style>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
import loading from '../Loading.vue'
import Vuetable from '../MpVueTable/MpVueTable'
import VuetablePagination from '../MpVueTable/MpVueTablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
import CreateJournalEntryModal from './journal_entries/CreateJournalEntries'

export default {
    components: {
        Vuetable,
        VuetablePagination,
        VuetablePaginationInfo,
        CreateJournalEntryModal,
        loading
    },
    mounted() {
        if (doCreate) this.showCreateModal();
    },
    props: [
        'maxjournalid',
        'journalentry',
        'groups',
        'funds',
        'permissions',
    ],
    data () {
        return {
            max_journal_entry_id: this.maxjournalid,
            enableCreate: true,
            register: [],
            apiUrl: '',
            modal_data: '',
            table_data: this.journalentry,
            type: '',
            fields: [
                {
                    name: 'journal entry #',
                    sortField: 'id',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                },
                {
                    name: 'date',
                    sortField: 'date',
                    titleClass: 'text-center',
                    dataClass: 'text-center text-nowrap',
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
    beforeMount() {
        this.apiUri();
    },
    computed: {
        ...mapGetters([
            'getIsLoadingState',
        ]),
        ...mapState([
            'JournalEntries',
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
        ]),
        setMessage(type) {
            $( document ).find('.flash-message-custom .flash__message').remove()
            this.flash("Journal entry has been "+type+" successfully!", 'success', {
                timeout: 5000,
                important: true
            });
        },
        fetchData() {
            axios.get('/accounting/journal-entries/fetch/data')
                .then((res) => {
                    this.max_journal_entry_id = res.data.journal_entry_id
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        apiUri() {
            this.apiUrl ='/accounting/registers/table?type=journal_entry'
            this.enableCreate = false
        },
        showCreateModal() {
            this.fetchData()
            this.$nextTick(function() {
                $('#create-journal-entries-modal').modal('show')
            })
        },
        showCreateFundTransferModal() {
            this.fetchData()
            this.$nextTick(function() {
                $('#create-fund-transfers-modal').modal('show')
            })
        },
        onCellClicked(data, field, event) {
            if(data.comment == 'Journal Entry') {
                this.get({
                    url: '/accounting/registers/getSplits',
                    data: {
                        register_id: data.register_id
                    }
                }).then(result => {
                    this.register = result.data
                    this.setCurrentRecord(result.data)
                    $(document).find('.flash-message-custom .flash__message').remove()
                    $('#create-journal-entries-modal').modal('show');
                }).catch((err) => {
                    console.log(err)
                })
            }
        }
    }
}
</script>
