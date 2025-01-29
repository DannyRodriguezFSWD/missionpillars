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
                <?php printFoldersTreeNot($child, $not) ?>
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

    <?php
function printExcludeFoldersTree($folder) {
    ?>
    <li>
        <a class="folder" href="#">{{ $folder->name }}</a>
        <input type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printExcludeFoldersTree($child) ?>
            <?php endforeach; ?>
        </ol>
        @endif
        <ul>
            <?php
            foreach ($folder->tags as $tag) {
            ?>
                <li class="event-tag">
                    <input class="tag" type="checkbox" name="tags[]" value="{{ $tag->id }}"/>
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