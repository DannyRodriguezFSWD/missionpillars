<template>
    <div id="generic_price_table">
        <!--PRICE CONTENT START-->
        <div class="generic_content clearfix" :class="{active: crmmodules.plan.chms.active}">
                                
            <!--HEAD PRICE DETAIL START-->
            <div class="generic_head_price clearfix">
            
                <!--HEAD CONTENT START-->
                <div class="generic_head_content clearfix">
                
                <!--HEAD START-->
                    <div class="head_bg"></div>
                    <div class="head">
                        <span>ChMS</span>
                    </div>
                    <!--//HEAD END-->
                    
                </div>
                <!--//HEAD CONTENT END-->
                
                <!--PRICE START-->
                <div class="generic_price_tag clearfix">
                    <div v-if="isDiscounted" class="discounted">${{app_fee}}/month + {{100*contact_fee}}&cent; per contact</div>
                    <div>${{appFee}}/month + {{contactFee}}&cent; per contact</div>
                    <div>&nbsp;</div>
                    <span class="price">
                        <span class="sign">$</span>
                        <span class="currency" id="amount-label"></span>
                        <span class="month">/MON</span>
                    </span>
                    <div v-if="promocode"><em>*With</em> {{ promocode }} <em>promocode</em></div>
                    <p>&nbsp;</p>
                    <input id="range-slider" type="range" min="0" max="2000" step="10" value="0">
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
                    <li>
                        <span>Events / Tickets</span>
                    </li>
                    <li>
                        <span>Custom Form Builder</span>
                    </li>
                    <li>
                        <span>Pledges</span>
                    </li>
                    <li>
                        <span>Groups</span>
                    </li>
                    <li>
                        <span>Events</span>
                    </li>
                    <li>
                        <span>Custom Forms</span>
                    </li>
                    <li>
                        <span>Child Checkin</span>
                    </li>
                    <li>
                        <span>Mass Email sending</span>
                    </li>
                    <li>
                        <span>Mass SMS sending</span>
                    </li>
                    <li>
                        <span>And More...</span>
                    </li>
                </ul>
            </div>
            <!--//FEATURE LIST END-->
            
            <!--BUTTON START-->
            <div class="generic_price_btn clearfix" v-if="crmmodules.plan.chms.action == 'enable'">
                <button class="btn btn-primary" @click="$emit('enable-disable-module', true)">
                    <i aria-hidden="true" class="fa fa-angle-double-up"></i>
                    Enable Module
                </button>
            </div>

            <div class="generic_price_btn clearfix" v-if="crmmodules.plan.chms.action == 'disable'">
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
        name: 'CHMSPlan',
        props: {
            app_fee: {
                default: 40,
            },
            contact_fee: {
                default: 0.03
            },
            discount: 0,
            promocode: '',
            current_app_fee: false,
            current_contact_fee: false,
        },
        mounted() {
            this.initPriceSlider()
        },
        data: function(){
            return {
                fee: {
                    currency: this.app_fee,
                    cents: '00'
                }
            }
        },
        computed: {
            ...mapState([
                'crmmodules'
            ]),
            currentAppFee() {
                if (this.current_app_fee === false) return false
                return this.current_app_fee
            },
            currentContactFee() {
                if (this.current_contact_fee === false) return false
                return this.current_contact_fee
            },
            currentDiscount() {
                return this.discount ? this.discount : 0
            },
            isDiscounted() {
                return this.discount  && this.currentAppFee <= this.app_fee
                || this.currentAppFee !== false && this.currentAppFee < this.app_fee
            },
            contactFee() {
                // console.log(this.contact_Fee, this.current_contact_fee)
                var fee = 100*this.contact_fee
                if (this.currentContactFee !== false) fee = 100*this.currentContactFee
                if (this.isDiscounted) fee = (fee*(1.0-this.currentDiscount)).toFixed(2)
                return fee;
            },
            appFee() {
                // console.log('chms', this.app_fee, this.current_app_fee)
                var fee = this.app_fee
                if (this.currentAppFee !== false) fee = this.currentAppFee
                if (this.isDiscounted) fee = (fee*(1.0-this.currentDiscount)).toFixed(2)
                return fee;
            }
        },
        methods: {
            initPriceSlider(){
                

                var $element = $('input[type="range"]');
                var $handle;
                var BASE = 1*this.appFee;
                var PER_CONTACT_AMOUNT = 0.01*this.contactFee;
                var that = this

                $element.rangeslider({
                    polyfill: false,
                    onInit: function() {
                        var value = BASE;
                        if(this.value > 0){
                            value = BASE + (PER_CONTACT_AMOUNT * this.value);
                        }

                        $handle = $('.rangeslider__handle', this.$range);
                        updateHandle($handle[0], this.value);
                        $("#amount-label").html(value.toFixed(2));
                    }
                }).on('input', function() {
                    var value = BASE;
                    if(this.value > 0){
                        value = BASE + (PER_CONTACT_AMOUNT * this.value);
                    }
                    updateHandle($handle[0], this.value);
                    $("#amount-label").html(value.toFixed(2));
                });

                function updateHandle(el, val) {
                    el.textContent = val;
                }

                $('input[type="range"]').rangeslider();
            }
        }
    }
</script>

<style>
/* Price slider */
.rangeslider,
.rangeslider__fill {
  display: block;  
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
}
.rangeslider {
  background: #e6e6e6;
  position: relative;
}
.rangeslider--horizontal {
  height: 1px;
  width: 100%;
}
.rangeslider--disabled {
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=40);
  opacity: 0.4;
}
.rangeslider__fill {
  background: #5cb85c;
  position: absolute;
}
.rangeslider--horizontal .rangeslider__fill {
  top: 0;
  height: 100%;
}
.rangeslider__handle {
  background: #5cb85c;
  color: #FFFFFF;  
  cursor: pointer;
  display: inline-block;
  width: 5.2em;
  height: 2.2em;
  position: absolute;    
  -moz-border-radius: 22px;
  -webkit-border-radius: 22px;
  border-radius: 22px;    
  line-height: 2.2em;
  text-align: center;
}
.rangeslider__handle:before {
    font-family: 'FontAwesome';
    content: "\f053";
    font-size: 11px;
    opacity: 0.5;
    margin: 0 3px;
    color: #fff;
    display: block;    
    position: absolute;
    top: 0;
    left: 5px;    
    bottom: 0;  
}
.rangeslider__handle:after {
    font-family: 'FontAwesome';
    content: "\f054";
    font-size: 11px;
    opacity: 0.5;
    margin: 0 3px;
    color: #fff;
    display: block;    
    position: absolute;
    top: 0;
    right: 5px;
    bottom: 0;  
}
.rangeslider--horizontal .rangeslider__handle {
  top: -15px;
  touch-action: pan-y;
  -ms-touch-action: pan-y;
}
input[type="range"]:focus + .rangeslider .rangeslider__handle {
  -moz-box-shadow: 0 0 8px rgba(255, 0, 255, 0.9);
  -webkit-box-shadow: 0 0 8px rgba(255, 0, 255, 0.9);
  box-shadow: 0 0 8px rgba(255, 0, 255, 0.9);
}
.price span, .pricing__dollar{
  font-size: 60px;
}

.discounted {
    color: red;
    text-decoration: line-through;
}
</style>
