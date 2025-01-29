<?php
function printFoldersTree($folder) {
    ?>
    <li>
        <a class="folder" href="fake_url">{{ $folder->name }}</a>
        <input type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTree($child) ?>
            <?php endforeach; ?>
        </ol>
        @endif
        <ul>
            <?php
            foreach ($folder->tags as $tag) {
            ?>
                <li class="event-tag">
                    <input id="tag-{{ $tag->id }}" class="tag" type="checkbox" name="tags[]" value="{{ $tag->id }}"/>
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

<?php
function printFoldersTreeToCheckIn($folder, $tagsArray, $event) {
    ?>
    <li>
        <a class="folder" href="fake_url">{{ $folder->name }}</a>
        <input type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTreeToCheckIn($child, $tagsArray, $event) ?>
            <?php endforeach; ?>
        </ol>
        @endif
        
        <ul>
            <?php
            foreach ($folder->tags as $tag) {
                if( in_array($tag->id, $tagsArray)  ){
            ?>

                <li class="event-tag">
                    <input id="tag-{{ $tag->id }}" class="tag" type="checkbox" name="tags[]" value="{{ $tag->id }}"/>
                    <span class="fa fa-tag"></span> {{ $tag->name }}
                </li>
            <?php
                }
            }
            ?>
            
        </ul>
        
    </li>
    <?php
}
?>
@include('events.includes.actions-event-modal')