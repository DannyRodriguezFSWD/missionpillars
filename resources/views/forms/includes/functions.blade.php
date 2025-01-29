<?php
function printFoldersTree($folder, $tags) {
    ?>
    <li>
        <a class="folder" href="fake_url">{{ $folder->name }}</a>
        <input type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTree($child, $tags) ?>
            <?php endforeach; ?>
        </ol>
        @endif
        <ul>
            <?php
            foreach ($folder->tags as $tag) {
            ?>
                <li class="event-tag">
                    @if( in_array($tag->id, $tags) )
                    <input id="tag-{{ $tag->id }}" class="tag" type="checkbox" checked="" name="tags[]" value="{{ $tag->id }}"/>
                    @else
                    <input id="tag-{{ $tag->id }}" class="tag" type="checkbox" name="tags[]" value="{{ $tag->id }}"/>
                    @endif
                    <span class="fa fa-tag"></span> {{ $tag->name }}
                </li>
            <?php
            }
            ?>
        </ul>
        
    </li>
    <?php
}
?>
