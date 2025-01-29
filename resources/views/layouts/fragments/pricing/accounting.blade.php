<!--PRICE CONTENT START-->
<div class="generic_content clearfix">
                          
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
                <span class="price">
                    <span class="sign">$</span>
                    <span class="currency">{{$acct->app_fee}}</span>
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
        
        <h5>
            <span class="badge badge-success">
                14 day free trial!
            </span>
        </h5>
        <!--BUTTON START-->
        <div class="generic_price_btn clearfix">
          <a href="{{ route('subscription.index', ['feature' => 'accounting-transactions']) }}" class="btn btn-outline-success">Upgrade now</a>
        </div>
        <!--//BUTTON END-->
        
    </div>
    <!--//PRICE CONTENT END-->
