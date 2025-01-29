<?php

namespace App;

/**
 * Constants to use in Laravel way
 *
 * @author josemiguel
 */
class Constants {
    /**
     * Constant for using with redirects literals
     */
    //const HTTP = 'http://';
    //const APP_NAME = 'missionpillars';
    /**
     * main domain for redirects
     */
    //const APP_DOMAIN = 'app.qa.missionpillars.com';
    //const MAIN_DOMAIN = 'qa.missionpillars.com';
    //const SUBDOMAIN_REDIRECT_URL = 'http://[:subdomain:].qa.missionpillars.com/oneclick?';
    
    /*API*/
    const API = [ 'REQUEST_JSON_TYPE' => 'application/json' ];
    //const C2G = [
        //'API_URL' => 'http://demo-api.continuetogive.com/',
    //];

    /**
     * URL to consume json service
     */
    const API_DATA_URL = 'http://app.missionpillars.com/public/datas2.json';
    const API_DATA_SINGLE = 'http://app.missionpillars.com/public/single-transaction.json';

    /**
     * Default system creator
     */
    const DEFAULT_SYSTEM_CREATED_BY = 'Continue to Give';

    /**
     * Constant to search in json web service result
     */
    const CHART_OF_ACCOUNT_SUBTYPE_ORGANIZATIONS = 'organizations';

    /**
     * Constant to search in json web service result
     */
    const CHART_OF_ACCOUNT_SUBTYPE_PROJECTS = 'projects';

    /**
     * Constant to search in json web service result
     */
    const CHART_OF_ACCOUNT_SUBTYPE_GIVINGPAGES = 'givingpages';

    /**
     * Constant to search in json web service result
     */
    const CHART_OF_ACCOUNT_SUBTYPE_MISSIONARY = 'missionary';
    const CHART_OF_ACCOUNT_SUBTYPE_PURPOSE = 'purpose';
    const TAG_SYSTEM = [
        'FOLDERS' => [
            'ALL_TAGS' => 1,
            'AUTO_GENERATED' => 2,
            'CHART_OF_ACCOUNTS' => 3,
            'CAMPAIGNS' => 4,
            'GROUPS' => 5,
            'EVENTS' => 6,
            'FORMS' => 7,
            'TRANSACTION_PATHS' => 8,
            'DEVICES' => 9,
            'TYPE' => ['TAG_FOLDER' => 'TAGS', 'GROUP_FOLDER' => 'GROUPS'],
            'GROUP' => 'Group :name:',
            'FORM' => 'Form :name:',
        ],
        'TAGS' => [
            'DONOR' => 1,
            'MISSIONARY' => 2,
            'FOUNDRISER' => 3,
            'RECURRING_TRANSACTION' => 4,
            'PLEDGE' => 7,
            'FORM_RESPONDENT' => 8,
            'GROUP' => 'Group Members :name:',
            'FORM' => 'Form :name:',
            'EVENT' => 'Event :name:'
        ]
    ];
    const GROUPS_SYSTEM = [
        'FOLDERS' => [
            'ALL_GROUPS' => 10,
        ],
        'GROUPS' => [
            'GROUP_MEMBER' => 5,
            'GROUP_LEADER' => 6
        ]
    ];
    
