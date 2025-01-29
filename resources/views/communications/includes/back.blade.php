@if($communication->email_editor_type === 'none')
    <a href="javascript:void(0)" class="pull-left" id="backToSelectTemplate">
        <span class="fa fa-chevron-left"></span>
        @lang('Back')
    </a>
@else
    @include('widgets.back')
@endif