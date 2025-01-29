<div class="card">
    @include('reports.crm.includes.reports-component')
    <div class="card-body">
        <h3>{{ array_get($givers, 'contacts_in_range_1')->count() }} {{ array_get($report, 'name') }} who have not given between {{ $from }} - {{ $to }}</h3>
    </div>
    @include('reports.crm.includes.tables.report1_1')
    <div class="card-footer">&nbsp;</div>
</div>

<div class="card">
    <div class="card-header">&nbsp;</div>
    <div class="card-body">
        <h3>{{ array_get($givers, 'contacts_in_range_2')->count() }} {{ array_get($report, 'name') }} who have been giving between {{ $from2 }} - {{ $to2 }}</h3>
    </div>
    @include('reports.crm.includes.tables.report1_2')
    <div class="card-footer">&nbsp;</div>
</div>
