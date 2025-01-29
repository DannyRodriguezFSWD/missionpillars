<template>
    <div id="login">
        <div class="card-body">
            <h2 v-if="subdomain == ''">Sign In</h2>
            <h2 v-if="subdomain != ''">Welcome back to {{ organization }}</h2>
            <p class="text-muted">Sign in to your account</p>
            <span class="help-block" v-if="has_error">
                <small class="text-danger">
                    <strong>{{ error }}</strong>
                </small>
            </span>
            <div class="input-group mb-3">
                <span class="input-group-prepend"><i class="input-group-text fa fa-user"></i></span>
                <input v-model="username" class="form-control" placeholder="Username" required="" autocomplete="off" name="email" type="email">
            </div>
            <div class="input-group mb-4 ">
                <span class="input-group-prepend"><i class="input-group-text fa fa-lock"></i></span>
                <input @keyup.enter="submit" v-model="password" id="password" type="password" class="form-control " name="password" required="" placeholder="Password">
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <button @click="submit" type="button" class="btn btn-primary btn-block">
                        <span class="icon icon-key"></span> Sign In
                    </button>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-link btn-block" href="password/reset">Forgot password?</a>
                </div>
            </div>
        </div>
        <CRMModal v-if="crmmodal.modal.show" 
            :modalContainerStyle="style" 
            :modalBodyStyle="{padding: '0px'}" 
            :modalHeaderStyle="{display: 'none'}"
            :modalFooterStyle="{display: 'none'}">
            <div slot="body">
                <div class="list-group">
                    <div class="list-group-item text-center" style="background-color: #6c757d!important; color: #ffffff;">
                        Select Organization
                    </div>
                    <button v-for="(item, index) in items" :key="index" @click="sendLogin(index)" type="button" class="list-group-item list-group-item-action">
                        <strong>{{ item.organization }}</strong>
                    </button>
                    <button @click="closeModal()" type="button" class="list-group-item list-group-item-action">
                        <i class="fa fa-times text-danger" aria-hidden="true"></i>
                        Cancel
                    </button>
                </div>
            </div>
            
        </CRMModal>
        <loading v-if="getIsLoadingState"></loading>
    </div>
</template>
<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    import loading from './Loading'
    import CRMModal from './crm-modal.vue'; 

    export default {
        name: 'login',
        props: {
            organization: {
                type: String,
                default: null
            },
            subdomain: {
                type: String,
                default: null
            }
        },
        mounted() {
            console.log('login mounted')
        },
        components:{
            CRMModal,
            loading
        },
        data: function(){
            return {
                has_error: false,
                error: 'Your email/password combination is not correct',
                username: '',
                password: '',
                style: {padding: '0px', width: '60%', maxWidth: '600px'},
                items: []
            }
        },
        computed: {
            ...mapGetters([
                'getIsLoadingState',
            ]),
            ...mapState([
                'crmmodal'
            ])
        },
        methods: {
            ...mapActions([
                'post',
                'setIsLoadingState'
            ]),
            closeModal(){
                this.$store.dispatch('crmmodal/showModalAction', false)
            },
            submit(){
                this.has_error = false
                this.error = ''
                if(this.username.trim() == '' || this.password.trim() == ''){
                    this.has_error = true
                    this.error = 'Username and Password are required'
                }

                if(!this.has_error){
                    this.post({
                        url: '/signin',
                        data:{
                            email: this.username,
                            password: this.password,
                            subdomain: this.subdomain
                        }
                    }).then(result => {
                        console.log(result, result.data)
                        //window.axios.defaults.headers.common['X-CSRF-TOKEN'] = result.data.track_id
                        if(result.data.code == 400){//errors
                            this.has_error = true
                            this.error = result.data.message
                        }
                        else if(result.data.code == 200 && result.data.data.length > 1){
                            //show a poup and ask wich org want to login
                            this.$store.dispatch('crmmodal/showModalAction', true)
                            this.items = result.data.data
                        }
                        else{//success login
                            this.setIsLoadingState(true)
                            window.location.href = result.data.message
                        }
                    }).catch(error => {
                        console.log(error)
                        this.has_error = true
                        this.error = "An error occurred, please try refreshing your browser"
                    })
                }
            },
            sendLogin(index){
                let data = {
                    email: this.username,
                    password: this.password,
                    organization: this.items[index]
                }
                this.post({
                    url: '/login',
                    data: data
                }).then(result => {
                    if(result.data.code == 400){//errors
                        this.has_error = true
                        this.error = result.data.message
                    }
                    else{//success login
                        this.setIsLoadingState(true)
                        window.location.href = result.data.message
                    }
                }).catch(error => {
                    console.log(error)
                })
            }
        }
    }
</script>
<style scoped>
.modal-header-1 p{
    text-align: center !important;
}
.modal-container{
    padding: 0px !important;
}
</style>
