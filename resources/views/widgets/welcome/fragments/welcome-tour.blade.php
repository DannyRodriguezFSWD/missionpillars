<div class="modal fade" id="widget-welcome-tour" tabindex="-1" role="modal" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Welcome')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="flexslider">
                    <ul class="slides">
                        <li>
                            <div class="row">
                                <div class="col-sm-5">
                                    <h5>@lang('Add the widgets you want')</h5>
                                    <p>@lang("You can add the widgets you want by clicking the \"Add Widget\" button.")</p>
                                </div>
                                <div class="col-sm-7">
                                    <img class="img-responsive" src="{{ asset('img/widgets/welcome/addwidget.png') }}" />
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-sm-5">
                                    <h5>@lang('Configure them to your needs')</h5>
                                    <p>@lang("Each widget can be configured to the specific metrics you need.")</p>
                                </div>
                                <div class="col-sm-7">
                                    <img class="img-responsive" src="{{ asset('img/widgets/welcome/editwidget.jpg') }}" />
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-sm-5">
                                    <h5>@lang('Order them the way you want them')</h5>
                                    <p>@lang("You can even move the widgets around so your most important widgets are on top.  Simply grab a widget by the title bar and move it wherever you want it. Don't worry, the other widgets will clear the way for you.")</p>
                                </div>
                                <div class="col-sm-7">
                                    <img class="img-responsive" src="{{ asset('img/widgets/welcome/draganddrop.png') }}" />
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button> -->
                <div class="custom-navigation">
                    <div class="row">
                        <div class="col-sm-6">
                            <a href="#" class="flex-prev btn btn-secondary float-left prev">Prev</a>
                        </div>
                        <div class="custom-controls-container"></div>
                        <div class="col-sm-6">
                            <a href="#" class="flex-next btn btn-success float-right next">Next</a>
                            <button class="btn btn-primary float-right dismiss" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="{{ asset('css/flexslider.css') }}" rel="stylesheet">
<style>
    .flexslider{ margin: 0; }
    button.dismiss, a.prev{ display: none; }
    
</style>
@endpush

@push('scripts')
<script type="text/javascript" src="{{ asset('js/jquery.flexslider-min.js') }}"></script>
<script type="text/javascript">
(function () {
    $('.flexslider').flexslider({
        controlsContainer: $(".custom-controls-container"),
        customDirectionNav: $(".custom-navigation a"),
        slideshow: false,
        animationLoop: true,
        after: function(slider){
            if(slider.currentSlide === 0){
                $('a.prev').hide();
            }
            else{
                $('a.prev').show();
            }
            
            if(slider.currentSlide < (slider.count - 1)){
                $('a.next').show();
                $('button.dismiss').hide();
            }
            else{
                $('a.next').hide();
                $('button.dismiss').show();
            }
        }
    });
    
    $('a.next').on('click', function(e){
        if($(this).html() === 'Close'){
            $('#widget-welcome-tour').modal('hide')
        }
    });
})();
</script>
@endpush