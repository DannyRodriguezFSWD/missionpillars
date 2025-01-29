<?php
function printFoldersTree($folder) {
    ?>
    <li>
        <a class="folder" href="{{ route('groups.showFolder', ['id' => $folder->id]) }}">{{ $folder->name }}</a>
        <input checked type="checkbox" />

        @if(count($folder->getGroupsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getGroupsChildrenFolders as $child): ?>
                <?php printFoldersTree($child) ?>
            <?php endforeach; ?>
        </ol>
        @endif
    </li>
    <?php
}
?>

<?php
function printFoldersTreeToGroupContact($folder, $id) {
    ?>
    <li>
        <a class="folder" href="{{ route('contacts.groups', ['id' => $id, 'folder' => $folder->id]) }}">{{ $folder->name }}</a>
        <input checked type="checkbox" />

        @if(count($folder->getGroupsChildrenFolders) > 0)
        <ol>
            <?php foreach ($folder->getGroupsChildrenFolders as $child): ?>
                <?php printFoldersTreeToGroupContact($child, $id) ?>
            <?php endforeach; ?>
        </ol>
        @endif
    </li>
    <?php
}
?>