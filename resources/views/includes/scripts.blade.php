@stack('oauth')

<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('/js/app.js')) }}"></script>
<script src="{{ asset('js/MPHelper.js') }}"></script>
<script src="{{ asset('js/pace.min.js') }}"></script>
<script src="{{ asset('js/Chart.min.js') }}"></script>

@if (Route::currentRouteName() === 'contacts.edit')
<script src="{{ asset('js/choices.min.js') }}"></script>
@endif

<script src="{{ asset('js/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>

@if (!in_array(Route::currentRouteName(),['subscription.show','subscription.index']) && $trialModule && !$paymentOption)
    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('js/crm-software-billing-upgrade-modal.js') }}?v={{ filemtime(public_path('/js/crm-software-billing-upgrade-modal.js')) }}"></script>
@elseif (in_array(Route::currentRouteName(),['subscription.show','subscription.index']))
    <script src="https://js.stripe.com/v3/"></script>
@endif

<style>
/*** jQuery UI autocomplete styling **/

/* overrrides */

ul.ui-menu.ui-autocomplete {
    padding-right: 15px
}

ul.ui-menu.ui-autocomplete li .ui-menu-item-wrapper {
    white-space: nowrap;
    font-size: smaller;
}


/* custom widgets */

/* see custom.catcomplete below */
.ui-autocomplete-category {
    font-weight: bold;
    padding: .2em .4em;
    margin: .8em 0 .2em;
    line-height: 1.5;
}
</style>

<script type="text/javascript">


