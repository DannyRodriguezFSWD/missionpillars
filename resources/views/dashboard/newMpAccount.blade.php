<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <title>Document</title>
</head>
<style>
    .celebImage{
        width: 600px;
        position: absolute;
        left: 50%;
        bottom: 0;
        margin-left: -300px
    }
</style>
<body>

<div class="d-none" id="wrapper">
    <div style="display: none" class="row p-4 hide-on-start">
        <div class="col-md-12 py-4">
            <h1 class="display-4 text-center">Watch these Mission Pillars getting started videos.</h1>
        </div>
        <div class="col-md-6">
            <div class="card gs-video-card">
                <div class="card-header">
                    <h5 class="card-title text-center">Church and Donor Management</h5>
                </div>
                <div class="card-body">
                    <iframe style="min-height: 400px; min-width: 100%" src="https://www.youtube.com/embed/GbN_LIGTbjw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
                </div>
                <div class="card-footer font-weight-bold">Watch this video to get the most out of your church or donor management account!</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card gs-video-card">
                <div class="card-header">
                    <h5 class="card-title text-center">Accounting Guide</h5>
                </div>
                <div class="card-body">
                    <iframe style="min-height: 400px; min-width: 100%" src="https://www.youtube.com/embed/LO3jHrHP4qw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
                </div>
                <div class="card-footer font-weight-bold">Watch this video to get the most out of your fund accounting system!</div>
            </div>
        </div>
        <div class="col text-center">
            <a href="/" class="btn btn-primary btn-lg gotoDashboard">View Your Dashboard</a>
        </div>
    </div>
    <img class="celebImage" src="{{asset('img/widgets/celebration.gif')}}" alt="">
</div>

<script src="{{asset('js/confetti.browser.min.js')}}"></script>
<script src="{{ asset('js/app.js') }}?t={{ filemtime(public_path('/js/app.js')) }}"></script>
<script src="{{ asset('js/MPHelper.js') }}"></script>

<script src="{{ asset('js/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
<script>
    (function(){
        $('#wrapper').removeClass('d-none')
        Swal.fire({
            html: `
            <div class="display-4">Excellent, you have just created your Mission Pillars account!</div>
            <p>Our advanced AI team has begun importing all your contacts and transaction history from Continue To Give.
                       <br>This will happen in the background. So feel free to use your account!</p>
                        `,
            backdrop: `rgba(0,0,0,0)`,
            background: `rgb(237,237,237)`
        }).then(() => {
            $('.hide-on-start').fadeIn(1000);
            $('.celebImage').remove()
        })


        let duration = 5000;
        let animationEnd = Date.now() + duration;
        let defaults = { startVelocity: 20, spread: 360, ticks: 90 };

        function randomInRange(min, max) {
            return Math.random() * (max - min) + min;
        }

        let interval = setInterval(function() {
            let timeLeft = animationEnd - Date.now();

            if (!Swal.isVisible()) {
                return clearInterval(interval);
            }

            let particleCount = 200;
            // since particles fall down, start a bit higher than random
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
        }, 400);
    })()
</script>
</body>
</html>