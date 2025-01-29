<div class="welcome">
    <div class="grid-item ::CLASS::" id="::ID::">
        <div data-id="::ID::" data-order="::ORDER::">
            <div class="card">
                <div class="card-header">
                    @include('widgets.includes.drag-and-drop-icon') <span>::NAME::</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <h5>@lang('Your Dashboard, Your Design.')</h5>
                            <p>@lang('Get the most out of your dashboard with customizable widgets. You can add and remove key metrics so your most important data is available at your fingertips.')</p>
                            <div class="text-center">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#widget-welcome-tour">
                                    @lang('Learn how')
                                </button>
                                &nbsp;or&nbsp;
                                <button class="btn btn-secondary delete-widget" data-index="::INDEX::" data-toggle="modal" data-target="#delete-widget">
                                    @lang('Delete this widget')
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
</div>