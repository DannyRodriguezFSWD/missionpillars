<template>
    <div class="buy-phone-number">
        <label>Enter area code and click search button <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="text" class="form-control" autocomplete="off" placeholder="Enter area code and click search button" v-model="code" v-on:keyup="onAreaCodeChange()">
            <div class="input-group-append">
                <button class="btn btn-outline-primary" type="button" v-on:click="searchAvailablePhoneNumbers()">
                    <span class="fa fa-search"></span> Search
                </button>
            </div>
        </div>

        <div v-if="phoneNumbers.length > 0">
            <h6 class="mt-4">Select phone number <span class="text-danger">*</span></h6>
            <table class="table">
                <tbody>
                    <tr v-for="phoneNumber in phoneNumbers">
                        <th>
                            <input type="radio" name="phoneNumber" :value="phoneNumber" v-model="phoneNumberSelected" v-on:change="onRadioChange()"/>
                            {{ phoneNumber.friendlyName }}
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="row mt-3">
            <div class="form-group col-md-6">
                <label for="name">Enter a label for this phone number <span class="text-danger">*</span></label>
                <input name="name" type="text" class="form-control" placeholder="Phone Number Label" v-model="name" />
            </div>
        </div>
        
        <hr>
        <h4 class="mt-4">Permissions and Notifications</h4>
        <p class="mb-0">Please select one or more contacts that will will have access to this phone number.</p>
        <p class="mb-0">These contacts will also be notified when someone replies back or sends an SMS.</p>
        <p class="small">(The notification wil be sent on their mobile phone and primary email)</p>

        <div class="row">
            <div class="form-group col-md-6">
                <input class="form-control autocomplete ui-autocomplete-input" placeholder="Contact's Name" required="" autocomplete="off" name="contact" type="text" id="searchContactCreate">

                <ul id="notificationContactListCreate" class="mt-3"></ul>
            </div>
        </div>
        <hr>
        <div class="mt-2">
            <button class="btn btn-primary" v-on:click="buyPhoneNumber" :disabled="phoneNumberSelected == -1 || name == '' || hasNotificationContacts === false">
                Purchase Phone Number
            </button>
        </div>

        <div id="overlay" v-if="loading && standAloneComponent == 'true'">
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
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';

    let notificationContacts = [];

    export default {
        props: ['dataType', 'dataTarget', 'dataShow', 'dataHide', 'url', 'standAloneComponent'],
        name: 'CRMCommunicationsBuyPhoneNumber',
        data: function (){
            return {
                phoneNumberSelected: -1,
                code: '',
                name: '',
                hasNotificationContacts: false,
                request: {
                    error: false,
                    header: 'Error',
                    body: ''
                }
            }
        },
        mounted: function () {
            this.code = this.areaCode;
            if(this.standAloneComponent == 'true'){
                this.initAction({
                    base: this.url,
                    phone: 'false',
                    type: this.dataType,
                    target: this.dataTarget,
                    show: this.dataShow,
                    hide: this.dataHide
                });
            }
            
            let vueThis = this;

            $(document).ready(function () {
                $('#notificationContactListCreate').on('click', '.removeContactFromList', function () {
                    notificationContacts = notificationContacts.filter(contact => contact.item.id != $(this).attr('data-contact-id'))
                    updateNotificationContactList()
                });

                function updateNotificationContactList() {
                    let notificationContactList = ''
                    if (notificationContacts.length > 0) {
                        notificationContacts.forEach(contact => {
                            notificationContactList += `<li>${contact.item.label} <button class="fa fa-close btn btn-sm removeContactFromList" data-contact-id="${contact.item.id}"></button></li>`
                        })
                        
                        vueThis.hasNotificationContacts = true;
                    } else {
                        vueThis.hasNotificationContacts = false;
                    }

                    $('#notificationContactListCreate').html(notificationContactList);
                }
                
                $('#searchContactCreate').autocomplete({
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
                            console.log(notificationContacts);
                            updateNotificationContactList();
                        }
                    },
                    close(){
                        $('#searchContactCreate').val('');
                    }
                });
            });
        },
        computed: {
            ...mapState([
                'phoneNumbers',
                'areaCode',
                'baseUrl',
                'phoneNumber',
                'loading',
                'notifications'
            ])
        },
        methods: {
            ...mapMutations([
                'ON_CHANGE_PHONE_NUMBER_MODEL',
                'ON_CHANGE_PHONE_AREA_CODE_MODEL',
                'IS_LOADING',
                'STATE_NOTIFICATIONS'
            ]),
            ...mapActions([
                'buyPhoneNumberAction',
                'searchAvailablePhoneNumbersAction',
                'initAction'
            ]),
            searchAvailablePhoneNumbers: function(){
                this.searchAvailablePhoneNumbersAction({
                    code: this.code,
                    baseUrl: this.baseUrl
                });
            },
            onRadioChange: function(){
                this.ON_CHANGE_PHONE_NUMBER_MODEL(this.phoneNumberSelected);
            },
            onAreaCodeChange: function(){
                if(this.code.length <= 0){
                    this.ON_CHANGE_PHONE_AREA_CODE_MODEL('false');
                    return;
                }
                this.ON_CHANGE_PHONE_AREA_CODE_MODEL(this.code);
            },
            buyPhoneNumber: function(){
                this.buyPhoneNumberAction({
                    payload: this.$store.state,
                    name: this.name,
                    notifications: {
                        contacts: notificationContacts.map(u => u.item.id)
                    },
                    standAloneComponent:this.standAloneComponent
                });
            }
        }
    }
</script>
