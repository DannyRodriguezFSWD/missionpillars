@foreach ($states->sortBy('name') as $state)
    <option value="{{$state->id}}" class="states_option">{{ $state->name }}</option>
@endforeach
