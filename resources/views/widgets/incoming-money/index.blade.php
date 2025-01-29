<div class="incoming-money">
    <div class="grid-item ::CLASS::" id="::ID::">
        <div data-id="::ID::" data-order="::ORDER::">
            <div class="card">
                <div class="card-header">
                    @include('widgets.includes.drag-and-drop-icon') <span>::NAME::</span>
                    @include('widgets.incoming-money.fragments.menu')
                    @include('widgets.incoming-money.fragments.resize')
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <td>@lang('One time donations')</td>
                            <td class="text-right">
                                <strong>$::ONE_TIME_AMOUNT::</strong>
                            </td>
                            <td class="text-right">
                                <strong>::ONE_TIME_PERCENT::%</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('Pledges')</td>
                            <td class="text-right">
                                <strong>$::PLEDGES_AMOUNT::</strong>
                            </td>
                            <td class="text-right">
                                <strong>::PLEDGES_PERCENT::%</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('Recurring')</td>
                            <td class="text-right">
                                <strong>$::RECURRING_AMOUNT::</strong>
                            </td>
                            <td class="text-right">
                                <strong>::RECURRING_PERCENT::%</strong>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>@lang('Total')</strong></td>
                            <td class="text-right">
                            <span class="badge badge-primary badge-pill p-2">
                                <strong>$::TOTAL_AMOUNT::</strong>
                            </span>
                            </td>
                            <td class="text-right">
                            <span class="badge badge-primary badge-pill p-2">
                                <strong>::TOTAL_PERCENT::%</strong>
                            </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
</div>
