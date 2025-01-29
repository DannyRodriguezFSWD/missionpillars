<div class="kpi money">
    <li class="list-group-item justify-content-between">
        <div class="d-flex w-100 justify-content-between">
            <h6 class="mb-1">::KPI.TITLE::</h6>

            <h6 class="kpi-status text-::KPI.STATUS::">
                <span class="badge badge-::KPI.STATUS:: badge-pill p-1">
                    <i class="fa fa-arrow-::KPI.INDICATOR::"></i>
                </span>
                $::KPI.CURRENT.VALUE::
            </h6>
        </div>

        <p><small>::KPI.DESCRIPTION::</small></p>

        <p>
            <small class="text-::KPI.STATUS::">
                <span class="badge badge-::KPI.STATUS:: badge-pill p-1">
                    <i class="fa fa-arrow-::KPI.INDICATOR::"></i>
                </span>
                $::KPI.CURRENT.VALUE::
            </small>
            <small>
                @lang('from') $::KPI.LAST.VALUE:: @lang('in') $::KPI.LAST.VALUE::
            </small>
        </p>
    </li>
</div>