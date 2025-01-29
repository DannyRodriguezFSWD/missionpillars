<div class="gs-videos">
    <div class="grid-item ::CLASS::" id="::ID::">
        <style>
        .gs-video-card {
            margin-bottom: 20px;
        }
        .gs-video-card .card-header {
            font-weight: bold;
            text-align: center;
        }
        .gs-video-card .card-body {
            text-align: center;
            position: relative;
        }
        </style>
        <div data-id="::ID::" data-order="::ORDER::">
            <div class="card gs-videos-widget-card">
                <div class="card-header">
                    @include('widgets.includes.drag-and-drop-icon') <span>::NAME::</span>
                    @include('widgets.fragments.delete')
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 col-xl-6">
                            <div class="card gs-video-card">
                                <div class="card-header">Church and Donor Management</div>
                                <div class="card-body">
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/GbN_LIGTbjw" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                </div>
                                <div class="card-footer">Watch this video to get the most out of your church or donor management account!</div>
                            </div>
                        </div>

                        <div class="col-sm-12 col-xl-6">
                            <div class="card gs-video-card">
                                <div class="card-header">Accounting Guide</div>
                                <div class="card-body">
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/LO3jHrHP4qw" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                </div>
                                <div class="card-footer">Watch this video to get the most out of your fund accounting system!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
