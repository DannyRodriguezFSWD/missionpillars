@extends('layouts.app')

@section('content')

@include('lists.includes.functions')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::model($list, ['route' => ['lists.update', $list->id], 'method' => 'PUT']) }}
        {{ Form::hidden('uid', Crypt::encrypt($list->id)) }}
        @if( !is_null($email) )
        {{ Form::hidden('email', $email) }}
        @endif
        
        @include('lists.includes.form')

        {{ Form::close() }}
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    (function () {

        var top = 84;
        $(window).scroll(function () {
            var y = $(this).scrollTop();

            var button = $('#floating-buttons');
            if (y >= top) {
                button.css({
                    'position': 'fixed',
                    'top': '60px',
                    'right': '51px',
                    'z-index': '99'
                });
            } else {
                button.removeAttr('style')
            }
        });

    })();
</script>
@endpush

@endsection
