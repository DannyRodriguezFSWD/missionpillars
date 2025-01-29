<div class="form-group">
    <label class="d-block">{{ array_get($field, 'name') }}</label>
    
    @if (array_get($field, 'type') === 'text')
    
    {{ Form::text(array_get($field, 'code'), array_get($field, 'value'), ['class'=>'form-control mp_custom_field']) }}
    
    @elseif (array_get($field, 'type') === 'textarea')
    
    {{ Form::textarea(array_get($field, 'code'), array_get($field, 'value'), ['class'=>'form-control mp_custom_field', 'rows' => 2]) }}
    
    @elseif (array_get($field, 'type') === 'integer')
    
    {{ Form::number(array_get($field, 'code'), array_get($field, 'value'), ['class'=>'form-control mp_custom_field', 'step' => 1]) }}
    
    @elseif (array_get($field, 'type') === 'decimal')
    
    {{ Form::number(array_get($field, 'code'), array_get($field, 'value'), ['class'=>'form-control mp_custom_field', 'step' => 0.01]) }}
    
    @elseif (array_get($field, 'type') === 'date')
    
    {{ Form::text(array_get($field, 'code'), array_get($field, 'value'), ['class' => 'form-control datepicker readonly mp_custom_field', 'autocomplete' => 'off']) }}
    
    @elseif (array_get($field, 'type') === 'datetime')
    
    <div class="row">
        <div class="col-6">
            {{ Form::text(array_get($field, 'code').'_date', substr(array_get($field, 'value'), 0, 10), ['class' => 'form-control datepicker readonly mp_custom_field', 'autocomplete' => 'off']) }}
        </div>
        
        <div class="col-6">
            <select name="{{ array_get($field, 'code').'_time' }}" class="form-control mp_custom_field">
                @include('events.includes.time-options')
            </select>
        </div>
    </div>
    
    @if (array_get($field, 'value'))
    @push('scripts')
    <script>
        $('[name="{{ array_get($field, 'code').'_time' }}"]').val('{{ substr(array_get($field, 'value'), 11) }}');
    </script>
    @endpush
    @endif
    
    @elseif (array_get($field, 'type') === 'select')
    
    {{ Form::select(array_get($field, 'code'), array_get($field, 'optionsArray'), array_get($field, 'value'), ['class' => 'form-control mp_custom_field']) }}
    
    @elseif (array_get($field, 'type') === 'multiselect')
    
    {{ Form::select(array_get($field, 'code'), array_get($field, 'optionsArray'), array_get($field, 'value'), ['class' => 'form-control mp_custom_field mp_custom_field_multiselect', 'multiple' => true]) }}
    
    @endif
</div>
