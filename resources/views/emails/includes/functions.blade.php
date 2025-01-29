<?php
function printIncludedFoldersTree($folder, $included) {
    ?>
    <li>
        <a class="folder" data-id="{{ array_get($folder, 'id') }}" href="#">{{ array_get($folder, 'name') }}</a>
        <input class="folder" data-id="{{ array_get($folder, 'id') }}" type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printIncludedFoldersTree($child, $included) ?>
    <?php endforeach; ?>
        </ol>
        @endif
        <ul data-folder="{{ array_get($folder, 'id') }}">
            <?php
            foreach ($folder->tags as $tag) {
                ?>
                <li class="event-tag">
                    @if(in_array($tag->id, $included))
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
function printExcludedFoldersTree($folder, $excluded) {
    ?>
    <li>
        <a class="folder" data-id="{{ array_get($folder, 'id') }}" href="#">{{ array_get($folder, 'name') }}</a>
        <input class="folder" data-id="{{ array_get($folder, 'id') }}" type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printExcludedFoldersTree($child, $excluded) ?>
    <?php endforeach; ?>
        </ol>
        @endif
        <ul data-folder="{{ array_get($folder, 'id') }}">
            <?php
            foreach ($folder->tags as $tag) {
                ?>
                <li class="event-tag">
                    @if(in_array($tag->id, $excluded))
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

function printMapTagEvent($folder) {
    ?>
    <li>
        <a class="folder" data-id="{{ array_get($folder, 'id') }}" href="#">{{ array_get($folder, 'name') }}</a>
        <input class="folder" data-id="{{ array_get($folder, 'id') }}" type="checkbox" />
        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
        <?php printMapTagEvent($child) ?>
    <?php endforeach; ?>
        </ol>
        @endif
        <ul data-folder="{{ array_get($folder, 'id') }}">
            <?php
            foreach ($folder->tags as $tag) {
                ?>
                <li class="event-tag">
                    <input id="tag-{{ $tag->id }}" class="tag" type="radio" name="selected_tag" value="{{ $tag->id }}"/>
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