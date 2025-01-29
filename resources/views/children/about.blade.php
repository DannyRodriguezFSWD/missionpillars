@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('child-checkin.about') !!}
@endsection
@section('title')
    Child Check-In
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            
            <h3>
                Welcome to your Child Check-In!
            </h3>
            
            <p>
                With this feature, you can set up a self service check-in kiosk allowing people to print out safety labels for this children.
            </p>
            
            <div class="card text-center">
                <div class="card-header">
                    Your Child Check In Link
                </div>
                <div class="card-body" style="padding: 25px">
                    <div class="input-group">
                        
                        {{-- <a href="{{ route('checkin.index') }}" target="_blank"> --}}
                        {{ Form::text('checkin-url', route('checkin.index'), ['class' => 'form-control', 'readonly' => true, 'id' => 'checkin-url']) }}
                    {{-- </a> --}}
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" style="width: 100px;" onclick="copy('checkin-url')">
                                <i class="fa fa-copy"></i>
                                Copy&nbsp;
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <h4 class="text-center">
            </h4>
            
            <p>
                <h5>
                    The process will print two labels for each child.  
                </h5>
                <ol>
                    <li>
                        Navigate to the above link on the designated computer (see Getting Set Up, below)
                    </li>
                    <li>
                        Place one label on the child
                    </li>
                    <li>
                        The parent/guardian will keep the second label and use this as a ticket to pick up their child. 
                    </li>
                </ol>
            </p>
            <div class="card text-center">
                <div class="card-header">
                    Video Instructions
                </div>
                <div class="card-body">
                    <p>&nbsp;</p>
                    <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/fMehSGz3GEw?rel=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <p>&nbsp;</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h4>
                        Getting Set Up
                    </h4>
                    <ol>
                        <li>
                            Purchase a label printer and label paper <i>(<a href="https://continue-to-give-kiosk-center.myshopify.com/collections/printing-supplies" target="_blank">Click here to purchase</a>)</i> 
                        </li>
                        <li>
                            Install the printer on a computer where people will check their children in
                        </li>
                        <li>
                            Open your Child Check-In link in a browser on that computer 
                            <ul>
                                <li>
                                    Chrome or Edge
                                </li>
                                <li>
                                    Setup the print settings to ensure that the labels print as expected
                                    <ul>
                                        <li>
                                            Orientation: Landscape
                                        </li>
                                        <li>
                                            Paper Size: 1.1" x 3.5"
                                        </li>
                                        <li>
                                            Margins: 0" for top, bottom, left and right or minimum margins available
                                        </li>
                                        <li>
                                            Headers and footers: Off
                                        </li>
                                        <li>
                                            Change Scale to fit ~150
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    To apply these settings 
                                    <ul>
                                        <li>Print the page, apply the settings in the displayed print preview dialog, and click print</li>
                                        <li>If that does not work, access <b>File</b> (ALT+F if hidden) > <b>Page Setup</b></li>
                                    </ul>
                                </li>
                                <li>
                                    <i>Recommended: put the browser in full-screen mode</i>
                                    <ul>
                                        <li>MacOS: Press the <em>F5</em> key</li>
                                        <li>Windows: Press the <em>F11</em> key</li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            Advanced instructions for automatic print (without print dialog box): <a href="https://www.youtube.com/watch?v=vis_0dM9Jug" target="_blank">Click Here</a>
                        </li>
                        <li>
                            People can now do a self check-in and print their own labels. 
                        </li>
                        <li>
                            Use the extra parent label as the confirmation ticket to pick up the child
                        </li>
                    </ol>

                    <p>
                        That’s it. We hope you like the simplicity of this safety feature! 
                    </p>
                </div>
                <div class="col-sm-6 col-md-3">
                    <h5>Google Chrome Example</h5>
                    <a href="{{ asset('public/img/child-checkin/Chrome.jpg') }}" target="_blank">
                        <img class="img-fluid" src="{{ asset('public/img/child-checkin/Chrome.jpg') }}" alt="Print settings google chrome" style="max-height: 500px;">
                    </a>
                </div>
                <div class="col-sm-6 col-md-3">
                    <h5>Microsoft Edge Example</h5>
                    <a href="{{ asset('public/img/child-checkin/Edge.jpg') }}" target="_blank">
                        <img class="img-fluid" src="{{ asset('public/img/child-checkin/Edge.jpg') }}" alt="Print settings microsoft edge" style="max-height: 500px;>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