(function () {

    //
    $(".readonly").on('keydown paste', function (e) {
        e.preventDefault();
    });


    //
    jQuery(document).ready(function ($) {
        $(".clickable-row,.clickable-cell").click(function (e) {
            e.stopPropagation();
            switch ($(this).data("target")) {
                case 'blank':
                case '_blank':
                window.open($(this).data("href"), '_blank');
                break;

                default:
                window.location = $(this).data("href");
            }
        });
        
        $('.clickable-table').on('click', 'tr', function (e) {
            e.stopPropagation();
            switch ($(this).data("target")) {
                case 'blank':
                case '_blank':
                window.open($(this).data("href"), '_blank');
                break;

                default:
                window.location = $(this).data("href");
            }
        });
        
        @if( is_null(session('timezone')) && !isset($exception) )
        // Set session timezone by calculating offset in seconds and setting Ajax
        // NOTE: hours and minutes are not enough https://app.asana.com/0/1117069745037349/1181874597981603/f
        var rightNow = new Date();
        var jan1 = new Date(rightNow.getFullYear(),0,1);
        var jan1utc = new Date(Date.UTC(rightNow.getFullYear(),0,1));
        var std_time_offset = (jan1utc - jan1) / 1000;

        $.get("{{ route('app.set.timezone') }}", { 'offset_seconds' : std_time_offset})
        .done(function(data){
            if(data.timezone == ''){
                window.location.reload()
            }
        })
        .fail(function(data) {
            console.log(data.responseText);
        });
        @endif
    });


    //
    $('.calendar, .datepicker').datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '1910:{{ \Carbon\Carbon::now()->addYears(4)->year }}',
        dateFormat: 'yy-mm-dd'
    });


    /**
     * custom category complete. Adapted from https://jqueryui.com/autocomplete/#categories
     *
     * To implement ensure that source: is an array objects that include both
     * label and category attributes. The array should already be sorted by category
     * to ensure that all objects in the same category are displayed together
     *
     * see AccountsController::sortByChartCategory
     */
    $.widget( "custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super()

            this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
        },

        _renderMenu: function( ul, items ) {
            var that = this,
            currentCategory = "";
            $.each( items, function( index, item ) {
                var li;
                if ( item.category != currentCategory ) {
                    ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
                    currentCategory = item.category;
                }
                li = that._renderItemData( ul, item );
                if ( item.category ) {
                    li.attr( "aria-label", item.category + " : " + item.label );
                }
            });
        },
    });


    //
    var assign = [];
    var buttons = [];
    @if(in_array(Route::currentRouteName(), \App\Constants::VIEW_USES_MAIL_MERGE_CODES))
    buttons = [
        // '<button data-merge-code="[:name:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Full Name">Name</button>',
        // '<button data-merge-code="[:salutation:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Title + Preferred Name (or First name + Last name if Preferred Name is empty)">Salutation</button>',
        '<button data-merge-code="[:title:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Title (e.g., Mr., Mrs. Ms., ... )">Title</button>',
        '<button data-merge-code="[:preferred-fallback-to-first-last-name:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Preferred Name. If not present falls back to first and last name">Preferred Name /w Fallback to First Last</button>',
        '<button data-merge-code="[:preferred-name:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Preferred Name">Preferred Name</button>',
        // '<button data-merge-code="[:preferred-name:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Preferred Name">Preferred Name</button>',
        '<button data-merge-code="[:first-name:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using First Name">First Name</button>',
        '<button data-merge-code="[:last-name:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Last Name">Last Name</button>',
        '<button data-merge-code="[:contact-org-name:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using the contact\'s organization name">Contact Organization Name</button>',
        '<button data-merge-code="[:contact-org-title:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using the contact\'s organizational title">Contact Organization Title</button>',
        '<button data-merge-code="[:contact_id:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Contact ID">Contact ID</button>',
    ];
    assign.push('mergecodes');
    @endif

    var templates = [];

    @if(in_array(Route::currentRouteName(), \App\Constants::VIEW_USES_TRANSACTION_CODES)
    || isset($templates))
    buttons.push('<button data-merge-code="[:address:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using Address">Address</button>');
    buttons.push('<button data-merge-code="[:organization_name:]" type="button" class="dropdown-item btn-dropdown-item" title="Personalize your email using the your Organization Name">Your Organization Name</button>');
    buttons.push('<button data-merge-code="[:start_date:]" type="button" class="transaction-code dropdown-item btn-dropdown-item" title="Personalize your email using Start Date">Start Date</button>');
    buttons.push('<button data-merge-code="[:end_date:]" type="button" class="transaction-code dropdown-item btn-dropdown-item" title="Personalize your email using End Date">End Date</button>');
    buttons.push('<button data-merge-code="[:total_amount:]" type="button" class="transaction-code dropdown-item btn-dropdown-item" title="Personalize your email using the Total Amount">Total Amount</button>');
    buttons.push('<button data-merge-code="[:list_of_donations:]" type="button" class="transaction-code dropdown-item btn-dropdown-item" title="Shows a list of donations">List Of Donations</button>');
    //buttons.push('<button data-merge-code="[:funds_sumary:]" type="button" class="transaction-code dropdown-item btn-dropdown-item" title="Shows Funds Sumary">Funds Sumary</button>');
    buttons.push('<button data-merge-code="[:last_transaction_date:]" type="button" class="transaction-code dropdown-item btn-dropdown-item" title="Shows the date of the latest transaction">Last Transaction Date</button>');
    buttons.push('<button data-merge-code="[:last_transaction_amount:]" type="button" class="transaction-code dropdown-item btn-dropdown-item" title="Shows the amount of the latest transaction">Last Transaction Amount</button>');
    buttons.push('<button data-merge-code="[:last_transaction_purpose:]" type="button" class="transaction-code dropdown-item btn-dropdown-item" title="Shows the purpose of the latest transaction">Last Transaction Purpose</button>');
    @endif

    @if (isset($templates))
    assign.push('templates');
    @foreach($templates as $template)
    templates.push('<button data-template="{{ array_get($template, "id") }}" type="button" class="dropdown-item btn-dropdown-item">{{ array_get($template, "name") }}</button>');
    @endforeach
    @endif

    //Sticky toolbar buttons
    var top = 84;
    var px = 60;
    $(window).scroll(function () {
        var y = $(this).scrollTop();

        var button = $('#floating-buttons');
        if (y >= top) {
            button.css({
                'position': 'fixed',
                'top': px+'px',
                'right': '30px',
                'z-index': '99'
            });
        } else {
            button.removeAttr('style')
        }
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('a.alt-link').on('click', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        $.get("{{ route('dashboard.click.noc2g') }}", {link: url})
        .done(function(data){
            window.open(url, '_blank');
        })
        .fail(function(data) {
            console.log(data.responseText);
        });
    });
    
    $('[data-toggle="tooltip"]').tooltip();
})();

