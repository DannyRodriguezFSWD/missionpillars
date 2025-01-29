<?php
function printFoldersTree($folder, $in) {
    ?>
    <li>
        <a class="folder" href="#">{{ $folder->name }}</a>
        <input type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTree($child, $in) ?>
            <?php endforeach; ?>
        </ol>
        @endif
        <ul>
            <?php
            foreach ($folder->tags as $tag) {
            ?>
                <li class="event-tag">
                    @if(in_array($tag->id, $in))
                    <input id="tag-{{ $tag->id }}" class="tag" type="checkbox" name="tags[]" value="{{ $tag->id }}" checked=""/>
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
    
    <?php
function printFoldersTreeNot($folder, $not) {
    ?>
    <li>
        <a class="folder" href="#">{{ $folder->name }}</a>
        <input type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTree($child, $not) ?>
            <?php endforeach; ?>
        </ol>
        @endif
        <ul>
            <?php
            foreach ($folder->tags as $tag) {
            ?>
                <li class="event-tag">
                    @if(in_array($tag->id, $not))
                    <input id="tag-{{ $tag->id }}" class="tag" type="checkbox" name="not[]" value="{{ $tag->id }}" checked=""/>
                    @else
                    <input id="tag-{{ $tag->id }}" class="tag" type="checkbox" name="not[]" value="{{ $tag->id }}"/>
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