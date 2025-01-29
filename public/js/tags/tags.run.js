$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var app = angular.module('TagsApplication', []);

app.controller('Tags', function ($scope, $http, $location, $timeout) {
    var triggerButton;
    $scope.folder = {id: 0, uid: 0, name: '', folders: [], tags: [], url: {create: '', update: '', delete: '', content: ''}};
    $scope.tag = {id: 0, uid: 0, name: '', url: {create: '', update: '', delete: ''}};

    $('ol.tree').on('click', 'label.folder', function (e) {
        $scope.folder.id = $(this).data('id');
        $scope.folder.uid = $(this).data('uid');
        $scope.folder.name = $(this).html();
        $('input[name="parent"]').val( $(this).data('id') );
        $('#uid').val( $(this).data('uid') );
        $http.post($scope.folder.url.content, $scope.folder).then(function (result) {
            if (result.data !== false) {
                $scope.folder.folders = result.data.folders;
                $scope.folder.tags = result.data.tags;
            } else {
                $('#errorModal').modal();
            }
        });
        $scope.$apply();
    });

    /**
     * This will create the new folder item
     * */
    $('#save-folder').on('click', function (e) {
        if ($(triggerButton).val() === 'edit') {
            editFolder();
        } else {
            saveFolder();
        }
    });

    function editFolder() {
        let folder = {id: $scope.folder.id, uid: $scope.folder.uid, item: {id: triggerButton.data('id'), uid: triggerButton.data('uid'), name: $('#folder').val()}};
        $http.post($scope.folder.url.update, folder).then(function (result) {
            if (result.data !== false) {
                $scope.folder.folders = result.data.folders;
                $scope.folder.tags = result.data.tags;
            } else {
                $('#errorModal').modal();
            }
        });
    }

    function saveFolder() {
        let folder = {id: $scope.folder.id, uid: $scope.folder.uid, item: {name: $('#folder').val()}};
        if (inputIsEmpty(folder.item.name)) {
            $http.post($scope.folder.url.create, folder).then(function (result) {
                if (result.data !== false) {
                    let parent = $("ol.tree").find(`[data-parent='${result.data.parent.id}']`);
                    let ol = $(parent).find('ol:first');
                    if (!ol.length) {
                        $(parent).addClass();
                        $(parent).append(`<ol><li data-parent="${result.data.folder.id}"><label class="folder" data-id="${result.data.folder.id}" data-uid="${result.data.folder.uid}" >${result.data.folder.name}</label><input type="checkbox" /></li></ol>`);
                    } else {
                        $(ol).append(`<li data-parent="${result.data.folder.id}"><label class="folder" data-id="${result.data.folder.id}" data-uid="${result.data.folder.uid}" >${result.data.folder.name}</label><input type="checkbox" /> </li>`)
                    }
                    $scope.folder.folders = result.data.folders;
                    $scope.folder.tags = result.data.tags;
                } else {
                    $('#errorModal').modal();
                }
            });
        } else {
            console.log('validation worked');
        }
    }

    function inputIsEmpty(value) {
        if (value.trim() === "" || value === "" || value === null || value.trim() === null) {
            return false;
        }
        return true;
    }

    $('#folderModal').on('shown.bs.modal', function (event) {
        triggerButton = $(event.relatedTarget);
        if ($(triggerButton).val() === 'edit') {
            $('#folder').val($(triggerButton).data('name'));
        }
        $('#folder').focus()
    });

    $('#deleteFolderModal').on('show.bs.modal', function (event) {
        triggerButton = $(event.relatedTarget);
        var modal = $(this)
        var p = modal.find('.modal-body p');
        var msg = p.data('msg').replace(':folder:', '<b>' + triggerButton.data('name') + '</b>');
        p.html(msg);
    });

    $('#delete-folder').on('click', function (e) {
        let folder = {id: $scope.folder.id, uid: $scope.folder.uid, item: {id: triggerButton.data('id'), uid: triggerButton.data('uid')}};

        $http.post($scope.folder.url.delete, folder).then(function (result) {
            $scope.folder.folders = result.data.folders;
            $scope.folder.tags = result.data.tags;
            let li = $("ol.tree").find(`[data-parent='${result.data.delete}']`);
            $(li).remove();
        });
    });

    $('#save-tag').on('click', function (e) {
        if ($(triggerButton).val() === 'edit') {
            editTag();
        } else {
            saveTag();
        }
    });

    function editTag() {
        let tag = {id: $scope.folder.id, uid: $scope.folder.uid, item: {id: triggerButton.data('id'), uid: triggerButton.data('uid'), name: $('#tag').val()}};
        if (inputIsEmpty(tag.item.name)) {
            $http.post($scope.tag.url.update, tag).then(function (result) {
                if (result.data !== false) {
                    $scope.folder.folders = result.data.folders;
                    $scope.folder.tags = result.data.tags;
                } else {
                    $('#errorModal').modal();
                }
            });
        } else {
            console.log('validation worked');
        }
    }

    function saveTag() {
        let tag = {id: $scope.folder.id, uid: $scope.folder.uid, item: {name: $('#tag').val()}};

        if (inputIsEmpty(tag.item.name)) {
            $http.post($scope.tag.url.create, tag).then(function (result) {
                if (result.data !== false) {
                    $scope.folder.folders = result.data.folders;
                    $scope.folder.tags = result.data.tags;
                } else {
                    $('#errorModal').modal();
                }
            });
        } else {
            console.log('validation worked');
        }
    }

    $('#tagModal').on('shown.bs.modal', function (event) {
        triggerButton = $(event.relatedTarget);
        if ($(triggerButton).val() === 'edit') {
            $('#tag').val($(triggerButton).data('name'));
        }
        $('#tag').focus()
    });

    $('#deleteTagModal').on('show.bs.modal', function (event) {
        triggerButton = $(event.relatedTarget);
        var modal = $(this)
        var p = modal.find('.modal-body p');
        var msg = p.data('msg').replace(':tag:', '<b>' + triggerButton.data('name') + '</b>');
        p.html(msg);
    });

    $('#delete-tag').on('click', function (e) {
        let tag = {id: $scope.folder.id, uid: $scope.folder.uid, item: {id: triggerButton.data('id'), uid: triggerButton.data('uid')}};

        $http.post($scope.tag.url.delete, tag).then(function (result) {
            if (result.data !== false) {
                $scope.folder.folders = result.data.folders;
                $scope.folder.tags = result.data.tags;
            } else {
                $('#errorModal').modal();
            }
        });
    });

});