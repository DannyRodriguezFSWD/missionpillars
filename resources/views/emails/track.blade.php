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
        {{ Form::open(['route' => ['emails.track.store', array_get($email, 'id')]]) }}
        {{ Form::hidden('uid', Crypt::encrypt(array_get($email, 'id'))) }}
        
        <div class="row">
            <div class="col-md-9">
                <h3>@lang('Step 3')</h3>
            </div>
            <div class="col-md-3 text-right pb-2">
                <div class="" id="floating-buttons">
                    <a href="{{ route('emails.count', ['id' => array_get($email, 'id')]) }}" class="btn btn-secondary">
                        <i class="icons icon-arrow-left"></i>
                        @lang('Previous')
                    </a>

                    <button type="submit" class="btn btn-primary">
                        @lang('Next')
                        <i class="icons icon-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <p>&nbsp;</p>
        <div class="row">
            <div class="col-sm-12">
                <h5><strong>@lang('Tag contacts based on actions taken below:')</strong></h5>
                <h6>@lang('Select an Action to Tag Contact Based on that Action')</h6>
            </div>
            <div class="col-sm-12">
                <ul class="list-group">
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'sent', array_has($track, 'sent')) }} @lang('Sent') @lang('(The message has been sent.)')
                        {{ Form::hidden('sent', array_get($track, 'sent')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'error', array_has($track, 'error')) }} @lang('Error') @lang('(The message has not been sent because of malformed email.)')
                        {{ Form::hidden('error', array_get($track, 'error')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'accepted', array_has($track, 'accepted')) }} @lang('Accepted') @lang('(The message has been placed in queue.)')
                        {{ Form::hidden('accepted', array_get($track, 'accepted')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'rejected', array_has($track, 'rejected')) }} @lang('Rejected') @lang('(The message has been rejected by the recipient email server.)')
                        {{ Form::hidden('rejected', array_get($track, 'rejected')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'delivered', array_has($track, 'delivered')) }} @lang('Delivered') @lang('(The email was sent and it was accepted by the recipient email server.)')
                        {{ Form::hidden('delivered', array_get($track, 'delivered')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'failed', array_has($track, 'failed')) }} @lang('Failed') @lang('(The email could not be delivered to the recipient email server.)')
                        {{ Form::hidden('failed', array_get($track, 'failed')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'opened', array_has($track, 'opened')) }} @lang('Opened') @lang('(The email recipient opened the email.)')
                        {{ Form::hidden('opened', array_get($track, 'opened')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'clicked', array_has($track, 'clicked')) }} @lang('Clicked') @lang('(The email recipient clicked on a link in the email.)')
                        {{ Form::hidden('clicked', array_get($track, 'clicked')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'unsubscribed', array_has($track, 'unsubscribed')) }} @lang('Unsubscribed') @lang('(The email recipient clicked on the unsubscribe link.)')
                        {{ Form::hidden('unsubscribed', array_get($track, 'unsubscribed')) }}
                    </li>
                    <li class="list-group-item">
                        {{ Form::checkbox('status[]', 'complained', array_has($track, 'complained')) }} @lang('Complained') @lang('(The email recipient clicked on the spam complaint button within their email client.)')
                        {{ Form::hidden('complained', array_get($track, 'complained')) }}
                    </li>
                </ul>
            </div>
        </div>

        {{ Form::close() }}

    </div>

    <!-- <div class="card-footer">&nbsp;</div> -->
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
            if (request.data.tag.trim() === '') {
                Swal.fire("Enter tag name",'','error');
                $('input[name="tag"]').focus();
                return false;
            }

            $.ajax(request).done(function (data) {
                if (data !== false) {
                    var selector = '[data-folder="' + data.folder_id + '"]';
                    var li = '<li class="event-tag"><input id="tag-' + data.id + '" class="tag" type="radio" name="selected_tag" value="' + data.id + '" checked=""/> <span class="fa fa-tag"></span> ' + data.name + '</li>';
                    $(selector).append(li);
                    $('#button-select-tag').prop('disabled', false).removeClass('disabled');
                } else {
                    Swal.fire("An error has occurred",'Please contact with technical support','error');
                }
            }).fail(function (data) {
                Swal.fire('',data,'error');
            });
        });

        $('#tags').on('change', 'input[name="selected_tag"]', function (e) {
            $('#button-select-tag').prop('disabled', false).removeClass('disabled');
        });
        
        $('button#button-select-tag').on('click', function(e){
            var hidden = $(current_check).val();
            $('input[name="' + hidden + '"]').val($('input[name="selected_tag"]:checked').val());
        });

        $('#new-tag').on('shown.coreui.modal', function (e) {
            var modal = $(this);
            modal.find('input[name="tag"]').val('').focus();
        }).on('hidden.coreui.modal', function (e) {

        });

        var current_check = null;
        var current_value = null;
        $('#tags button.close-modal, #new-tag button.close-modal').on('click', function (e) {
            $(current_check).prop('checked', false);
            current_check = null;
            current_value = null;
        });

        $('input[name="status[]"]').on('click', function (e) {
            current_check = 'input[value="' + $(this).val() + '"]';
            current_value = $('input[name="' + $(this).val() + '"]').val();
            //alert(current_value);
            var checked = $(this).prop('checked');
            if (checked) {
                $('#tags').modal('show');
            }
        });
        
        $('#tags').on('shown.coreui.modal', function (e) {
            //alert(Number.isInteger( parseInt(current_value) ) + '-'+ current_value);
            var number = parseInt(current_value);
            if(Number.isInteger(number)){
                $('#tag-'+current_value).prop('checked', true).trigger('change');
            }
        });
        
    })();
</script>
@endpush

@endsection
