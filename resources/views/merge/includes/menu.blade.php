<div class="float-right pb-2" id="floating-buttons">
    <a class="btn btn-primary {{ Route::current()->getName() == 'merge.index' ? 'active':'' }}" href="{{ route('merge.index') }}">
        <span class="fa fa-clone"></span>
        @lang('Show Duplicates')
    </a>
    <a class="btn btn-primary {{ Route::current()->getName() == 'merge.individual' ? 'active':'' }}" href="{{ route('merge.individual') }}">
        <span class="fa fa-user"></span>
        @lang('Merge Individual')
    </a>
</div>