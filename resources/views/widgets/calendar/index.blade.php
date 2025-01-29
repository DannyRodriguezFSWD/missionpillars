<div class="calendar-widget">
    <div class="grid-item ::CLASS::" id="::ID::">
        <div data-id="::ID::" data-order="::ORDER::">
            <div class="card">
                <div class="card-header">
                    @include('widgets.includes.drag-and-drop-icon') <span>::NAME::</span>
                    @include('widgets.calendar.fragments.menu')
                    @include('widgets.calendar.fragments.resize')
                </div>
                <div class="card-body">
                    <div class="canvas"></div>
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
</div>