<template>
    <div id="generic_price_table">
        <!--PRICE CONTENT START-->
        <div class="generic_content clearfix" :class="{active: crmmodules.plan.accounting.active}">
                                
                <!--HEAD PRICE DETAIL START-->
                <div class="generic_head_price clearfix">
                
                    <!--HEAD CONTENT START-->
                    <div class="generic_head_content clearfix">
                    
                    <!--HEAD START-->
                        <div class="head_bg"></div>
                        <div class="head">
                            <span>Accounting</span>
                        </div>
                        <!--//HEAD END-->
                        
                    </div>
                    <!--//HEAD CONTENT END-->
                    
                    <!--PRICE START-->
                    <div class="generic_price_tag clearfix">	
                        <span v-if="isDiscounted" class="price discounted">
                            <span class="sign">$</span>
                            <span class="currency">{{this.app_fee}}</span>
                            <span class="cent">.00</span>
                            <span class="month">/MON</span>
                        </span>
                        <span class="price">
                            <span class="sign">$</span>
                            <span class="currency">{{appFee.split('.')[0]}}</span>
                            <span class="cent">.{{appFee.split('.')[1]}}</span>
                            <span class="month">/MON</span>
                        </span>
                        <div v-if="promocode"><em>*With</em> {{ promocode }} <em>promocode</em></div>
                    </div>
                    <!--//PRICE END-->
                    
                </div>                            
                <!--//HEAD PRICE DETAIL END-->
                
                <!--FEATURE LIST START-->
                <div class="generic_feature_list">
                    <ul>
                        <li>
                            <span>Bank Integrations</span>
                        </li>
                        <li>
                            <span>Advanced Reporting</span>
                        </li>
                        <li>
                            <span>Fund Transfers</span>
                        </li>
                        <li>
                            <span>And More...</span>
                        </li>
                    </ul>
                </div>
                <!--//FEATURE LIST END-->
                
                <!--BUTTON START-->
            
            <div class="generic_price_btn clearfix" v-if="crmmodules.plan.accounting.action == 'enable'">
                <button class="btn btn-primary" @click="$emit('enable-disable-module', true)">
                    <i aria-hidden="true" class="fa fa-angle-double-up"></i>
                    Enable Module
                </button>
            </div>
            
<!--
            <div class="generic_price_btn clearfix" v-if="crmmodules.plan.accounting.action == 'enable'">
                <button class="btn btn-primary" v-on:click="redirect(url)">
                    <i aria-hidden="true" class="fa fa-angle-double-up"></i>
                    Enable Module
                </button>
            </div>
-->
            <div class="generic_price_btn clearfix" v-if="crmmodules.plan.accounting.action == 'disable'">
                <button class="btn btn-danger" @click="$emit('enable-disable-module', false)">
                    <i aria-hidden="true" class="fa fa-angle-double-down"></i>
                    Disable Module
                </button>
            </div>
            <!--//BUTTON END-->
                
            </div>
            <!--//PRICE CONTENT END-->
    </div>
</template>
<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    export default {
        props: {
            url: {},
            app_fee: {
                default: 29,
            },
            current_app_fee: false,
            discount: 0,
            promocode: '',
        },
        name: 'AccountingPlan',
        mounted() {
            
        },
        computed: {
            ...mapState([
                'crmmodules',
                'action'
            ]),
            currentAppFee() {
                if (this.current_app_fee === false) return false
                return this.current_app_fee
            },
            currentDiscount() {
                return this.discount ? this.discount : 0
            },
            isDiscounted() {
                return this.discount  && this.currentAppFee <= this.app_fee 
                || this.currentAppFee !== false && this.currentAppFee < this.app_fee
            },
            appFee() {
                // console.log(this.app_fee, this.current_app_fee)
                var fee = this.app_fee
                if (this.currentAppFee !== false) fee = this.currentAppFee
                if (this.isDiscounted) fee = fee*(1.0-this.currentDiscount)
                if (typeof fee == 'number') fee = (fee).toFixed(2)
                return fee;
            }
        },
        methods: {
            redirect: function(url){
                window.location.href = url
            }
        }
    }
</script>
<style scoped>
.discounted .sign,
.discounted .currency,
.discounted .cent,
.discounted .month,
.discounted {
    color: red !important;
    line-height: inherit !important;
    vertical-align: bottom !important;
    text-decoration: line-through !important;
    font-size: medium !important;
}
</style>
