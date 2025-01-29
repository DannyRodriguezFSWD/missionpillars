@foreach($duplicates as $item)
<div class="row">
    <div class="col-sm-1 mt-4">
        <input type="radio" {!! $loop->first ? 'checked="checked"':'' !!} name="item" value="{{ array_get($item, 'id') }}"/>
    </div>
    <div class="col-sm-11">
        <h2>
            @if (array_get($item, 'type') === 'organization')
                {{ array_get($item, 'company') }}
            @else
                {{ array_get($item, 'first_name') }} {{ array_get($item, 'last_name') }}
            @endif
        </h2>
        <p class="mb-0">
            @if ($item->email_1 && $item->email_1 != 'NULL')
                Email 1: {{ array_get($item, 'email_1') }}
                @if ($item->email_2 && $item->email_2 != 'NULL')
                    <br>
                    Email 2: {{ array_get($item, 'email_2') }}
                @endif
            @else
                &nbsp;
            @endif
        </p>
        <p>
            @if ($item->created_at)
                <small>Created at {{ displayLocalDateTime(array_get($item, 'created_at'))->toDayDateTimeString() }}</small>
            @endif
            @if ($item->updated_at)
                <small>Last updated {{ displayLocalDateTime(array_get($item, 'updated_at'))->toDayDateTimeString() }}</small>
            @endif
        </p>
        <p class="text-right">
            <button class="btn btn-sm btn-primary" onclick="view_contact({{array_get($item,'id')}})"><i class="fa fa-eye"></i> View Timeline</button>
            <a href="{{route('contacts.show', array_get($item,'id'))}}" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-external-link-square"></i> View Contact</a>
        </p>
    </div>
</div>
<hr/>
@endforeach
<script>
    function view_contact(id){
        let route = '{{route('ajax.merge.view.contact')}}'
        $('#overlay').show()
        axios.get(route,{
            params:{
                id: id
            }
        }).then(res => {
            Swal.fire({
                html: res.data,
            })
            $('#overlay').hide()
        });
    }
</script>