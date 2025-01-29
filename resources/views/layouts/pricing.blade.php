<div id="generic_price_table">   
  <section>
          <div class="container">
              
              <!--BLOCK ROW START-->
              <div class="row">
                  <div class="col-md-6">
                    @include('layouts.fragments.pricing.chms')
                  </div>
                  <div class="col-md-6">
                    @if (env('APP_ACCOUNTING_AVAILABLE'))
                      @include('layouts.fragments.pricing.accounting')
                    @endif
                  </div>
              </div>	
              <!--//BLOCK ROW END-->
              
          </div>
      </section>             
    
  </div>

  @push('scripts')
      <script>
        (function(){
          $('.generic_price_btn label.btn').on('click', function(e){
            if( $(this).hasClass('free') ){
              return false;
            }
            $(this).parents('.generic_content').toggleClass('active');

            if( $(this).parents('.generic_content').hasClass('active') ){
              $(this).find('span').html('Remove 1 free month');
            }
            else{
              $(this).find('span').html('Add 1 free month');
            }
          });
        })();
      </script>
  @endpush
