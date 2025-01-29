@extends('emails.send.master-layout')
@section('content')
<tr>
    <td>
        <table style="background-color: #20A8D8; padding: .50rem">
            <tbody>
            <tr>
                <td class="content-cell" style="background: #FFFFFF;">
                    <?php echo $content; ?>
                </td>
            </tr>
            </tbody>
        </table>
    </td>
</tr>
@endsection