<?php
function printFoldersTree($folder) {
    ?>
    <li>
        <a class="folder" href="{{ route('tags.show', ['id' => $folder->id]) }}">{{ $folder->name }}</a>
        <input checked type="checkbox" />

        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTree($child) ?>
            <?php endforeach; ?>
        </ol>
        @endif
    </li>
    <?php
}
?>

<?php
function printFoldersTreeToTagContact($folder, $id) {
    ?>
    <li>
        <a class="folder" href="{{ route('contacts.tags', ['id' => $id]) }}?folder={{ $folder->id }}">{{ $folder->name }}</a>
        <input checked type="checkbox" />

        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTreeToTagContact($child, $id) ?>
            <?php endforeach; ?>
        </ol>
        @endif
    </li>
    <?php
}
?>

<?php
function printFoldersTreeToExport($folder, $root, $id, $list, $listName) {
    ?>
    <li>
        <a class="folder" href="{{ route('mailchimp.addtags', ['id' => $id, 'list' => $list, 'folder' => $folder->id, 'listname' => $listName]) }}">{{ $folder->name }}</a>
        <input checked type="checkbox" /> 

        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTreeToExport($child, $root, $id, $list, $listName) ?>
            <?php endforeach; ?>
        </ol>
        @endif
    </li>
    <?php
}
?>

    <?php
function printFoldersTreeToDelete($folder, $root, $id, $list, $listName) {
    ?>
    <li>
        <a class="folder" href="{{ route('mailchimp.deletetags', ['id' => $id, 'list' => $list, 'folder' => $folder->id, 'listname' => $listName]) }}">{{ $folder->name }}</a>
        <input checked type="checkbox" /> 

        @if(count($folder->getTagsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getTagsChildrenFolders as $child): ?>
                <?php printFoldersTreeToDelete($child, $root, $id, $list, $listName) ?>
            <?php endforeach; ?>
        </ol>
        @endif
    </li>
    <?php
}
?>