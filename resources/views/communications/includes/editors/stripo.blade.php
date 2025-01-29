<div class="form-group mt-4">
    <div class="row">
        <div class="col-12">
            {{ Form::mpButton('Load Another Template', ['class' => 'load_template_dnd btn-info']) }}
            {{ Form::mpButton('<i class="fa fa-edit"></i> Update Loaded Template', ['class' => 'update_template_dnd btn-warning', 'style'=>'display: none']) }}
            {{ Form::mpButton('<i class="fa fa-save"></i> Save As New Template', ['class' => 'save_template_dnd btn-success']) }}
            <!--<center><button class="btn btn-success" type="button" onclick="sendStripoEmail();"><i class="fa fa-paper-plane"></i> Save and Close</button></center>-->
        </div>
    </div>
</div>

<div class="form-group">
    <div id="stripoSettingsContainer" style="width: 400px;height: 1000px;float: left;">Loading...</div>
    <div id="stripoPreviewContainer" style="width: calc(100% - 400px);height: 1000px;float: left;"></div>
</div>

<!-- TODO do a proper fix for this -->
<style>
.esdev-app .nav-tabs.nav-justified>li {
    display: table-cell;
    width: 50%;
}
</style>

<script>
    function sendStripoEmail() {
        window.StripoApi.compileEmail((error, html, ampHtml, ampErrors) => {
            $("input[name='content']").val(html);
            $('#btn_email').trigger('click');
        });
    }
    
    function request(method, url, data, callback) {
        let req = new XMLHttpRequest();
        req.onreadystatechange = function () {
            if (req.readyState === 4 && req.status === 200) {
                callback(req.responseText);
            } else if (req.readyState === 4 && req.status !== 200) {
                console.error('Can not complete request. Please check you entered a valid PLUGIN_ID and SECRET_KEY values');
            }
        };
        req.open(method, url, true);
        if (method !== 'GET') {
            req.setRequestHeader('content-type', 'application/json');
        }
        req.send(data);
    }

    function loadDemoTemplate(callback) {
        request('GET', 'https://raw.githubusercontent.com/ardas/stripo-plugin/master/Public-Templates/Custom-Templates/Finance/Promo-newsletter/Promo-newsletter.html', null, function(html) {
            request('GET', 'https://raw.githubusercontent.com/ardas/stripo-plugin/master/Public-Templates/Custom-Templates/Finance/Promo-newsletter/Promo-newsletter.css', null, function(css) {
                callback({html: html, css: css});
            });
        });
    }
    
    function initPlugin(template) {
        window.Stripo.init({
            settingsId: 'stripoSettingsContainer',
            previewId: 'stripoPreviewContainer',
            codeEditorButtonId: 'codeEditor',
            undoButtonId: 'undoButton',
            redoButtonId: 'redoButton',
            locale: 'en',
            html: template.html,
            css: template.css,
            apiRequestData: {
                emailId: 123
            },
            getAuthToken: function (callback) {
                request('GET', "{{ route('stripo.getauthtoken') }}", null,
                    function(data) {
                        callback(JSON.parse(data).token);
                    });
            }
        });
    }

    loadDemoTemplate(initPlugin);
</script>