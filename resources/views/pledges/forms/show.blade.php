@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('pledgeforms.show',$form) !!}
@endsection
@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12" style="min-height: 50px;">
                        <div class="float-right pb-2" id="floating-buttons">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#share-modal">
                                <i class="fa fa-share-alt"></i> @lang('Share Link')
                            </button>
                            @if(auth()->user()->can('pledge-update'))
                                <a id="edit-transaction" class="btn btn-primary" href="{{ route('pledgeforms.edit', ['id' => array_get($form, 'id')]) }}">
                                    <span class="fa fa-edit"></span>
                                    @lang('Edit')
                                </a>
                            @endif
                            @if(auth()->user()->can('pledge-delete'))
                                <button type="button" class="btn btn-danger delete" data-form="#form-{{ array_get($form, 'id') }}" data-toggle="modal" data-target="#delete-modal">
                                    <i class="fa fa-trash"></i> @lang('Delete')
                                </button>
                            @endif
                        </div>
                        @if(auth()->user()->can('pledge-delete'))
                        {{ Form::open( ['route' => ['pledgeforms.destroy', array_get($form, 'id')], 'method' => 'DELETE', 'id' => 'form-'.array_get($form, 'id')] )  }}
                        {{ Form::hidden('uid', Crypt::encrypt(array_get($form, 'id'))) }}
                        {{ Form::close() }}
                        @endif
                    </div>
                </div>

                <table class="table">
                    <tbody>
                        <tr>
                            <td>
                                <strong>@lang('Name'):</strong>
                                {{ array_get($form, 'name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Purpose'):</strong>
                                {{ array_get($form, 'purpose.name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Fundraiser'):</strong>
                                {{ array_get($form, 'campaign.name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Form'):</strong>
                                {{ array_get($form, 'form.name') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">&nbsp;</div>
        </div>

    </div>

</div>
<!--/.row-->
@include('pledges.forms.includes.delete-modal')
@include('pledges.forms.includes.share-modal')

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
