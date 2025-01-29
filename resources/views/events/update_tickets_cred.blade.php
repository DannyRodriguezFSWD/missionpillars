@extends('layouts.auth-forms')

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card my-2 card-accent-info">
                <div class="card-header">
                    <h3 class="text-center card-title">@lang('Please confirm the name of each ticket holder')</h3>
                </div>
                <div class="card-body">
                    <form action="{{route('events.ticket_credentials')}}" method="POST">
                        {{csrf_field()}}
                        <div class="row">
                            @foreach($tickets as $key => $ticket)
                                <div class="col-12">
                                    <div class="card text-black bg-secondary ticket_card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <div class="font-weight-bold">Ticket {{ $key+1 }}/{{ $tickets->count() }} "{{array_get($ticket,'ticket_name')}}":</div>
                                                <div class="font-weight-bold text-primary text-right">${{array_get($ticket,'price')}}</div>
                                            </div>
                                            <input type="hidden" name="ticket_id[]" value="{{array_get($ticket,'id')}}">
                                            <div class="form-group">
                                                <input required placeholder="First Name" name="first_name[]" type="text" class="form-control-sm form-control" @if($key === 0) value="{{array_get($contact,'first_name')}}" @endif>
                                            </div>
                                            <div class="form-group">
                                                <input required placeholder="Last Name" name="last_name[]" type="text" class="form-control-sm form-control" @if($key === 0) value="{{array_get($contact,'last_name')}}" @endif>
                                            </div>
                                            <div class="form-group">
                                                <input required placeholder="Email" name="email[]" type="email" class="form-control-sm form-control" @if($key === 0) value="{{array_get($contact,'email_1')}}" @endif>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="col-12 text-right">
                                <button class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
