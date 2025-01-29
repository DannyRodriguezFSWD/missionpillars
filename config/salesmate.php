<?php

return [
    'defaultOwnerId' => 4,
    
    'customFields' => [
        'contacts' => [
            'ctgid' => 'intCustomField1',
            'mpid' => 'intCustomField2'
        ],
        'companies' => [
            'ctgid' => 'intCustomField1',
            'mpid' => 'intCustomField2',
            'email' => 'textCustomField2',
            'processorType' => 'textCustomField7',
            'achStatus' => 'textCustomField4',
            'statusCrm' => 'textCustomField5',
            'statusAcct' => 'textCustomField6'
        ]
    ],
    
    'pipelines' => [
        'ctg' => [
            'name' => 'CTG Sales',
            'stages' => [
                'new' => 'New (Untouched)',
                'filledContactForm' => 'Filled In Contact Form',
                'contacted' => 'Contacted',
                'watchedDemo' => 'Watched Recorded Demo',
                '1on1' => '1 on 1 request',
                'commitment' => 'Commitment',
                'startedSignup' => 'Started Signup',
                'organizationCreated' => 'Created Organization',
                'startedApplicationCtg' => 'Started Application CTG',
                'startedApplicationIso' => 'Started Application ISO',
                'startedApplicationWepay' => 'Started Application WePay',
                'filledAppInfo' => 'Filled In App Info',
                'uploadedVoidedCheck' => 'Uploaded Voided Check',
                'uploadedSupportingDocs' => 'Uploaded Supporting Docs',
                'uploadedPhotoId' => 'Uploaded Photo ID',
                'failedAutoBoard' => 'Failed To Auto Board',
                'uwComplications' => 'Underwriting Complications',
                'submittedUw' => 'Submitted - UW',
                'filledBillingInfo' => 'ISO Filled In Billing Info',
                'activeMerchant' => 'Active Merchant'
            ]
        ],
        'missionary' => [
            'name' => 'Missionary Processes',
            'stages' => [
                'removed' => 'Missionary Removed',
                'qualified' => 'Qualified',
                'watchedDemo' => 'Watched Recorded Demo',
                '1on1' => '1 on 1 request',
                'startedSignup' => 'Started Signup',
                'requestMade' => 'Missionary Request Made',
                'denied' => 'Missionary Request Denied',
                'accepted' => 'Missionary Request Accepted'
            ]
        ],
        'wom' => [
            'name' => 'WOM',
            'stages' => [
                'raised500' => 'Raised 500',
                'saidSendContacts' => 'Said they\'d send over contacts',
                'receivedReferrals' => 'Received referrals',
                'receivedReferrals' => 'Received referrals'
            ]
        ],
        'mp' => [
            'name' => 'MP Sales',
            'titles' => [
                'crm' => 'MP-CRM',
                'acct' => 'MP-ACCT'
            ],
            'stages' => [
                'cold' => 'Cold',
                'contactMade' => 'Contact Made',
                'recordedDemo' => 'Recorded Demo',
                '1on1' => '1 on 1 request',
                'commitment' => 'Commitment',
                'startedSignup' => 'Started Signup',
                'organizationCreated' => 'Created Organization',
                'trialEnded' => 'Trial Ended',
                'activated' => 'Activated',
                'failedBilling' => 'Failed Billing'
            ]
        ]
    ],
    
    'sameWeightStages' => [
        [
            'mp.stages.activated',
            'mp.stages.failedBilling'
        ]
    ]
];

