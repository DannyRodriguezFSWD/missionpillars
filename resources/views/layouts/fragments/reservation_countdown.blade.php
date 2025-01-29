@php
    $agent = new \Jenssegers\Agent\Agent();
@endphp
<div class="row" id="time-left" style="display:none;">
    <div class="col-lg-8 offset-lg-2">
        <div class="alert alert-warning" role="alert">
            <div class="row">
                <div class="col-md-3">
                    <h1 id="countdown"></h1>
                </div>
                <div class="col-md-9">
                    <p>
                        We'll hold your registration for {{ env('TICKETS_TEMPORARY_HOLD', 10) }} minutes while you complete your registration. <br>
                        After {{ env('TICKETS_TEMPORARY_HOLD', 10) }} minutes we'll release the registration. So please complete your registration before then.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@isset($event_template)
    @if(array_get($event_template, 'allow_reserve_tickets'))
        @push('scripts')
            <script>
                var url = "{{ route('tickets.get.timeleft', ['register_id' => array_get($register, 'id')]) }}";
                var time_left = 0;
                var countdown_id = 0;
                function countdown(){
                    time_left--;
                    if(time_left < 0 && countdown_id != 0){
                        clearInterval(countdown_id);
                        countdown_id = 0;
                        Swal.fire('Your selected tickets will be released','','info');
                        window.location.href = "{{ \App\Classes\Redirections::get() }}";
                    }
                    else if(countdown_id != 0){
                        var countdown_time = new Date(time_left * 1000).toISOString().substr(14, 5);
                        $('#countdown').html(countdown_time);
                    }
                }

                function getRemainingTime(){
                    $.ajax(url).done(function(data){
                        if(data > 0){
                            time_left = data;
                            countdown_id =  setInterval(countdown, 1000);
                            $('#time-left').show();
                        }
                        else{
                            clearInterval(countdown_id);
                            window.location.href = "{{ \App\Classes\Redirections::get() }}";
                        }
                    }).fail(function(data){
                        console.log('getRemainingTime', data);
                    });
                }
                @if($agent->isMobile())
                $(window).focus(function() {
                    window.location.reload();
                })
                @endif

                @if(!isset($noTimer) || (isset($noTimer) && !$noTimer))
                getRemainingTime();
                @endif
            </script>
        @endpush
    @endif
@endisset
