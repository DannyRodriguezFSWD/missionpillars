@push('styles')
<style>
    .dropdown-toggle::after{
        content: initial;
    }
</style>
@endpush()
<div class="row">
    
    <div class="col-sm-12 text-center">
        @include('widgets.back')
        {{ array_get($contact, 'full_name') }}
        
        @if (auth()->user()->can('contact-update'))
        <div class="btn-group pull-right">
            <button type="button" class="btn brn-large btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="border: none;">
                <span>@lang('Actions')</span>
                <i class="c-icon mr-1 fa fa-ellipsis-v text-white"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('contacts.show', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                    <i class="c-icon mr-1 fa fa-user-circle-o"></i> @lang('Profile')
                </a>
                <a href="{{ route('contacts.compose', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                    <i class="c-icon mr-1 fa fa-envelope-o"></i> @lang('Send Email')
                </a>
                <a href="{{ route('contacts.sms', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                    <i class="c-icon mr-1 fa fa-commenting-o"></i> @lang('Send SMS')
                </a>
                @if(auth()->user()->can('transaction-view'))
                    <a href="{{ route('contacts.transactions', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                        <i class="c-icon mr-1 fa fa-dollar"></i> @lang('View Transactions')
                    </a>
                @endif
                @if(auth()->user()->can('transaction-view'))
                    <a href="{{ route('contacts.recurring', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                        <i class="c-icon mr-1 fa fa-repeat"></i> @lang('View Recurring')<br>@lang('Transactions')
                    </a>
                @endif
                @if(auth()->user()->can('group-view'))
                    <a href="{{ route('contacts.groups', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                        <i class="c-icon mr-1 fa fa-group"></i> @lang('View Groups')
                    </a>
                @endif
                @if(auth()->user()->can('contact-notes'))
                <a href="{{ route('contacts.notes', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                    <i class="c-icon mr-1 fa fa-sticky-note"></i> @lang('View Notes')
                </a>
                @endif
                <a href="{{ route('contacts.forms', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                    <i class="c-icon mr-1 fa fa-list-alt"></i> @lang('View Forms')
                </a>
                @if(auth()->user()->can('contact-update') || $contact && auth()->user()->contact->id == $contact->id)
                    <a href="{{ route('contacts.edit', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                        <i class="c-icon mr-1 fa fa-edit"></i> 
                        @if (array_get($contact, 'type') === 'organization')
                            @lang('Edit Organization')
                        @else
                            @lang('Edit Contact')
                        @endif
                    </a>
                @endif
                <a href="{{ route('contacts.tags', ['id' => array_get($contact, 'id')]) }}" class="dropdown-item">
                    <i class="c-icon mr-1 fa fa-tags"></i> @lang('Edit Tags')
                </a>
            </div>
        </div>
        @endcan
    </div>
    
</div>
