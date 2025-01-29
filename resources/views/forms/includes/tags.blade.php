@include('forms.includes.functions')
<div class="row">
    <div class="col-sm-12">
        <ol class="tree">
            <?php printFoldersTree($tree, $tags); ?>
        </ol>
    </div>
</div>
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush
@push('scripts')
<script type="text/javascript">
    (function(){
        $('a.folder').on('click', function(e){
            e.preventDefault();
            var parent = $(this).parent('li');
            var checked = parent.find('input.tag', this).prop('checked');
            parent.find('input.tag', this).prop('checked', !checked);
        });
    })();
</script>
@endpush