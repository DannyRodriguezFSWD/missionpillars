<div class="modal fade" id="checkinSettingsModal" tabindex="-1" role="dialog" aria-labelledby="checkinSettingsModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Checkin Settings</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @include('checkin.includes.settings.event')
                    
                    @include('checkin.includes.settings.group')
                    
                    @include('checkin.includes.settings.print')
                </div>
                
                @include('checkin.includes.settings.buttons')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
