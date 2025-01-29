<div ng-if="kpi.type === 'inverted_number_percent'">
    <div class="d-flex w-100 justify-content-between">
        <h6 class="mb-1"><% kpi.title %></h6>

        <h6 class="text-danger" ng-if="kpi.status === 'up'">
            <span class="badge badge-danger badge-pill p-1">
                <i class="fa fa-arrow-up"></i>
            </span>
            <% kpi.current.value %>
        </h6>

        <h6 class="text-primary" ng-if="kpi.status === 'equals'">
            <span class="badge badge-primary badge-pill p-1">=</span>
            <% kpi.current.value %>
        </h6>

        <h6 class="text-success" ng-if="kpi.status === 'down'">
            <span class="badge badge-success badge-pill p-1">
                <i class="fa fa-arrow-down"></i>
            </span>
            <% kpi.current.value %>
        </h6>
    </div>
    <p>
        <small><% kpi.description %></small>
    </p>

    <p ng-if="kpi.status === 'up'">
        <small class="text-danger">
            <span class="badge badge-danger badge-pill p-1">
                <i class="fa fa-arrow-up"></i>
            </span>
            <% kpi.current.number %> (<% kpi.current.percent %>%)
        </small>
        <small>
            @lang('from') <% kpi.last.number %> @lang('in') <% kpi.last.year %>
        </small>
    </p>

    <p ng-if="kpi.status === 'equals'">
        <small class="text-primary">
            <span class="badge badge-primary badge-pill p-1">=</span>
            <% kpi.current.number %> (<% kpi.current.percent %>%)
        </small>
        <small>
            @lang('from') <% kpi.last.number %> @lang('in') <% kpi.last.year %>
        </small>
    </p>

    <p ng-if="kpi.status === 'down'">
        <small class="text-success">
            <span class="badge badge-success badge-pill p-1">
                <i class="fa fa-arrow-down"></i>
            </span>
            <% kpi.current.number %> (<% kpi.current.percent %>%)
        </small>
        <small>
            @lang('from') <% kpi.last.number %> @lang('in') <% kpi.last.year %>
        </small>
    </p>
</div>