    const DEVICE_CATEGORY = [
        'PHONE' => 'phone',
        'TABLET' => 'tablet',
        'DESKTOP' => 'desktop',
        'LAPTOP' => 'laptop'
    ];
    const TRANSACTION_PATH = [
        'TEXT' => ['SEARCH_FOR' => 'moduleType=Module_SMS&task=choosetask', 'VALUE' => 'text', 'SEARCH_IN' => 'url'],
        'KIOSK' => ['SEARCH_FOR' => 'kiosk.', 'VALUE' => 'kiosk', 'SEARCH_IN' => 'url'],
        'BADGE' => ['SEARCH_FOR' => 'moduleType=Module_Badge', 'VALUE' => 'badge', 'SEARCH_IN' => 'url,referrer'],
        'IFRAME' => ['SEARCH_FOR' => 'iframe.', 'VALUE' => 'embedded form', 'SEARCH_IN' => 'url,referrer'],
        'FACEBOOK' => ['SEARCH_FOR' => 'facebookbadge.', 'VALUE' => 'facebook', 'SEARCH_IN' => 'referrer'],
        'GIVERS_APP' => ['SEARCH_FOR' => 'giverapp.', 'VALUE' => 'givers app', 'SEARCH_IN' => 'url'],
        'TEXT_LINK' => ['SEARCH_FOR' => 'donationpromptsms', 'VALUE' => 'text for link', 'SEARCH_IN' => 'url,referrer'],
        'VIRTUAL_TERMINAL' => ['SEARCH_FOR' => 'virtualterminal.', 'VALUE' => 'virtual terminal', 'SEARCH_IN' => 'url'],
        'CONTINUE_TO_GIVE' => ['SEARCH_FOR' => 'continuetogive.com', 'VALUE' => 'continue to give', 'SEARCH_IN' => 'url']
    ];
    const CALENDARS = [
        'DEFAULT_COLOR' => '#b2cd92'
    ];
    
    
    CONST NOTIFICATIONS = [
        'EMAIL' => [
            'STATUS' => ['QUEUED' => 'queued', 'SENT' => 'sent', 'ERROR' => 'error', 'UNSUBSCRIBED' => 'unsubscribed', 'RESUBSCRIBED' => 'resubscribed'],
            'MESSAGE' => ['QUEUED' => 'Email has been Queued', 'SENT' => 'Email has been Sent', 'ERROR' => 'Sent Email has Error', 'UNSUBSCRIBED' => 'Contact unsubscribed from list', 'UNSUBSCRIBED_ALL' => 'Contact unsubscribed from all emails', 'RESUBSCRIBED' => 'Contact resubscribed from list', 'RESUBSCRIBED_ALL' => 'Contact resubscribed from all emails'],
        ],
        'SMS' => [
            'STATUS' => ['QUEUED' => 'queued', 'SENT' => 'sent', 'ERROR' => 'error', 'UNSUBSCRIBED' => 'unsubscribed'],
            'MESSAGE' => ['QUEUED' => 'SMS has been Queued', 'SENT' => 'SMS has been Sent', 'ERROR' => 'Sent SMS has Error', 'UNSUBSCRIBED' => 'Contact unsubscribed from list'],
        ]
    ];
    
    
    const CHARTS = [
        'COLORS' => [
            'RGB' => [
                'RED' => 'rgb(255, 99, 132)',
                'ORANGE' => 'rgb(255, 159, 64)',
                'YELLOW' => 'rgb(255, 205, 86)',
                'GREEN' => 'rgb(75, 192, 192)',
                'BLUE' => 'rgb(54, 162, 235)',
                'PURPLE' => 'rgb(153, 102, 255)',
                'GREY' => 'rgb(231,233,237)'
            ],
            'RGBA' => [
                'RED' => 'rgba(255, 99, 132, 0.75)',
                'ORANGE' => 'rgba(255, 159, 64, 0.75)',
                'YELLOW' => 'rgba(255, 205, 86, 0.75)',
                'GREEN' => 'rgba(75, 192, 192, 0.75)',
                'BLUE' => 'rgba(54, 162, 235, 0.75)',
                'PURPLE' => 'rgba(153, 102, 255, 0.75)',
                'GREY' => 'rgba(231,233,237, 0.75)'
            ],
            'DEVICE_CATEGORY' => [
                'rgb(255, 99, 132)',
                'rgb(255, 159, 64)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(54, 162, 235)',
                'rgb(153, 102, 255)',
                'rgb(231,233,237)'
            ],
            'TRANSACTION_PATH' => [
                'rgb(255, 99, 132)',
                'rgb(255, 159, 64)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(54, 162, 235)',
                'rgb(153, 102, 255)',
                'rgb(231,233,237)',
                'rgb(131,133,137)',
                'rgb(15, 79, 17)',
            ]
        ]
    ];
    
    const TIME_PERIODS = [
        'Month' => 'Month',
        'Week' => 'Week',
        'Bi-Weekly' => 'Two Weeks',
        'One Time' => 'One Time'
    ];
    const MONTHS = [
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December'
    ];

    const SHORT_MONTH_NAMES = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec'
    ];

    /**
     * route name
     */
    const SUBSCRIPTION_REDIRECTS_TO = 'subscription.index';
    const SUBSCRIPTION_DAYS_IN_MONTH = 30;

    const SUBSCRIPTION_INVOICE_DETAILS = [
        'app_fee' => 'Module fee',
        'phone_number_fee' => 'Purchased phone number fee',
        'sms_fee' => 'Percent SMS fee',
        'email_fee' => 'Percent email fee',
        'contact_fee' => 'Per contact fee',
        'discount' => 'Discount',
    ];

    const VIEW_USES_MAIL_MERGE_CODES = [
        'print-mail.create',
        'print-mail.edit',
        'emails.create',
        'emails.edit',
        'communications.create',
        'communications.edit',
    ];

    const VIEW_USES_TRANSACTION_CODES = [
        'communications.create',
        'communications.edit',
        'contacts.compose',
    ];
    
    const REPORTS = [
        ['id' => 0, 'name' => 'New givers', 'category' => 'Financial', 'description' => 'This report displays new givers over a specific period of time.'],
        ['id' => 1, 'name' => 'Latent givers', 'category' => 'Financial', 'description' => 'This report lets you find givers who have NOT given during a specified date range, but DID give during the second date range specified.'],
        ['id' => 2, 'name' => 'Donor statistics', 'category' => 'Financial', 'description' => 'This report shows how much money the donor gave in the date range.'],
        ['id' => 3, 'name' => 'Purposes statistics', 'category' => 'Financial', 'description' => 'This report shows total transactions for each purpose by transaction size.'],
        ['id' => 4, 'name' => 'Fundraisers statistics', 'category' => 'Financial', 'description' => 'This report shows total transactions for each fundraiser by transaction size.'],
    ];

    const CONTACT_FORM_RELATIONSHIPS = [
        'PAYER' => 'Payer',
        'FORM_CONTACT' => 'FormContact',
        'PAYER_AND_FORM_CONTACT' => 'PayerAndFormContact',
    ];

    const ACCOUNT_TYPES = [
        'ASSET' => 'asset',
        'LIABILITY' => 'liability',
        'EQUITY' => 'equity',
        'INCOME' => 'income',
        'EXPENSES' => 'expenses'
    ];

    const MODULE_FREE_DAYS = 14;
    
    const SMS_CHAR_LIMIT = 318;
}