function copy(id){
    /* Get the text field */
    var copyText = document.getElementById(id);
    /* Select the text field */
    copyText.select();
    /* Copy the text inside the text field */
    document.execCommand("copy");
    /* Alert the copied text */
    Swal.fire("Copied the text: ",copyText.value,'success');
}
@if(auth()->check())
(async () => {
    let newMenuItems = await axios.get('/new_menu_items');
    newMenuItems = newMenuItems.data
    newMenuItems.forEach(item => {
        let elements = document.querySelectorAll(`a.c-sidebar-nav-link[href="${window.location.origin}${item.uri}"]`)
        elements.forEach(element => {
            if (element != null) {
                element.innerHTML += `<span class="badge badge-success">New</span>`
                element.setAttribute('data-toggle', 'tooltip')
                if (item.tool_tip) element.setAttribute('title', item.tool_tip);
                $(element).tooltip()
            }
        })
    })
})()

document.addEventListener('coreui_sidebar_setActiveLink_fired', function () {
    if (document.querySelectorAll('a.c-sidebar-nav-link.c-active').length == 0) {
        let breadCrumbEls = document.querySelectorAll('li.breadcrumb-item > a'); //Get possible parent from breadcrumb links.
        for (let i = breadCrumbEls.length - 1; i >= 0; i--) {
            if (document.querySelectorAll(`.c-sidebar-nav-link[href="${breadCrumbEls[i].href}"]`).length) {
                $(`.c-sidebar-nav-link[href="${breadCrumbEls[i].href}"]`).addClass('c-active')
                $(`.c-sidebar-nav-link[href="${breadCrumbEls[i].href}"]`).parents('.c-sidebar-nav-dropdown').addClass('c-show')
                break;
            }
        }
    }
})

@endif

var mergeTags = [
    //{name: 'Name', code: '[:name:]', title: 'Personalize your email using Full Name'},
    //{name: 'Salutation', code: '[:salutation:]', title: 'Personalize your email using Title + Preferred Name (or First name + Last name if Preferred Name is empty)'},
    {name: 'Title', code: '[:title:]', title: 'Personalize your email using Title (e.g., Mr., Mrs. Ms., ... )'},
    {name: 'Preferred Name /w Fallback to First Last', code: '[:preferred-fallback-to-first-last-name:]', title: 'Personalize your email using Preferred Name. If not present falls back to first and last name'},
    {name: 'Preferred Name', code: '[:preferred-name:]', title: 'Personalize your email using Preferred Name'},
    {name: 'First Name', code: '[:first-name:]', title: 'Personalize your email using First Name'},
    {name: 'Last Name', code: '[:last-name:]', title: 'Personalize your email using Last Name'},
    {name: 'Contact Organization Name', code: '[:contact-org-name:]', title: 'Personalize your email using the contact\'s organization name'},
    {name: 'Contact Organization Title', code: '[:contact-org-title:]', title: 'Personalize your email using the contact\'s organizational title'},
    {name: 'Contact ID', code: '[:contact_id:]', title: 'Personalize your email using Contact ID'},
    {name: 'Date Today', code: '[:date_today:]', title: 'Date Today'},
]

