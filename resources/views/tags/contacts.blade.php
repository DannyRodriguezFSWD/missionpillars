@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    @include('widgets.back')
                </div>
                <ol class="breadcrumb">
                    @foreach($path as $folder)
                        <li class="breadcrumb-item">
                            <a href="{{ route('tags.show', ['id' => $folder->id]) }}">
                                {{ $folder->name }}
                            </a>
                        </li>
                    @endforeach
                    <li class="breadcrumb-item active">{{ $tag->name }}</li>
                </ol>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Country</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contacts as $contact)
                        <tr>
                            <td>{{ $contact->first_name }}</td>
                            <td>{{ $contact->last_name }}</td>
                            <td>{{ $contact->email_1 }}</td>
                            <td>{{ $contact->address ? $contact->address->country : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $contacts->links() }}
                <div class="card-footer">
                    &nbsp;
                </div>
            </div>
        </div>
        <!--/.col-->
    </div>
    <!--/.row-->

@endsection
