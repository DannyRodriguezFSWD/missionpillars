<template>
    <div class="tasks-list">
        <div class="card-deck mb-3">
            <div class="card">
                <div class="card-body p-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-tasks p-4 font-2xl mr-3"></i>
                        <div>
                            <div class="text-value-sm">{{ totalTasks }}</div>
                            <div class="text-muted text-uppercase font-weight-bold small">
                                Total Tasks
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                      
                        <button @click="exportToCsv" class="btn btn-success mr-2">
                            <i class="fa fa-download"></i> Export CSV
                        </button>
                        <button class="btn btn-primary mr-2" @click="openAddTaskModal">
                            <i class="fa fa-check-square"></i>
                            Add Task
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between pb-2 flex-wrap">
              
                         <div class="form-group m-0 filter-group">
                            <multiselect 
                                v-model="selectedOwners"
                                :options="owners"
                                :multiple="true"
                                :close-on-select="false"
                                :clear-on-select="false"
                                placeholder="Filter by owners"
                                track-by="id"
                                label="first_name"
                                @input="onOwnersChange"
                            >
                                <template slot="option" slot-scope="{option}">
                                    <template v-if="option">
                                        {{ option.first_name || '' }} {{ option.last_name || '' }}
                                    </template>
                                </template>
                                <template slot="selection" slot-scope="{values, option}">
                                    <template v-if="option">
                                        {{ option.first_name || '' }} {{ option.last_name || '' }}
                                    </template>
                                </template>
                                <template slot="tag" slot-scope="{option, remove}">
                                    <span class="multiselect__tag" v-if="option">
                                        <span>{{ option.first_name || '' }} {{ option.last_name || '' }}</span>
                                        <i aria-hidden="true" tabindex="1" class="multiselect__tag-icon" @click="remove(option)"></i>
                                    </span>
                                </template>
                            </multiselect>
                        </div>

                        <div class="input-group m-0 filter-group">
                        <input 
                            type="text" 
                            class="form-control search-input" 
                            v-model="searchQuery"
                            placeholder="Search tasks..."
                            @keyup="handleSearchKeyup"
                        >
                        <div class="input-group-append" v-if="searchQuery">
                            <button 
                                class="btn btn-outline-secondary reset-search" 
                                type="button"
                                @click="resetSearch"
                            >
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Filter buttons -->
                    <div class="filter-buttons">
                        <div class="btn-group m-0">
                            <button 
                                v-for="filter in filters" 
                                :key="filter.value"
                                class="btn" 
                                :class="[activeFilter === filter.value ? 'btn-primary' : 'btn-outline-primary']"
                                @click="handleFilterChange(filter.value)">
                                {{ filter.label }}
                            </button>
                            <button 
                                class="btn" 
                                :class="[activeFilter === 'custom' ? 'btn-primary' : 'btn-outline-primary']"
                                @click="showCustomDatePicker = true">
                                Custom
                            </button>
                        </div>
                        <div v-if="showCustomDatePicker" class="mt-2 text-right">
                            <input type="date" v-model="customStartDate" class="mr-2">
                            <input type="date" v-model="customEndDate" class="mr-2">
                            <button class="btn btn-sm btn-primary" @click="applyCustomFilter">Apply</button>
                            <button class="btn btn-sm btn-secondary ml-2" @click="showCustomDatePicker = false">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body mb-0 pt-0">
                <div class="table-responsive">
                    <mp-vue-table
                        ref="vuetable"
                        :api-url="filteredEndpoint"
                        :fields="fields"
                        data-path="tasks.data"
                        pagination-path="tasks"
                        @vuetable:pagination-data="onPaginationData"
                        @vuetable:loading="onTableLoading"
                        @vuetable:loaded="onTableLoaded">

                        <template slot="actions" slot-scope="props">
                            <p class="pb-0 mb-0">
                                <a class="text-primary" 
                                   style="cursor: pointer;"
                                   @click="openEditModal(props.rowData)">
                                    <span class="fa fa-edit"></span>
                                    Edit or Complete Task
                                </a>
                            </p>
                        </template>

                        <template slot="status" slot-scope="props">
                            <span class="badge" :class="getStatusClass(props.rowData.status)">
                                {{ props.rowData.status }}
                            </span>
                        </template>

                        <template slot="due_date" slot-scope="props">
                            {{ formatDate(props.rowData.due) }}
                        </template>

                        <template slot="assignee" slot-scope="props">
                            <template v-if="props.rowData.assigned_to">
                                {{ props.rowData.assigned_to.first_name }} {{ props.rowData.assigned_to.last_name }}
                                ({{ props.rowData.assigned_to.email_1 }})
                            </template>
                            <template v-else>
                                Unassigned
                            </template>
                        </template>

                        <template slot="linked_to" slot-scope="props">
                            <template v-if="props.rowData.linked_to">
                                {{ props.rowData.linked_to.first_name }} {{ props.rowData.linked_to.last_name }}
                                ({{ props.rowData.linked_to.email_1 }})
                            </template>
                            <template v-else>
                                Not Linked
                            </template>
                        </template>

                    </mp-vue-table>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="vuetable-pagination text-center">
                                <VueTablePagination ref="pagination"
                                    @vuetable-pagination:change-page="changePage"
                                ></VueTablePagination>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <loading v-if="getIsLoadingState"></loading>

        <!-- Add modals -->
        <task-edit-modal
            v-for="task in tasks"
            :key="task.id"
            :task="task"
            @taskUpdated="refreshTable"
        ></task-edit-modal>
    </div>
