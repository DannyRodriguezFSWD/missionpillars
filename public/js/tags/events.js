var currentForm;
$('.edit-folder').on('click', function (e) {
    currentForm = $(this).data('form');
    $('#edit-folder-modal').find('input[name="folder"]').val($(this).data('name'));
});

$('.delete-folder').on('click', function (e) {
    currentForm = $(this).data('form');
    var msg = $('#delete-folder-modal').find('p').data('msg').replace(':folder:', $(this).data('name'));

    $('#delete-folder-modal').find('p').html(msg);
});

$('#edit-folder-modal').on('shown.bs.modal', function (e) {
    $('#folder', this).focus()
});

$('#button-update-folder').on('click', function (e) {
    var value = $('#edit-folder-modal').find('input[name="folder"]').val();
    var folder = $('#edit-folder-modal').find('select[name="parent"]').val();
    var currentFolder = $(currentForm).find('input[name="current_folder_parent_id"]').val();
    
    if( folder === currentFolder ){
        $('#alert-message').fadeIn();
        return false;
    }

    $(currentForm).find('input[name="name"]').val(value);
    $(currentForm).find('input[name="folder_parent_id"]').val(folder);
    $(currentForm).submit();
});

$('#button-delete-folder').on('click', function (e) {
    $(currentForm).submit();
});

/*-------TAGS------------*/
$('.edit-tag').on('click', function (e) {
    currentForm = $(this).data('form');
    $('#edit-tag-modal').find('input[name="tag"]').val($(this).data('name'));
});

$('.delete-tag').on('click', function (e) {
    currentForm = $(this).data('form');
    var msg = $('#delete-tag-modal').find('p').data('msg').replace(':tag:', $(this).data('name'));

    $('#delete-tag-modal').find('p').html(msg);
});

$('#edit-tag-modal').on('shown.bs.modal', function (e) {
    $('#tag', this).focus()
});

$('#button-update-tag').on('click', function (e) {
    
    var value = $('#edit-tag-modal').find('input[name="tag"]').val();
    var folder = $('#edit-tag-modal').find('select[name="parent"]').val();
    console.log(folder);
    $(currentForm).find('input[name="name"]').val(value);
    $(currentForm).find('input[name="folder_id"]').val(folder);
    $(currentForm).submit();
});

$('#button-delete-tag').on('click', function (e) {
    $(currentForm).submit();
});

/*----BOTH-----*/

$('#folderModal, #tagModal').on('shown.bs.modal', function (e) {
    $('input[type="text"]', this).focus()
});