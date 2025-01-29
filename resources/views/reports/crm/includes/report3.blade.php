<div class="card">
    @include('reports.crm.includes.reports-component')
    <div class="card-body">
        <h3>Purposes found ({{ count($givers) }})</h3>
    </div>
    @include('reports.crm.includes.tables.report3')
    <div class="card-footer">&nbsp;</div>
</div>