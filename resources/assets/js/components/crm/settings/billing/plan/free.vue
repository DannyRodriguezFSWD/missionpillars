<template>
    <div id="generic_price_table">
        <!--PRICE CONTENT START-->
        <div class="generic_content clearfix" :class="{active: crmmodules.plan.free.active}">
                                
                <!--HEAD PRICE DETAIL START-->
                <div class="generic_head_price clearfix">
                
                    <!--HEAD CONTENT START-->
                    <div class="generic_head_content clearfix">
                    
                    <!--HEAD START-->
                        <div class="head_bg"></div>
                        <div class="head">
                            <span>CRM</span>
                        </div>
                        <!--//HEAD END-->
                        
                    </div>
                    <!--//HEAD CONTENT END-->
                    
                    <!--PRICE START-->
                    <div class="generic_price_tag clearfix">	
                        <span class="price">
                            <span class="sign">$</span>
                            <span class="currency">0</span>
                            <span class="cent">.00</span>
                            <span class="month">/MON</span>
                        </span>
                    </div>
                    <!--//PRICE END-->
                    
                </div>                            
                <!--//HEAD PRICE DETAIL END-->
                
                <!--FEATURE LIST START-->
                <div class="generic_feature_list">
                <ul>
                    <li>
                        <span>Contacts</span>
                    </li>
                    <li>
                        <span>Transactions</span>
                    </li>
                    <li>
                        <span>Purposes</span>
                    </li>
                    <li>
                        <span>Fundraisers</span>
                    </li>
                    <li>
                        <span>Tasks</span>
                    </li>
                </ul>
                </div>
                <!--//FEATURE LIST END-->
                
                <!--BUTTON START-->
                <!--
                <div class="generic_price_btn clearfix">
                    <div class="btn-group-toggle" data-toggle="buttons">
                    <label class="btn active free">
                        <input name="free" type="checkbox" checked autocomplete="off"> Starting plan
                    </label>
                    </div>
                </div>
                -->
                <!--//BUTTON END-->
                
        </div>
            <!--//PRICE CONTENT END-->
    </div>
</template>
<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    export default {
        name: 'FreePlan',
        mounted() {
        },
        computed: {
            ...mapState([
                'crmmodules'
            ])
        },
    }
</script>


