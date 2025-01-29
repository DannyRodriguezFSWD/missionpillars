@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('settings.sms.index') !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="text-center col-sm-12">
                <a href="javascript:history.back()" class="pull-left">
                    <span class="fa fa-chevron-left"></span> @lang('Back')
                </a>
                <div>@lang('SMS Settings')</div>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('settings.sms.create') }}" class="btn btn-success pull-right">
                    <i class="fa fa-plus"></i> Get Phone Number
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                @if ($smsPhoneNumbers->count() === 0)
                <div class="alert alert-info">
                    @lang('Before you can send an SMS, you need to purchase a number that will be unique to your organization that your sms messages will be sent from (Your account will be charged $10/month for the purchase of a phone number)')
                </div>
                
                @else
                
                @foreach ($smsPhoneNumbers as $smsPhoneNumber)
                <h4>{{ $smsPhoneNumber->name_and_number }}</h4>
                <div class="table-responsive mb-3">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <td>
                                    <div>
                                        <div>
                                            <small class="mt-0">@lang('Permissions and reply notifications will be sent to'):</small>
                                        </div>

                                        <div>
                                            <small class="mt-0">
                                                <i class="fa fa-users"></i>
                                                Contacts: {{ $smsPhoneNumber->current_contacts }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('settings.sms.edit', $smsPhoneNumber) }}" class="btn btn-primary">
                                        <i class="fa fa-edit"></i>
                                        Edit
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="releaseNumber({{ $smsPhoneNumber->id }});">
                                        <i class="fa fa-trash"></i>
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endforeach
                
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function releaseNumber(id) {
        confirmMessage('Alert', 'If you remove your phone number, another organization would be able to use it', function () {
            customAjax({
                url: "/crm/settings/sms/"+id,
                type: 'delete',
                success: function (data) {
                    Swal.fire('Phone number was removed successfully', '', 'success');
                    window.location.reload();
                }
            });
        });
    }
</script>
@endpush

@endsection
