<div class="btn-group float-right mt-2">
    <button type="button" class="btn btn-transparent dropdown-toggle p-0 text-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-cog"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end">
        <button onclick="editWidget(this)" data-index="::INDEX::" class="dropdown-item">
            <span class="fa fa-edit"></span> @lang('Edit widget')
        </button>
        <button data-toggle="modal" data-target="#delete-widget" class="dropdown-item delete-widget" data-index="::INDEX::">
            <span class="fa fa-trash"></span> @lang('Delete widget')
        </button>
    </div>
</div>