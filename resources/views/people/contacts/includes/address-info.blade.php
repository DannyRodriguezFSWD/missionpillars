<div class="row">
    <div class="col-md-12">
        <h5>@lang('Address')</h5>
        <div class="btn-group float-right">
            <a class="btn btn-success" href="{{ route('addresses.create', ['id' => $contact->id]) }}">
                <i class="icon icon-location-pin"></i> 
                @lang('Add Address')
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <p>&nbsp;</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>@lang('Address')</th>
                    <th>@lang('City')</th>
                    <th>@lang('Region')</th>
                    <th>@lang('Postal Code')</th>
                    <th>@lang('Country')</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_get($contact, 'orderedAddresses', []) as $address)
                
                <tr>
                    <td>{{$address->mailing_address_1}}</td>
                    <td>{{$address->city}}</td>
                    <td>{{$address->region}}</td>
                    <td>{{$address->postal_code}}</td>
                    <td>{{ isset($address->countries) ? $address->countries->name : $address->country }}</td>
                    <td>
                        <a href="{{ route('addresses.edit', ['id' => $address->id]) }}" class="btn btn-link">
                            <span class="fa fa-edit"></span>
                        </a>
                    </td>
                    <td>
                        {{ Form::open(['route' => ['addresses.destroy', $address->id], 'method' => 'delete', 'id' => 'address-'.$address->id]) }}
                        {{ Form::hidden('uid', Crypt::encrypt($address->id)) }}
                        <button type="button" class="btn btn-link text-danger" data-id="address-{{ $address->id }}" data-toggle="modal" data-target="#delete-address-modal">
                            <span class="fa fa-trash"></span>
                        </button>
                        {{ Form::close() }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
    </div>
</div>