@if(in_array(Route::currentRouteName(), \App\Constants::VIEW_USES_TRANSACTION_CODES)
    || isset($templates))
    mergeTags.push({name: 'Address', code: '[:address:]', title: 'Personalize your email using Address'});
    mergeTags.push({name: 'Your Organization Name', code: '[:organization_name:]', title: 'Personalize your email using the your Organization Name'});
    mergeTags.push({name: 'Your Organization EIN', code: '[:ein:]', title: 'Personalize your email using the your Organization EIN'});
    mergeTags.push({name: 'Start Date', code: '[:start_date:]', title: 'Personalize your email using Start Date'});
    mergeTags.push({name: 'End Date', code: '[:end_date:]', title: 'Personalize your email using End Date'});
    mergeTags.push({name: 'Total Amount', code: '[:total_amount:]', title: 'Personalize your email using the Total Amount'});
    mergeTags.push({name: 'List Of Donations', code: '[:list_of_donations:]', title: 'Shows a list of donations'});
    mergeTags.push({name: 'Last Transaction Date', code: '[:last_transaction_date:]', title: 'Shows the date of the latest transaction'});
    mergeTags.push({name: 'Last Transaction Amount', code: '[:last_transaction_amount:]', title: 'Shows the amount of the latest transaction'});
    mergeTags.push({name: 'Last Transaction Purpose', code: '[:last_transaction_purpose:]', title: 'Shows the purpose of the latest transaction'});
    //mergeTags.push({name: 'Funds Sumary', code: '[:funds_sumary:]', title: 'Shows Funds Sumary'});
@endif

var templatesInTiny = [];

@if (isset($templates))
@foreach($templates->where('editor_type', 'tiny') as $template)
templatesInTiny.push({name: '{{ array_get($template, "name") }}', code: '{{ array_get($template, "id") }}' });
@endforeach
@endif

function initTinyEditor(options = null) {
    if (options === null) {
        options = {};
    }

    if (typeof(options.selector) == 'undefined') {
        options.selector = '.tinyTextarea';
    }

    if (typeof(options.height) == 'undefined') {
        options.height = 300;
    }

    if (typeof(options.toolbar) == 'undefined') {
        options.toolbar = 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | mailmerge';
    }

    if (typeof(options.plugins) == 'undefined') {
        options.plugins = 'preview powerpaste casechange importcss tinydrive searchreplace autolink autosave save directionality advcode visualblocks visualchars fullscreen image link template codesample table charmap pagebreak nonbreaking anchor tableofcontents insertdatetime advlist lists checklist wordcount editimage help formatpainter permanentpen pageembed charmap emoticons advtable';
    }

    if (typeof(options.setup) == 'undefined') {
        options.setup = function (editor) {
            editor.ui.registry.addMenuButton('mailmerge', {
                text: 'Mail Merge',
                fetch: function (callback) {
                    let items = [];

                    mergeTags.forEach(function (item, index) {
                        items.push({
                            type: 'menuitem',
                            text: item.name,
                            onAction: function () {
                                editor.insertContent(item.code);
                            }
                        });
                    });

                    callback(items);
                }
            });
        }
    }
    
    options.relative_urls = false;
    options.remove_script_host  = false;

    options.tinydrive_token_provider = "{{ route('tiny.jwt') }}";

    tinymce.init(options);
}

