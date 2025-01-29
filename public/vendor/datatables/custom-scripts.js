

$.fn.dataTable.ext.buttons.saveSearch = {
    text: function (dt) {
        return '<i class=\"fa fa-save\"></i> ' + dt.i18n('buttons.saveSearch', 'Save Search');
    },
    
    className: 'buttons-saveSearch',
    
    action: function (e, dt, button, config) {
        $('#saveSearchModal').data('callback', function(name) {
            var url = dt.ajax.url() + '/state'
            var data = dt.state()
            data.name = name ? name : "saveSearch_" + Date.now()
            data.is_user_search = 1
            
            $.post(url, data, function (r) {
                if (r.result) {
                    Swal.fire('Success', 'List saved successfully', 'success');
                }
            })
        })
        
        // simulate click away
        $('body').trigger('click')
        $('#saveSearchModal').modal('show')
        $('#saveSearchModal').find('input,select').filter(':first').focus()
    }
};



$.fn.dataTable.ext.buttons.updateSearch = {
    text: function (dt) {
        return '<i class=\"fa fa-save\"></i> ' + dt.i18n('buttons.saveSearch', 'Update Search');
    },
    
    className: 'buttons-updateSearch',
    
    action: function (e, dt, button, config) {
        var url = dt.ajax.url() + '/state'
        var data = dt.state()
        data.is_user_search = 1
        data.updateState = selectedState

        $.post(url, data, function (r) {
            if (r.result) {
                Swal.fire('Success', 'List updated successfully', 'success');
            }
        })
    }
};



$.fn.dataTable.ext.buttons.emailOrPrint = {
    text: function (dt) {
        return '<i class=\"fa fa-envelope\"></i> ' + dt.i18n('buttons.emailOrPrint', 'Create Communication');
    },
    
    className: 'buttons-emailOrPrint',
    
    action: function (e, dt, button, config) {
        var url = dt.ajax.url() + '/communication'
        var data = dt.state()
        data.name = name ? name : "saveSearch_list_" + Date.now()
        
        $.post(url, data, function (r) {
            console.log(r);
            if (!r.communication || !r.communication.id) { 
                console.log('failed to create communication')
                // simulate click away
                $('body').trigger('click')
                return
            }
            location.href = `/crm/communications/${r.communication.id}/edit`
        })
    }
};



$.fn.dataTable.ext.buttons.sendSms = {
    text: function (dt) {
        return '<i class=\"fa fa-commenting\"></i> ' + dt.i18n('buttons.sendSms', 'Send SMS');
    },
    
    className: 'buttons-sendSms',
    
    action: function (e, dt, button, config) {
        var url = dt.ajax.url() + '/sms'
        var data = dt.state()
        data.name = name ? name : "saveSearch_list_" + Date.now()
        
        $.post(url, data, function (r) {
            console.log(r);
            if (!r.sms || !r.sms.id) { 
                console.log('failed to create SMS Message')
                // simulate click away
                $('body').trigger('click')
                return
            }
            location.href = `/crm/communications/sms/${r.sms.id}/edit`
        })
    }
};

$.fn.dataTable.ext.buttons.pictureDirectory = {
    text: function (dt) {
        return '<i class=\"fa fa-file-pdf-o\"></i> ' + dt.i18n('buttons.pictureDirectory', 'Picture Directory');
    },
    
    className: 'buttons-pictureDirectory',
    
    action: function (e, dt, button, config) {
        var url = dt.ajax.url() + '/state'
        var data = dt.state()
        data.name = name ? name : "saveSearch_" + Date.now()

        $.post(url, data, function (r) {
            if (r.result) {
                location.href = dt.ajax.url() + '/' + r.state.id + `/pdf-picture-directory`;
            }
        })
    }
};

$.fn.dataTable.ext.buttons.excel = {
    text: function (dt) {
        return '<i class=\"fa fa-file-excel-o\"></i> ' + dt.i18n('buttons.excel', 'Excel');
    },
    
    className: 'buttons-excel',
    
    action: function (e, dt, button, config) {
        var url = dt.ajax.url() + '/state'
        var data = dt.state()

        $.post(url, data, function (r) {
            if (r.result) {
                location.href = dt.ajax.url() + '/' + r.state.id + `/excel`;
            }
        })
    }
};

// copied from public/vendor/datatables/buttons.server-side.js
// console.log('custom scripts')
var buildDtUrl = function(dt, action) {
    var url = dt.ajax.url() || '';
    var params = dt.ajax.params();
    params.action = action;
    
    if (url.indexOf('?') > -1) {
        return url + '&' + $.param(params);
    }
    
    return url + '?' + $.param(params);
};
