@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('tasks') !!}
@endsection
@section('content')
<div id="crm-tasks">
    <tasks-list
        v-bind:endpoint="'{{ route('tasks.index') }}'"
        v-bind:display="'tasks'"
        v-bind:owners="{{ json_encode($allOwners) }}"
    >
    </tasks-list>
</div>

@include('tasks.includes.create')
@include('tasks.includes.list')
@push('scripts')
    <script src="{{ mix('js/tasks.js') }}"></script>
@endpush

@endsection