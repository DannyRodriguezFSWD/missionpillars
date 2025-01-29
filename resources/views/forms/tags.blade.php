@extends('layouts.app')

@section('content')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush
<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => ['forms.formtags', $form->id]]) }}
        <div class="row">
            <div class="col-sm-8">
                <h3>@lang('Tag') {{ $form->name }}</h3>
            </div>
            <div class="col-sm-4 text-right">
                <button id="btn-submit-contact" type="submit" class="btn btn-primary">
                    <i class="icons icon-note"></i> @lang('Save')
                </button>
            </div>
        </div>
        <div id="form-tags">
            @include('forms.includes.functions')
            @include('forms.includes.tags')
        </div>
        {{ Form::close() }}
    </div>
</div>
@push('scripts')
<script>
    (function(){
        var top = 80;
        $(window).scroll(function () {
            var y = $(this).scrollTop();
            var button = $('#btn-submit-contact');
            if (y >= top) {
                button.css({
                    'position': 'fixed',
                    'top': '60px',
                    'right': '36px',
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
