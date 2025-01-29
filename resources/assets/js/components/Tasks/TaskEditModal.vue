<template>
    <div class="modal fade" :id="'task-modal-' + task.id" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Task</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form @submit.prevent="validateAndSubmit" class="task-form">
                    <div class="modal-body">
                        <div class="form-group">
                            <span class="text-danger">*</span> <label>Name</label>
                            <input type="text" v-model="formData.name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea v-model="formData.description" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <span class="text-danger">*</span> <label>Due</label>
                                    <input type="text" v-model="formData.due_date" class="form-control datepicker" required>
                                </div>
                                <div class="col-sm-2">
                                    <label>Time</label>
                                    <select v-model="formData.hour" class="form-control edit-time" @change="handleTimeChange">
                                        <option v-for="(value, key) in hours" :key="key" :value="key">{{ value }}</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <label>&nbsp;</label>
                                    <select v-model="formData.minutes" class="form-control time">
                                        <option v-for="(value, key) in minutes" :key="key" :value="key">{{ value }}</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <label>&nbsp;</label>
                                    <select v-model="formData.when" class="form-control time">
                                        <option value="AM">AM</option>
                                        <option value="PM">PM</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <span class="text-danger">*</span> <label>Assignee</label>
                            <input type="text" v-model="formData.assigned_to_contact" class="form-control assign">
                            <input type="hidden" v-model="formData.assigned_to" name="assigned_to">
                        </div>
                        <div class="form-group">
                            <div class="d-flex">
                                <label class="c-switch-sm c-switch c-switch-label c-switch-primary mr-2">
                                    <input type="checkbox" v-model="formData.email_assignee_due" class="c-switch-input">
                                    <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                                </label>
                                <label>Email assignee when task is near due date</label>
                            </div>
                        </div>
                        <div class="form-group" v-show="formData.email_assignee_due">
                            <div class="d-flex">
                                <label>Email</label>
                                <input type="number" v-model="formData.due_number" class="form-control small mx-2" style="width: 100px;">
                                <select v-model="formData.due_period" class="form-control small mr-2" style="width: 100px;">
                                    <option value="day">days</option>
                                    <option value="week">weeks</option>
                                    <option value="month">months</option>
                                </select>
                                <label>before</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <span class="text-danger">*</span> <label>Link to Contact</label>
                            <input type="text" v-model="formData.link_to_contact" class="form-control link">
                            <input type="hidden" v-model="formData.linked_to" name="linked_to">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-success" @click="completeTask" v-if="task.status === 'open'">
                            Complete Task
                        </button>
                        <button type="button" class="btn btn-success" @click="reopenTask" v-else>
                            Reopen Task
                        </button>
                        <button type="button" class="btn btn-link text-danger" @click="deleteTask">
                            <span class="fa fa-trash-o"></span>
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="resetForm">
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        task: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            showTime: false,
            hours: {
                '---': '---',
                '01': '01',
                '02': '02',
                '03': '03',
                '04': '04',
                '05': '05',
                '06': '06',
                '07': '07',
                '08': '08',
                '09': '09',
                '10': '10',
                '11': '11',
                '12': '12'
            },
            minutes: {
                '00': '00',
                '05': '05',
                '10': '10',
                '15': '15',
                '20': '20',
                '25': '25',
                '30': '30',
                '35': '35',
                '40': '40',
                '45': '45',
                '50': '50',
                '55': '55'
            },
            formData: {
                name: '',
                description: '',
                due_date: '',
                hour: '---',
                minutes: '00',
                when: 'AM',
                assigned_to: '0',
                assigned_to_contact: '',
                email_assignee_due: false,
                due_number: 1,
                due_period: 'day',
                linked_to: '0',
                link_to_contact: ''
            }
        }
    },
    mounted() {
        const self = this;
        
        // Set up CSRF token for all axios requests
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Initialize when modal opens
        $(this.$el).on('show.coreui.modal', function() {
            console.log('Modal opening - initializing form');
            self.initializeForm();
            self.initializeAutocomplete();
            
            // Handle time display
            $(this).find('.edit-time').change();
        });
    },
    methods: {
        initializeForm() {
            console.log('Task data:', this.task);
            
            // First set up the contact strings like in list.blade.php
            let assignedTo = null;
            let linkedTo = null;

            // Debug assigned and linked contacts
            console.log('AssignedTo:', this.task.assignedTo);
            console.log('LinkedTo:', this.task.linkedTo);

            // Replicate the logic from list.blade.php
            if (this.task.assigned_to) {
                assignedTo = `${this.task.assigned_to.first_name} ${this.task.assigned_to.last_name} (${this.task.assigned_to.email_1})`;
                console.log('Formatted assignedTo:', assignedTo);
            }

            if (this.task.linked_to) {
                linkedTo = `${this.task.linked_to.first_name} ${this.task.linked_to.last_name} (${this.task.linked_to.email_1})`;
                console.log('Formatted linkedTo:', linkedTo);
            }

            // Initialize form data
            this.formData = {
                name: this.task.name,
                description: this.task.description,
                due_date: this.formatDate(this.task.due),
                hour: this.task.show_time ? this.formatHour(this.task.due) : '---',
                minutes: this.task.show_time ? this.formatMinutes(this.task.due) : '00',
                when: this.task.show_time ? this.formatAmPm(this.task.due) : 'AM',
                assigned_to: this.task.assigned_to && this.task.assigned_to.id ? this.task.assigned_to.id : '0',
                assigned_to_contact: assignedTo || '',
                email_assignee_due: !!this.task.email_due,
                due_number: this.task.due_number || 1,
                due_period: this.task.due_period || 'day',
                linked_to: this.task.linked_to && this.task.linked_to.id ? this.task.linked_to.id : '0',
                link_to_contact: linkedTo || ''
            };

            console.log('Initialized formData:', this.formData);
            this.showTime = this.formData.hour !== '---';
        },
        initializeAutocomplete() {
            const self = this;
            
            // Initialize link autocomplete
            $(this.$el).find('.link').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "/crm/ajax/contacts/autocomplete",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    self.formData.link_to_contact = ui.item.label;
                    self.formData.linked_to = ui.item.id;
                    return false;  // Prevent default behavior
                }
            });

            // Initialize assign autocomplete
            $(this.$el).find('.assign').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "/crm/ajax/contacts/autocomplete",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    self.formData.assigned_to_contact = ui.item.label;
                    self.formData.assigned_to = ui.item.id;
                    return false;  // Prevent default behavior
                }
            });

            // Handle backspace/delete for assign
            $(this.$el).find('.assign').keyup(function(e) {
                if ((e.keyCode == 8 || e.keyCode == 46) && $(this).val() === '') {
                    self.formData.assigned_to = '0';
                    self.formData.assigned_to_contact = '';
                }
            });

            // Handle backspace/delete for link
            $(this.$el).find('.link').keyup(function(e) {
                if ((e.keyCode == 8 || e.keyCode == 46) && $(this).val() === '') {
                    self.formData.linked_to = '0';
                    self.formData.link_to_contact = '';
                }
            });
        },
        handleTimeChange(e) {
            const value = e.target.value || this.formData.hour
            this.showTime = value !== '---'
            if (this.showTime) {
                $(this.$el).find('.time').show()
            } else {
                $(this.$el).find('.time').hide()
            }
        },
        validateAndSubmit() {
            if (this.formData.assigned_to == '0' || !this.formData.assigned_to) {
                Swal.fire('Tasks should be assigned to a contact', '', 'info')
                $(this.$el).find('input[name="assigned_to_contact"]').focus()
                return
            }

            if (this.formData.linked_to == '0' || !this.formData.linked_to) {
                Swal.fire('Tasks should be linked to a contact', '', 'info')
                $(this.$el).find('input[name="link_to_contact"]').focus()
                return
            }

            this.handleSubmit()
        },
        async handleSubmit() {
            try {
                // Create FormData object to match Laravel's form submission
                const formData = new FormData();
                
                // Add form fields in the same order as the blade template
                formData.append('_method', 'PUT');
                formData.append('name', this.formData.name);
                formData.append('description', this.formData.description || '');
                formData.append('due_date', this.formData.due_date);
                formData.append('hour', this.formData.hour);
                formData.append('minutes', this.formData.minutes);
                formData.append('when', this.formData.when);
                formData.append('assigned_to_contact', this.formData.assigned_to_contact || '');
                formData.append('assigned_to', this.formData.assigned_to);
                formData.append('email_assignee_due', this.formData.email_assignee_due ? '1' : '0');
                formData.append('due_number', this.formData.due_number);
                formData.append('due_period', this.formData.due_period);
                formData.append('link_to_contact', this.formData.link_to_contact || '');
                formData.append('linked_to', this.formData.linked_to);
                formData.append('uid', this.task.encrypted_id);  

                await $.ajax({
                    url: `/crm/tasks/${this.task.id}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'Accept': 'application/json'
                    },
                    success: () => {
                        this.$emit('taskUpdated');
                        $(this.$el).modal('hide');
                    },
                    error: (error) => {
                        console.error('Error updating task:', error);
                    }
                });
            } catch (error) {
                console.error('Error in handleSubmit:', error);
            }
        },
        async completeTask() {
            try {
                await axios.post(`/crm/tasks/${this.task.id}`, { complete: 1 , _method: 'PUT',uid: this.task.encrypted_id})
                this.$emit('taskUpdated')
                $(this.$el).modal('hide')
            } catch (error) {
                console.error('Error completing task:', error)
            }
        },
        async reopenTask() {
            try {
                const response = await axios.post(`/crm/tasks/${this.task.id}`, {open: 1, _method: 'PUT', uid: this.task.encrypted_id}, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                this.$emit('taskUpdated');
                $(this.$el).modal('hide');
            } catch (error) {
                console.error('Error reopening task:', error);
            }
        },
        async deleteTask() {
            if (confirm('Are you sure you want to delete this task?')) {
                try {
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('uid', this.task.encrypted_id);
                    const response = await axios.post(`/crm/tasks/${this.task.id}`, formData, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    
                    this.$emit('taskUpdated');
                    $(this.$el).modal('hide');
                } catch (error) {
                    console.error('Error deleting task:', error);
                }
            }
        },
        resetForm() {
            this.initializeForm()
        },
        formatDate(date) {
            return moment(date).format('YYYY-MM-DD')
        },
        formatHour(date) {
            return moment(date).format('hh')
        },
        formatMinutes(date) {
            return moment(date).format('mm')
        },
        formatAmPm(date) {
            return moment(date).format('A')
        },
        formatContact(contact) {
            if (!contact) return ''
            return `${contact.first_name} ${contact.last_name} (${contact.email_1})`
        }
    }
}
</script> 