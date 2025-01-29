<div class="modal fade" id="view-log-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Duplicated profiles merged successfully')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 500px !important; overflow: auto;">
                <table id="log" class="table table-striped table-bordered">
                    <tbody></tbody>
                </table>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@push('scripts')
<script>
    function log(result){
        if(result){
            var i = 1;
            resultemail = result.contact.email_1 ? result.contact.email_1 :'';
            if (resultemail && result.contact.email_2) {
                resultemail =  resultemail + " / " + result.contact.email_2;
            }
            
            if (result.contact.type === 'organization') {
                resultname = result.contact.company+" ["+resultemail+"]";
                resultname_possessive = result.contact.company+"'s ["+resultemail+"]";
            } else {
                resultname = result.contact.first_name+' '+result.contact.last_name+" ["+resultemail+"]";
                resultname_possessive = result.contact.first_name+' '+result.contact.last_name+"'s ["+resultemail+"]";
            }
            
            tagnames = ""
            
            result.data.forEach(function(item){
                var msg = itememail = '';
                if (['contact','relationship'].includes(item.type)) {
                    itememail = item.model.email_1 ? item.model.email_1 :'';
                    if (itememail && item.model.email_2) {
                        itememail =  itememail + " / " + item.model.email_2;
                    }
                    
                    if (item.model.type === 'organization') {
                        var itemname = item.model.company+' ['+itememail+']';
                    } else {
                        var itemname = item.model.first_name+' '+item.model.last_name+' ['+itememail+']';
                    }
                }
                    
                if(item.type == 'contact'){
                    msg = '<b>'+itemname+'</b> profile merged into <b>'+resultname_possessive+"</b> profile";
                }
                else if(item.type == 'altid'){
                    msg = '<b>'+item.model.label+' ('+item.model.system_created_by+ ' contact - ' + item.model.alt_id+')</b> will be synced with <b>'+resultname_possessive+"</b> profile"
                }
                else if(item.type == 'email_addresses') {
                    for (const index in item.model)  {
                        msg += '<b>'+item.model[index]+'</b> added to to <b>'+resultname_possessive+'</b> profile as <b>Email '+ index +'</b>.';
                    }
                }
                else if(item.type == 'transaction'){
                    msg = '<b>$'+item.model.amount+' '+item.model.status+' transaction</b> added to <b>'+resultname_possessive+"</b> transactions";
                }
                else if(item.type == 'form'){
                    msg = 'Form <b>'+item.model.form.name+'</b> added to <b>'+resultname_possessive+"</b> forms";
                }
                else if(item.type == 'address'){
                    msg = 'Address <b>'+item.model.mailing_address_1+' '+item.model.city+', '+item.model.region+'. '+item.model.country+'</b> added to <b>'+resultname_possessive+"</b> addresses";
                }
                else if(item.type == 'event'){
                    msg = 'All tickets bought/reserved and checkins for <b>'+item.model.event.template.name+' event</b> were transfered to <b>'+resultname+"</b>";
                }
                else if(item.type == 'relationship'){
                    msg = '<b>'+itemname+'</b> profile was added as <b>'+item.model.pivot.relative_relationship+' of '+resultname+"</b>";
                } 
                else if (item.type == 'tag') {
                    msg = 'The <b>'+item.model.name+'</b> tag will be applied to <b>'+resultname_possessive+"</b> profile";
                }
                else if (item.type === 'note') {
                     msg = 'The note <b>'+item.model.title+'</b> will be added to <b>'+resultname_possessive+"</b> profile";
                }
                $('#log').find('tbody').append('<tr><td>'+i+'</td><td>'+msg+'</td></tr>');
                i++;
            });
            
        }
        $('#view-log-modal').modal('show');
    }
</script>
@endpush