</template>

<script>
import VueTablePagination from "../MpVueTable/MpVueTablePagination"
import moment from 'moment'
import Multiselect from '../mp/multiselect'
import { mapState, mapGetters } from 'vuex'
import TaskEditModal from './TaskEditModal.vue'


export default {
    components: {
        VueTablePagination,
        Multiselect,
        TaskEditModal
    },
    props: {
        endpoint: {
            type: String,
            required: true
        },
        display: {
            type: String,
            required: true
        },
        owners: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            fields: [
                {
                    name: 'name',
                    title: 'Title',
                    sortField: 'title'
                },
                {
                    name: '__slot:status',
                    title: 'Status',
                    sortField: 'status'
                },
                {
                    name: '__slot:due_date',
                    title: 'Due Date',
                    sortField: 'due_date'
                },
                {
                    name: '__slot:assignee',
                    title: 'Assigned To',
                    sortField: 'assigned_to'
                },
                {
                    name: '__slot:linked_to',
                    title: 'Linked To',
                    sortField: 'linked_to'
                },
                {
                    name: '__slot:actions',
                    title: 'Actions',
                    sortField: null,
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    sortable: false
                }
            ],
            totalTasks: 0,
            showAssignedOnly: false,
            activeFilter: 'all',
            showCustomDatePicker: false,
            customStartDate: '',
            customEndDate: '',
            searchQuery: '',
            filters: [
                { label: 'All', value: 'all' },
                { label: 'Incomplete', value: 'incomplete' },
                { label: 'Overdue', value: 'overdue' },
                { label: 'Today', value: 'today' },
                { label: 'Tomorrow', value: 'tomorrow' },
                { label: 'This Week', value: 'week' }
            ],
            selectedOwners: [],
            debouncedSearchQuery: '',
            searchTimeout: null,
            tasks: []
        }
    },
    computed: {
        ...mapGetters([
            'getIsLoadingState',
        ]),
        filteredEndpoint() {
            const url = new URL(this.endpoint, window.location.origin);
            const params = new URLSearchParams(url.search);
            
            // Add filter parameter
            params.set('filter', this.activeFilter);
            
            if (this.debouncedSearchQuery.trim()) {
                params.set('search', this.debouncedSearchQuery.trim());
            }
            
            // Add or remove owners filter
            if (this.selectedOwners && this.selectedOwners.length > 0) {
                const ownerIds = this.selectedOwners.map(owner => owner.id);
                params.set('owners', ownerIds.join(','));
            } else {
                params.delete('owners');
            }
            
            // Add custom date parameters if needed
            if (this.activeFilter === 'custom' && this.customStartDate && this.customEndDate) {
                params.set('start_date', this.customStartDate);
                params.set('end_date', this.customEndDate);
            }
            
            // Construct the final URL
            const baseUrl = this.endpoint.split('?')[0];
            return `${baseUrl}?${params.toString()}`;
        },
        validOwners() {
            return this.owners.filter(owner => owner && owner.id);
        }
    },
    mounted() {
        // First check URL parameters
        const params = new URLSearchParams(window.location.search);
        
        if (params.toString()) {
            // If URL has parameters, use them
            this.activeFilter = params.get('filter') || 'all';
            
            if (params.get('start_date') && params.get('end_date')) {
                this.activeFilter = 'custom';
                this.customStartDate = params.get('start_date');
                this.customEndDate = params.get('end_date');
                this.showCustomDatePicker = true;
            }
            
            if (params.get('search')) {
                this.searchQuery = params.get('search');
            }
            
            if (params.get('owners')) {
                const ownerIds = params.get('owners').split(',');
                this.selectedOwners = this.owners.filter(owner => 
                    ownerIds.includes(owner.id.toString())
                );
            }
        } else {
            // If no URL parameters, try to load from cache
            const savedFilters = this.loadFiltersFromCache();
            if (savedFilters) {
                this.activeFilter = savedFilters.filter;
                this.searchQuery = savedFilters.search;
                
                if (savedFilters.customStartDate && savedFilters.customEndDate) {
                    this.activeFilter = 'custom';
                    this.customStartDate = savedFilters.customStartDate;
                    this.customEndDate = savedFilters.customEndDate;
                    this.showCustomDatePicker = true;
                }
                
                if (savedFilters.owners && savedFilters.owners.length) {
                    this.selectedOwners = this.owners.filter(owner => 
                        savedFilters.owners.includes(owner.id)
                    );
                }

                // Update URL with cached filters
                const params = new URLSearchParams();
                params.set('filter', this.activeFilter);
                if (savedFilters.owners) {
                    params.set('owners', savedFilters.owners.join(','));
                }
                if (savedFilters.search) {
                    params.set('search', savedFilters.search);
                }
                window.history.pushState(
                    { 
                        filter: this.activeFilter,
                        search: this.searchQuery
                    },
                    '',
                    `${window.location.pathname}?${params.toString()}`
                );
            }
        }

        // Listen for task creation/updates from the Blade modal
        document.addEventListener('taskSaved', () => {
        });

        // Add popstate event listener for browser back/forward buttons
        window.addEventListener('popstate', this.handlePopState);
    },
    beforeDestroy() {
        // Clean up event listeners
        window.removeEventListener('popstate', this.handlePopState);
        // Make sure overlay is hidden when component is destroyed
      
    },
    methods: {
        getStatusClass(status) {
            return {
                'badge-success': status === 'completed',
                'badge-warning': status === 'open',
                'badge-danger': status === 'overdue'
            }
        },
        formatDate(date) {
            return moment(date).format('YYYY-MM-DD')
        },
        onPaginationData(paginationData) {
            this.tasks = paginationData.data
            this.$refs.pagination.setPaginationData(paginationData)
            this.totalTasks = paginationData.total
        },
        onTableLoading() {
            this.$store.dispatch('setIsLoadingState', true);
        },
        onTableLoaded() {
            this.$store.dispatch('setIsLoadingState', false);
        },
        changePage(page) {
            this.$refs.vuetable.changePage(page)
        },
        openEditModal(task) {
            if (!task || !task.id) return;
            $(`#task-modal-${task.id}`).modal('show');
        },
        handleFilterChange: function(filter) {
            if (filter === this.activeFilter) return; // Prevent duplicate requests
            
            this.activeFilter = filter;
            this.showCustomDatePicker = false;
            
            const params = new URLSearchParams(window.location.search);
            params.set('filter', filter);
            
            // Remove date parameters if not using custom filter
            if (filter !== 'custom') {
                params.delete('start_date');
                params.delete('end_date');
            }
            
            // Preserve search query if exists
            if (this.searchQuery.trim()) {
                params.set('search', this.searchQuery.trim());
            }
            
            window.history.pushState(
                { 
                    filter: filter,
                    search: this.searchQuery.trim()
                },
                '',
                `${window.location.pathname}?${params.toString()}`
            );
            
            this.saveFiltersToCache();
        }, // 300ms delay
        handleSearchKeyup() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.debouncedSearchQuery = this.searchQuery;
            }, 500);
        },
        handleSearch() {
            // Update URL with search parameter
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            
            if (this.searchQuery.trim()) {
                params.set('search', this.searchQuery.trim());
            } else {
                params.delete('search');
            }
            
            // Push to browser history
            window.history.pushState(
                { 
                    filter: this.activeFilter,
                    search: this.searchQuery.trim()
                },
                '',
                `${window.location.pathname}?${params.toString()}`
            );
            
            this.saveFiltersToCache();
        },
        // Add this method to handle browser back/forward buttons
        handlePopState(event) {
            if (event.state) {
                this.activeFilter = event.state.filter || 'all';
                this.searchQuery = event.state.search || '';
                if (event.state.filter === 'custom') {
                    this.customStartDate = event.state.start_date || '';
                    this.customEndDate = event.state.end_date || '';
                }
            }
        },
        onOwnersChange() {
            const params = new URLSearchParams(window.location.search);
            
            // Add or remove owners parameter
            if (this.selectedOwners && this.selectedOwners.length > 0) {
                const ownerIds = this.selectedOwners.map(owner => owner.id);
                params.set('owners', ownerIds.join(','));
            } else {
                params.delete('owners');
            }
            
            // Update URL
            window.history.pushState(
                {
                    filter: this.activeFilter,
                    owners: this.selectedOwners,
                    search: this.searchQuery.trim()
                },
                '',
                `${window.location.pathname}?${params.toString()}`
            );

            this.saveFiltersToCache();
        },
        exportToCsv() {
            // Get current URL parameters
            const params = new URLSearchParams(window.location.search);
            
            // Add export flag
            params.set('export', 'csv');
            
            // Create export URL
            const exportUrl = `${this.endpoint}?${params.toString()}`;
            
            // Trigger download
            window.location.href = exportUrl;
        },
        resetSearch() {
            this.searchQuery = '';
            const params = new URLSearchParams(window.location.search);
            params.delete('search');
            
            window.history.pushState(
                { 
                    filter: this.activeFilter,
                    search: '',
                    owners: this.selectedOwners
                },
                '',
                `${window.location.pathname}?${params.toString()}`
            );
            
            this.saveFiltersToCache();
        },
        saveFiltersToCache() {
            const filters = {
                filter: this.activeFilter,
                search: this.searchQuery,
                customStartDate: this.customStartDate,
                customEndDate: this.customEndDate,
                owners: this.selectedOwners.map(owner => owner.id)
            };
            localStorage.setItem('tasksFilters', JSON.stringify(filters));
        },
        loadFiltersFromCache() {
            const savedFilters = localStorage.getItem('tasksFilters');
            if (savedFilters) {
                return JSON.parse(savedFilters);
            }
            return null;
        },
        applyCustomFilter() {
            this.activeFilter = 'custom';
            this.showCustomDatePicker = false;
            
            // Create URL with custom filter and dates
            const params = new URLSearchParams(window.location.search);
            params.set('filter', 'custom');
            params.set('start_date', this.customStartDate);
            params.set('end_date', this.customEndDate);
            
            // Preserve other parameters
            if (this.searchQuery.trim()) {
                params.set('search', this.searchQuery.trim());
            }
            if (this.selectedOwners && this.selectedOwners.length > 0) {
                params.set('owners', this.selectedOwners.map(owner => owner.id).join(','));
            }
            
            // Push to browser history
            window.history.pushState(
                { 
                    filter: 'custom',
                    start_date: this.customStartDate,
                    end_date: this.customEndDate,
                    search: this.searchQuery.trim(),
                    owners: this.selectedOwners
                },
                '',
                `${window.location.pathname}?${params.toString()}`
            );
            
            this.saveFiltersToCache();
        },
        openAddTaskModal() {
            $('#add-task-modal').modal('show');
        },
        refreshTable() {
            this.$refs.vuetable.refresh();
        }
    }
}
</script>