function renderImage(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function (e) {
            $(input.getAttribute('data-render-to')).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

const humanDateRange_today = "{{ date('Y-m-d') }}";
const humanDateRange_yesterday = "{{ date('Y-m-d', strtotime('-1 days')) }}";
const humanDateRange_last_week_start = "{{ date('Y-m-d', strtotime('last sunday midnight', strtotime('-1 week +1 day'))) }}";
const humanDateRange_last_week_end = "{{ date('Y-m-d', strtotime('next saturday', strtotime('last sunday midnight', strtotime('-1 week +1 day')))) }}";
const humanDateRange_this_week_start = "{{ date('Y-m-d', strtotime('last sunday midnight', strtotime('today'))) }}";
const humanDateRange_this_week_end = "{{ date('Y-m-d', strtotime('next saturday', strtotime('today'))) }}";
const humanDateRange_last_month_start = "{{ date('Y-m-d', strtotime('first day of last month')) }}";
const humanDateRange_last_month_end = "{{ date('Y-m-d', strtotime('last day of last month')) }}";
const humanDateRange_this_month_start = "{{ date('Y-m-01') }}";
const humanDateRange_this_month_end = "{{ date('Y-m-d', strtotime('last day of this month')) }}";
const humanDateRange_last_year_start = "{{ date('Y-m-d', strtotime('last year January 1st')) }}";
const humanDateRange_last_year_end = "{{ date('Y-m-d', strtotime('last year December 31st')) }}";
const humanDateRange_this_year_start = "{{ date('Y-m-d', strtotime('January 1st')) }}";
const humanDateRange_this_year_end = "{{ date('Y-m-d', strtotime('December 31st')) }}";

var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
</script>

<script>
    let simpleConfettiFireWorks = function () {
        let count = 200;
        let defaults = {
            origin: { y: 0.7 }
        };

        let fire = function(particleRatio, opts) {
            confetti(Object.assign({}, defaults, opts, {
                particleCount: Math.floor(count * particleRatio)
            }));
        }

        fire(0.25, {
            spread: 26,
            startVelocity: 55,
        });
        fire(0.2, {
            spread: 60,
        });
        fire(0.35, {
            spread: 100,
            decay: 0.91,
            scalar: 0.8
        });
        fire(0.1, {
            spread: 120,
            startVelocity: 25,
            decay: 0.92,
            scalar: 1.2
        });
        fire(0.1, {
            spread: 120,
            startVelocity: 45,
        });
    };

    function isValidFileImage(file) {
        return ['jpg','jpeg','gif','png'].includes(file.type.split('/').pop().toLowerCase())
    }

    $(document).on('show.coreui.modal', '.modal', function() {
        const zIndex = 1040 + 10 * $('.modal:visible').length;
        $(this).css('z-index', zIndex);
        setTimeout(() => $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack'));
    });

    $(document).on("hidden.coreui.modal", function (e) {
        if ($('.modal:visible').length) {
            $('body').addClass('modal-open');
        }
    });
    
    function customDelay(fn, ms) {
        let timer = 0
        return function(...args) {
            clearTimeout(timer)
            timer = setTimeout(fn.bind(this, ...args), ms || 0)
        }
    }
    
    let isLoading = false;
    
    function customAjax(params) {
        if (!isLoading) {
            $.ajax({
                url: params.url,
                type: params.type ? params.type : 'post',
                dataType: params.dataType ? params.dataType : 'json',
                data: params.data,
                processData: (params.processData || params.processData === false) ? params.processData : true,
                contentType: (params.contentType || params.contentType === false) ? params.contentType : 'application/x-www-form-urlencoded; charset=UTF-8',
                cache: (params.cache || params.cache === false) ? params.cache : true,
                beforeSend: function () {
                    isLoading = true;
                    
                    if (params.beforeSend) {
                        params.beforeSend();
                    } else {
                        $('#overlay').fadeIn();
                    }
                },
                success: function (response) {
                    params.success(response);

                    isLoading = false;
                    $('#overlay').fadeOut();
                },
                error: function (e, textStatus, errorThrown) {
                    isLoading = false;
                    $('#overlay').fadeOut();
                    
                    @if (config('app.env') === 'local')
                    writeConsole(e.responseText)
                    if (params.error) {
                        params.error(e, textStatus, errorThrown)
                    }
                    @else
                    if (params.error) {
                        params.error(e, textStatus, errorThrown)
                    } else {
                        Swal.fire('An error occurred, please try again later', '', 'error');
                    }
                    @endif
                }
            });
        }
    }
    
    function writeConsole(content) {
        top.consoleRef=window.open('','myconsole',
            'width=750,height=550'
            +',menubar=0'
            +',toolbar=0'
            +',status=0'
            +',scrollbars=1'
            +',resizable=1')

        if (top.consoleRef) { // may be blocked by browser
            top.consoleRef.document.writeln(
                '<html><head><title>Console</title></head>'
                +'<body bgcolor=white onLoad="self.focus()">'
                +content
                +'</body></html>'
            )
            top.consoleRef.document.close()
        }
    }
    
    /**
     * Opens a Boostrap (https://getbootstrap.com/docs/4.0/components/modal/#modal-components) confirmation box
     * @param  {[string]} title      The modal title
     * @param  {[string]} message    The message to use in the modal
     * @param  {[function]} confirmed    Optional. A function to call when the modal is confirmed
     * @param  {[string]} oktext     Optional. Text to use for the 'Ok' button
     * @param  {[function]} confirmed    Optional. A function to call when the modal is closed/canceled
     * @param  {[string]} canceltext Optional. Text to use for the 'Cancel' button
     * @return {[Promise]}            use .then and .catch to handle true/false values
     */
    function confirmMessage(title, message, confirmed, oktext, canceled, canceltext) {
        if (!oktext) oktext = 'Ok'
        if (!canceltext) canceltext = 'Cancel'
        var formattedMessage = "<p>" + message.replace(/\n\n/g, "</p><p>") + "</p>"

        if (!document.getElementById('confirm-message')) {
            jQuery('body').prepend('<div id="confirm-message" class="modal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+title+'</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">'+formattedMessage+'</div><div class="modal-footer"><button type="button" class="okbutton btn btn-primary" data-dismiss="modal">'+oktext+'</button><button type="button" class="cancelbutton btn btn-secondary" data-dismiss="modal">'+canceltext+'</button></div></div></div></div>');
        }
        else if (document.getElementById('confirm-message')) {
            // set new text and cancel prior message
            jQuery("#confirm-message .modal-title").text(title);
            jQuery('#confirm-message .modal-body').html(formattedMessage)
            jQuery('#confirm-message .okbutton').html(oktext)
            jQuery('#confirm-message .cancelbutton').html(canceltext)

            // clear data
            jQuery('#confirm-message').data('confirmed',null)
            jQuery('#confirm-message').off('hidden.coreui.modal')
        }

        jQuery('#confirm-message .okbutton').one('click',function(){
            if (typeof confirmed == 'function') confirmed()
        })

        // setup new canceled handlers on hidden
        jQuery('#confirm-message').on('hidden.coreui.modal', function(e) {
            if (canceled && document.activeElement !== document.querySelector('#confirm-message .okbutton')) canceled()
        })

        jQuery('#confirm-message').modal({show: true})
    }
    
    function dispatchToast(title_,body_,timestamp_,toastId,type_,icon_, toastDelay_) {
        var title = title_ !== undefined ? title_ : 'Toast Title';
        var body = body_ !== undefined ? unescape(body_) : 'Toast Body';
        var timestamp = timestamp_ !== undefined ? timestamp_ : 'just now';
        var _toastId = toastId !== undefined ? toastId : 'defaultToast';
        var type = type_ !== undefined ? type_ : '';
        var icon = icon_ !== undefined ? icon_ : 'fa fa-exclamation';
        var toastDelay = toastDelay_ !== undefined ? toastDelay_ : 2500
        var toastId_ = _toastId + Date.now()
        var toastContainer = '<div aria-live="polite" id="toastContainer" aria-atomic="true" style="position: fixed; top: 60px; right: 5px; z-index: 9999;"></div>'
        var newToast = '<div class="toast '+type+'" id="'+toastId_+'" role="alert" aria-live="assertive" aria-atomic="true">'
            + '<div class="toast-header">'
            + '<i class="'+icon+' mr-2"></i>'
            + '<strong class="mr-auto">'+title+'</strong>'
            + '<small class="text-muted">'+timestamp+'</small>'
            + '<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">'
            + '<span aria-hidden="true">&times;</span>'
            + '</button>'
            + '</div>'
            + '<div class="toast-body p-0">'
            + '<textarea class="form-control" readonly style="overflow: hidden">'+body+'</textarea>'
            +'</div>'
            +'</div>';

        if (!$('#toastContainer').length) {
            $('body').prepend(toastContainer);
        }

        if (!$("#".concat(toastId_)).length) {
            $('#toastContainer').append(newToast);
        }

        new coreui.Toast(document.querySelector("#".concat(toastId_)), {
            delay: toastDelay
        });
        $("#".concat(toastId_)).toast('show');
    }
    
    function parseResponseJSON(responseJSON, concat = '<br>') {
        let errorMessage = '';

        errorMessage = Object.values(responseJSON).join(concat)

        return errorMessage;
    }
    
    $.fn.scrollPaginate = function(params) {
        let container = $(this);
        let scroll = params.scroll ? params.scroll : 'window';
        let scrollDirection = params.scrollDirection ? params.scrollDirection : 'bottom';
        let page = 1;
        let data = params.data ? params.data : {};
        let lastPage = params.lastPage ? params.lastPage : 1;
        let previousSearch = new Date().getTime();
        let loadingView = params.loadingView ? params.loadingView : '@include('_partials.loading')';
        
        if (params.search) {
            $('<input type="hidden" name="last_page" value="'+params.lastPage+'" />').insertAfter(container);
            $('<input type="hidden" name="last_search" value="'+previousSearch+'" />').insertAfter(container);
            var lastPageInput = container.parent().find('[name="last_page"]');
            var lastSearchInput = container.parent().find('[name="last_search"]');
        }
        
        if (scroll === 'window') {
            $(window).scroll(function (e) {
                if (params.search) {
                    data.search = params.search.val();

                    if (lastSearchInput.val() > previousSearch) {
                        page = 1;
                        previousSearch = lastSearchInput.val();
                    }

                    lastPage = lastPageInput.val();
                }
                
                if (params.sort) {
                    data.sort = $(params.sort).data('sort');
                    data.sortType = $(params.sort).data('sort-type');
                }

                let gotAllPages = page >= lastPage ? true : false;
                
                if (scrollDirection === 'bottom') {
                    var scrollingDone = $(this).scrollTop() + $(this).height() === $(document).height();
                } else {
                    var scrollingDone = $(this).scrollTop() === 0;
                }

                if (!gotAllPages && scrollingDone) {
                    customAjax({
                        url: params.url+'?page='+(page+1),
                        data: params.data,
                        beforeSend: function () {
                            if (scrollDirection === 'bottom') {
                                container.append(loadingView);
                            } else {
                                container.prepend(loadingView);
                            }
                        },
                        success: function (response) {
                            page++;
                            lastPage = response.lastPage;
                            container.find('[data-loading="true"]').remove();
                            
                            if (scrollDirection === 'bottom') {
                                container.append(response.html);
                            } else {
                                container.prepend(response.html);
                            }
                        }
                    });
                }
            });
        } else  {
            scroll.scroll(function (e) {
                if (params.search) {
                    data.search = params.search.val();

                    if (lastSearchInput.val() > previousSearch) {
                        page = 1;
                        previousSearch = lastSearchInput.val();
                    }

                    lastPage = lastPageInput.val();
                }
                
                if (params.sort) {
                    data.sort = $(params.sort).data('sort');
                    data.sortType = $(params.sort).data('sort-type');
                }

                let gotAllPages = page >= lastPage ? true : false;
                
                if (scrollDirection === 'bottom') {
                    var scrollingDone = $(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight;
                } else {
                    var scrollingDone = $(this).scrollTop() === 0;
                }

                if (!gotAllPages && scrollingDone) {
                    customAjax({
                        url: params.url+'?page='+(page+1),
                        data: params.data,
                        beforeSend: function () {
                            if (scrollDirection === 'bottom') {
                                container.append(loadingView);
                            } else {
                                container.prepend(loadingView);
                            }
                        },
                        success: function (response) {
                            page++;
                            lastPage = response.lastPage;
                            container.find('[data-loading="true"]').remove();
                            
                            if (scrollDirection === 'bottom') {
                                container.append(response.html);
                            } else {
                                let oldHeight = container.height();
                                container.prepend(response.html);
                                scroll.scrollTop(container.height() - oldHeight);
                            }
                        }
                    });
                }
            });
        }
    }
    
    function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes == 0) return '0 Byte';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }
    
    function truncateString(str, num) {
        if (str.length > num) {
            return str.slice(0, num) + "...";
        } else {
            return str;
        }
    }
    
    function customFileUpload(params) {
        let formData = new FormData();           
        formData.append('file', params.button.prop('files')[0]);
        formData.append('folder', params.folder);
        formData.append('relation_id', params.relation_id);
        formData.append('relation_type', params.relation_type);

        customAjax({
            url: '{{ route('documents.store') }}',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function (response) {
                params.success(response);
            },
            error: function (e) {
                if (e.responseJSON) {
                    Swal.fire('Validation Error', parseResponseJSON(e.responseJSON), 'error');
                }
                params.button.val(null);
            }
        });
    }
    
    function customDocumentDelete(button) {
        Swal.fire({
            title: 'Remove this attachment?',
            type: 'question',
            showCloseButton: true,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return customAjax({
                    url: $(button).parent('[data-docuemnt-container="true"]').find('form').attr('action'),
                    type: 'delete',
                    data: {},
                    success: function () {
                        if ($(button).parents('[data-attachments-container="true"]').find('[data-docuemnt-container="true"]').length === 1) {
                            $('[data-attachments-container="true"]').addClass('d-none');
                        }
                        $(button).parent('[data-docuemnt-container="true"]').remove();
                        Swal.fire('Success!', 'Attachment was removed successfully.', 'success');
                    }
                });
            }
        });
    }
    
    function countChar(el, max) {
        var count = $(el).val().length;
        var countElement = $('[data-char-limit="'+$(el).prop('name')+'"]');
        
        if (count >= max) {
            countElement.html(max);
            countElement.parent().addClass('text-danger font-weight-bold');
            
            if (countElement.parent().find('.fa-exclamation-triangle').length === 0) {
                countElement.parent().prepend('<i class="fa fa-exclamation-triangle" data-toggle="tooltip" title="Character limit has been reached. Larger text do not always get delivered per phone spam rules."></i> ');
                countElement.parent().find('.fa-exclamation-triangle').tooltip();
            }
        } else {
            countElement.html(count);
            countElement.parent().removeClass('text-danger font-weight-bold');
            countElement.parent().find('.fa-exclamation-triangle').remove();
        }
    }
    
    function downloadImage(url){
        var img = document.createElement('img');
        img.src = url;
        img.crossOrigin = 'anonymous';
        img.onload = function(){
            var canvas = document.createElement("canvas");
            canvas.width = img.width;
            canvas.height = img.height;
            var ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0);
            var dataURL = canvas.toDataURL("image/png");
            var link = document.createElement('a');
            link.target = '_blank';
            link.href = dataURL
            link.download = 'qr.png';
            document.body.appendChild(link);
            link.click();
            link.parentNode.removeChild(link);
        }
    }
    
    function customSelectSearch(el, name) {
        let search = $(el).val().toLowerCase();
        $('[aria-labelledby="select-search-dropdown-'+name+'"] [data-val]').each(function () {
            if ($(this).attr('data-val').toLowerCase().includes(search)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
    
    function customSelectSearchOption(el, name) {
        let key = $(el).data('key');
        let val = $(el).data('val');
        
        $('#select-search-dropdown-'+name).html(val);
        $('[name="'+name+'"]').val(key);
        
        $('[aria-labelledby="select-search-dropdown-'+name+'"] [data-val]').removeClass('active');
        $(el).addClass('active');
    }
    
    $('[name="email_assignee_due"]').change(function () {
        if ($(this).prop('checked')) {
            $(this).parents('form').find('.emailDueContainer').removeClass('d-none');
        } else {
            $(this).parents('form').find('.emailDueContainer').addClass('d-none');
        }
    });
    
    function mpSetCookie(name, data, daysToLive) {
        let dateExpireCookie = new Date();
        dateExpireCookie.setDate(dateExpireCookie.getDate() + daysToLive); 
        document.cookie = name + "=" + data + "; expires="+ dateExpireCookie +";";
    }

    function mpGetCookie(name) {
        const value = '; ' + document.cookie;
        const parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    }
    
    function mpArrayToCSV(data) {
        return data.map(row => row.map(item => `"${item}"`).join(",")).join("\n");
    }
</script>
@stack('scripts')

