@extends('emails.send.master-layout')
@section('content')
    <tr>
        <td class="content-cell">
            @if ($preview_text)
            @include('emails.send.includes.preview-text')
            @endif
            
            <?php echo $content; ?>
        </td>
    </tr>
@endsection