<style scoped>
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}
.text-primary {
    color: #007bff !important;
}
.text-primary:hover {
    text-decoration: underline;
}
.input-group {
    max-width: 500px;
    margin: 0 auto;
}
.input-group .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}
.btn-group {
    margin-bottom: 8px;
}
.btn-group .btn {
    margin-right: 4px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
input[type="date"] {
    padding: 4px 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.input-group .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}
/* Add Multiselect styles */

.multiselect__input {
    border: 1px solid #e8e8e8;
    background: #fff;
    padding: 8px 30px 8px 12px;
    font-size: 14px;
}

.multiselect__input:focus {
    border-color: #66afe9;
    outline: 0;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
}

.search-input {
    border-radius: 5px 0px 0px 5px;
    height: 38px;
}

.reset-search {
    border-radius: 0px 5px 5px 0px;
    height: 38px;
}

.filter-group {
    width: 25%;
}

.filter-buttons {
    width: auto;
}

@media (max-width: 1500px) {
    .d-flex.justify-content-between {
        row-gap: 1rem;
    }

    .filter-group {
        width: 48%; /* Give some space between elements */
    }

    .filter-buttons {
        width: 100%;
        order: -1; /* Move filters to top */
    }

    .btn-group {
        width: 100%;
        display: flex;
    }

    .btn-group .btn {
        flex: 1;
    }
}

@media (max-width: 767px) {
    .filter-group {
        width: 100%;
    }
}
</style>