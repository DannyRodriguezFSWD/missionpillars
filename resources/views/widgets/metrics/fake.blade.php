<div class="card metric" ng-if="widget.type === 'fake'" id="widget-<% widget.id %>">
    <div class="card-header">
        @include('widgets.includes.drag-and-drop-icon') <% widget.name %>
        @include('widgets.metrics.fragments.menu')
        @include('widgets.includes.resize-widget')
    </div>
    <div class="card-body">
        <canvas></canvas>
    </div>
    <div class="card-footer">&nbsp;</div>
</div>