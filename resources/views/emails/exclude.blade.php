@extends('layouts.app')

@section('content')

@include('emails.includes.functions')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => ['emails.tags.exclude', array_get($email, 'id')]]) }}
        {{ Form::hidden('uid', Crypt::encrypt(array_get($list, 'id'))) }}
        {{ Form::hidden('folder', array_get($root, 'id')) }}
        <div class="row">
            <div class="col-md-6">
                <h3>@lang('Step 4')</h3>
            </div>
            <div class="col-md-6 text-right pb-2">
                <div class="" id="floating-buttons">
                    <a href="{{ route('emails.track', ['id' => array_get($email, 'id')]) }}" class="btn btn-secondary">
                        <i class="icons icon-arrow-left"></i>
                        @lang('Previous')
                    </a>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#new-tag">
                        <i class="icons icon-tag"></i>
                        @lang('New tag')
                    </button>
                    <button type="submit" class="btn btn-primary">
                        @lang('Next')
                        <i class="icons icon-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <p>&nbsp;</p>
        <div class="row">
            <div class="col-md-12">
                <h5>@lang('Only include contacts that have these tags for this list')</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <ol class="tree">
                    <?php printIncludedFoldersTree($tree, $included); ?>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <h5>@lang('Do not send email to contacts that have these tags for this list')</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <ol class="tree">
                    <?php printExcludedFoldersTree($tree, $excluded); ?>
                </ol>
            </div>
        </div>
        
        {{ Form::close() }}

    </div>

    <div class="card-footer">&nbsp;</div>
</div>
@include('emails.includes.tags')
@push('scripts')
<style>
    a.selected{
        background-color: #0074D9 !important;
        color: #fff;
    }
    a.selected:hover{
        color: #fff;
    }
    li input + ol{
        background: url("{{ asset('css/toggle-small-expand.png') }}") 44px 0px no-repeat;
    }
    li input:checked + ol{
        background: url("{{ asset('css/toggle-small.png') }}") 44px 4px no-repeat;
    }
</style>
@endpush

@push('scripts')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    (function () {

        $('a.folder').on('click', function (e) {
            $('a.folder').removeClass('selected');
            $(this).parent().find('a.folder:first').addClass('selected');

            var checked = $(this).parent().find('input[type="checkbox"]:first').prop('checked');
            $(this).parent().find('input[type="checkbox"]:first').prop('checked', !checked);

            var value = $(this).data('id');
            $('input[name="folder"]').val(value);
        });

        $('input.folder').on('click', function (e) {
            $('a.folder').removeClass('selected');
            $(this).parent().find('a.folder:first').addClass('selected');
            var value = $(this).data('id');
            $('input[name="folder"]').val(value);
        });

        $('#new-tag').on('shown.coreui.modal', function () {
            var value = $('input[name="folder"]').val();
            $('select[name="parent"]').val(value);
        });

        $('#save-tag').on('click', function (e) {
            var value = $('select[name="parent"]').val();
            $('input[name="folder"]').val(value);

            var request = {
                method: "POST",
                url: "{{ route('emails.tags', ['id' => array_get($email, 'id')]) }}",
                data: {
                    folder: $('select[name="parent"]').val(),
                    tag: $('input[name="tag"]').val()
                }
            };
            $.ajax(request).done(function (data) {
                if (data != false) {
                    var selector = '[data-folder="' + data.folder_id + '"]';
                    var li = '<li class="event-tag"><input id="tag-' + data.id + '" class="tag" type="checkbox" name="tags[]" value="' + data.id + '" checked=""/> <span class="fa fa-tag"></span> '+data.name+'</li>';
                    $(selector).append(li);
                } else {
                    Swal.fire("An error has occurred",'please contact with technical support','error');
                }
            }).fail(function (data) {
                Swal.fire('',data,'error');
            });
        });

        
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
