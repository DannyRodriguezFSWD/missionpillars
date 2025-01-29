<div class="card">
    @include('reports.crm.includes.reports-component')
    <div class="card-body">
        <h3>{{ array_get($report, 'name') }} ({{ $givers->count() }})</h3>
    </div>
    @include('reports.crm.includes.tables.report0')
    <div class="card-footer">&nbsp;</div>
</div>