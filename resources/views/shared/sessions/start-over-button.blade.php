@if(session('redirect_url') && session('redirect_url') != request()->fullUrl())
    @php
    $_class = isset($class) ? $class : 'btn btn-lg btn-info';
    $_style = isset($style) ? $style : '';
    @endphp
    <a href="{{ route('redirect.show', ['id' => 'redirect_url']) }}" class="{{$_class}}" style="{{$_style}}">
        <span class="fa fa-undo"></span> @lang('Start Over')
    </a>
@endif