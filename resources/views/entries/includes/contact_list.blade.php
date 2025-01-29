<table class="table table-striped">
    <tbody>
        @foreach ($match as $item)
        <tr>
            <td>
                {{ Form::open(['route' => ['entries.update', array_get($entry, 'id')], 'method' => 'PUT', 'id' => 'link_and_update-'.array_get($item, 'id')]) }}
                {{ Form::hidden('uid', Crypt::encrypt(array_get($entry, 'id'))) }}
                {{ Form::hidden('contact', array_get($item, 'first_name').' '.array_get($item, 'last_name').' ('.array_get($item, 'email_1').')') }}
                {{ Form::hidden('cid',Crypt::encrypt( array_get($item, 'id'))) }}
                {{ Form::hidden('link_action', 'link_and_update') }}
                {{ Form::hidden('relationship', $relationship) }}
                <button type="button" class="btn btn-secondary btn-sm attach-form-entry pull-left" data-id="{{ array_get($item, 'id') }}" data-action="link_and_update">
                    @lang('Link and update contact')
                </button>
                {{ Form::close() }}
                <button type="button" class="btn btn-link btn-sm pull-left help-button" data-toggle="modal" data-target="#help-modal" data-content="This action will link this form to {{ array_get($item, 'first_name') }} {{ array_get($item, 'last_name') }} <strong>({{ array_get($item, 'email_1') }})</strong> and then contact data will be updated with values in this form.">
                    <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;"></i>
                </button>
                <!-- -->
                {{ Form::open(['route' => ['entries.update', array_get($entry, 'id')], 'method' => 'PUT', 'id' => 'link-'.array_get($item, 'id')]) }}
                {{ Form::hidden('uid', Crypt::encrypt(array_get($entry, 'id'))) }}
                {{ Form::hidden('contact', array_get($item, 'first_name').' '.array_get($item, 'last_name').' ('.array_get($item, 'email_1').')') }}
                {{ Form::hidden('cid',Crypt::encrypt( array_get($item, 'id'))) }}
                {{ Form::hidden('link_action', 'link') }}
                
                <button type="button" class="btn btn-secondary btn-sm attach-form-entry pull-left ml-4" data-id="{{ array_get($item, 'id') }}" data-action="link">
                    @lang('Link')
                </button>
                <button type="button" class="btn btn-link btn-sm pull-left help-button" data-toggle="modal" data-target="#help-modal" data-content="This action will link this form to {{ array_get($item, 'first_name') }} {{ array_get($item, 'last_name') }} <strong>({{ array_get($item, 'email_1') }})</strong> without updating contact data.">
                    <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;"></i>
                </button>
                {{ Form::close() }}
                <a class="btn btn-link pull-left" href="{{ route('contacts.show', ['id' => array_get($item, 'id')]) }}" target="_blank">{{ array_get($item, 'first_name') }} {{ array_get($item, 'last_name') }} ({{ array_get($item, 'email_1') }})</a>
                <!-- -->
            </td>
        </tr>
        @endforeach
    </tbody>
</table>