<div class="chart-widget">
    <div class="grid-item ::CLASS::" id="::ID::">
        <div data-id="::ID::" data-order="::ORDER::">
            <div class="card metric">
                <div class="card-header">
                    @include('widgets.includes.drag-and-drop-icon') <span>::NAME::</span>
                    @include('widgets.metrics.fragments.menu')
                    @include('widgets.metrics.fragments.resize')
                </div>
                <div class="card-body">
                    <canvas></canvas>
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
</div>
