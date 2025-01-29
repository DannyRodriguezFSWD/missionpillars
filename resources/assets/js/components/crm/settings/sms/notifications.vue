<template>
    <div class="crm-sms-settings-notifications">
        <div class="row mt-3">
            <div class="form-group col-md-6">
                <label for="name">Enter a label for this phone number <span class="text-danger">*</span></label>
                <input name="name" type="text" class="form-control" placeholder="Phone Number Label" v-model="name" />
            </div>
        </div>
    
        <h4 class="mt-4">Permissions and Notifications</h4>
        <p class="mb-0">Please select one or more contacts that will will have access to this phone number.</p>
        <p class="mb-0">These contacts will also be notified when someone replies back or sends an SMS.</p>
        <p class="small">(The notification wil be sent on their mobile phone and primary email)</p>        <hr>
        <div class="row">
            <div class="form-group col-12">
                <input class="form-control autocomplete ui-autocomplete-input" placeholder="Contact's Name" required="" autocomplete="off" name="contact" type="text" id="searchContactEdit">
                
                <ul id="notificationContactListEdit" class="mt-3"></ul>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 text-right">
                <button class="btn btn-primary" v-on:click="notifications">
                    <i class="fa fa-paper-plane"></i>
                    Update Settings
                </button>
            </div>
        </div>

        <div id="overlay" v-if="loading">
            <div class="spinner">
                <div class="rect1"></div>
                <div class="rect2"></div>
                <div class="rect3"></div>
                <div class="rect4"></div>
                <div class="rect5"></div>
            </div>
        </div>
    </div>
</template>
<style scoped>
    #overlay{ display: block; }
</style>

<script>
    let notificationContacts = [];

    export default {
        props: ['url', 'id', 'dataType', 'dataTarget', 'name', 'contacts'],
        name: 'CRMSmsSettingsNotifications',
        data: function (){
            return {
                loading: false,
                contactList: ''
            }
        },
        mounted: function () {
            if (this.contacts !== 'null') {
                notificationContacts = JSON.parse(this.contacts);
                updateNotificationContactList();
            }
          
            $('#notificationContactListEdit').on('click', '.removeContactFromList', function () {
                notificationContacts = notificationContacts.filter(contact => contact.item.id != $(this).attr('data-contact-id'))
                updateNotificationContactList()
            });
                
            function updateNotificationContactList() {
                let notificationContactList = ''
                if (notificationContacts.length > 0) {
                    notificationContacts.forEach(contact => {
                        notificationContactList += `<li>${contact.item.label} <button class="fa fa-close btn btn-sm removeContactFromList" data-contact-id="${contact.item.id}"></button></li>`
                    })
                }
                
                $('#notificationContactListEdit').html(notificationContactList);
            }
          
            $('#searchContactEdit').autocomplete({
                source: ( request, response ) => {
                    $.ajax({
                        url: "/crm/ajax/contacts/autocomplete",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function( data ) {
                            response( data );
                        }
                    });
                },
                minLength: 2,
                select: ( event, ui ) => {
                    if (notificationContacts.find(contact => contact.item.id == ui.item.id ) == undefined) {
                        notificationContacts.push(ui);
                        updateNotificationContactList();
                    }
                },
                close(){
                    $('#searchContactEdit').val('');
                }
            });
        },
        methods: {
            notifications: function(){
                var params = {
                    name: this.name,
                    contacts: notificationContacts.map(u => u.item.id)
                };
                
                if (params.name == '') {
                    Swal.fire('You have to add a phone label first', '', 'info')
                    return;
                }
                
                if (notificationContacts.length === 0) {
                    Swal.fire('You have to select at least one contact first', '', 'info')
                    return;
                }
                
                var url = this.url+'settings/sms/'+this.id;
                this.loading = true;
                
                if (notificationContacts.length > 0) {
                    this.contactList = '';
                    notificationContacts.forEach(contact => {
                        this.contactList += `${contact.item.label}, `;
                    })
                    this.contactList = this.contactList.replace(/(^\s*,)|(,\s*$)/g, '');
                } else {
                    this.contactList = '';
                }
                
                axios.put(url, params).then((response) => {
                    this.loading = false;
                    if (response.status == 200) {
                        Swal.fire('Phone number settings updated successfully', '', 'success');
                        window.location = this.dataTarget;
                    } else {
                        this.modal.body = 'Something wrong happened';
                        this.modal.show = true;
                    }
                });
            }
        }
    }
</script>
