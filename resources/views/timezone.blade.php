@extends('layouts.app')

@section('content')

{{ \Carbon\Carbon::now() }}

{{ Form::open(['route' => 'examples.store']) }}
<button type="submit">submit</button>
{{ Form::close() }}

@push('scripts')
<script type="text/javascript">
    (function () {
        var timezone_offset_minutes = new Date().getTimezoneOffset();
        timezone_offset_minutes = timezone_offset_minutes === 0 ? 0 : -timezone_offset_minutes;

// Timezone difference in minutes such as 330 or -360 or 0
        console.log(timezone_offset_minutes);

    })();
</script>
@endpush

@endsection
