var inputSets = [
        {
            label: 'Contact',
            name: 'contact', // optional
            showHeader: true, // optional
            fields: [
                {
                    type: 'text',
                    label: 'First Name',
                    name: 'first_name',
                    className: 'form-control',
                    required: true
                },
                {
                    type: 'text',
                    label: 'Last Name',
                    name: 'last_name',
                    className: 'form-control',
                    required: true
                },
                {
                    type: "text",
                    subtype: "email",
                    label: "Email",
                    className: "form-control",
                    required: true,
                    name: "email_1"
                },
                {
                    type: 'text',
                    subtype: 'tel',
                    label: 'Home Phone',
                    name: 'home_phone',
                    className: 'form-control',
                    required: false
                },
                {
                    type: 'text',
                    subtype: 'tel',
                    label: 'Cell Phone',
                    name: 'cell_phone',
                    className: 'form-control',
                    required: false
                },
                {
                    type: 'text',
                    subtype: 'tel',
                    label: 'Work Phone',
                    name: 'work_phone',
                    className: 'form-control',
                    required: false
                },
            ]
        },
        {
            label: 'Address',
            name: 'contact-address',
            fields: [
                {
                    type: 'text',
                    label: 'Address',
                    name: 'mailing_address_1',
                    className: 'form-control',
                    placeholder: 'e.g. 123 Example St.',
                    required: true
                },
                {
                    type: 'text',
                    label: 'City',
                    name: 'city',
                    className: 'form-control',
                    placeholder: 'City',
                    required: true
                },
                {
                    type: 'text',
                    label: 'Region',
                    name: 'region',
                    className: 'form-control',
                    placeholder: 'Region',
                    required: true
                },
                {
                    type: 'text',
                    label: 'Postal Code',
                    name: 'postal_code',
                    className: 'form-control',
                    placeholder: 'Postal Code',
                    required: false
                },
                {
                    type: 'select',
                    label: 'Country',
                    name: 'country',
                    className: 'form-control',
                    values: {!! $countries !!}
                }
            ]
        },
        {
            label: 'Profile Picture',
            name: 'contact-profile-image',
            fields: [{
                type: 'file',
                label: 'Profile Image',
                className: 'form-control',
                name: 'profile_image'
            }]
        },
        {
            label: 'Payment',
            name: 'payment', // optional
            //showHeader: true, // optional
            fields: [
                {
                    type: 'number',
                    label: 'Payment',
                    name: 'amount',
                    value: '0.00',
                    min: 0.00,
                    step: 0.1,
                    className: 'form-control',
                    required: true
                },
                {
                    type: 'text',
                    label: 'Credit/Debit Card Number',
                    name: 'card_number',
                    placeholder: '**** **** **** ****',
                    className: 'form-control',
                    required: true,
                    maxlength: 16
                },
                {
                    type: 'select',
                    label: 'Month',
                    className: 'form-control',
                    name: 'month',
                    values: [
                      {
                        label: '01',
                        value: '01',
                        selected: false
                      },
                      {
                        label: '02',
                        value: '02',
                        selected: false
                      },
                      {
                        label: '03',
                        value: '03',
                        selected: false
                      },
                      {
                        label: '04',
                        value: '04',
                        selected: false
                      },
                      {
                        label: '05',
                        value: '05',
                        selected: false
                      },
                      {
                        label: '06',
                        value: '06',
                        selected: false
                      },
                      {
                        label: '07',
                        value: '07',
                        selected: false
                      },
                      {
                        label: '08',
                        value: '08',
                        selected: false
                      },
                      {
                        label: '09',
                        value: '09',
                        selected: false
                      },
                      {
                        label: '10',
                        value: '10',
                        selected: false
                      },
                      {
                        label: '11',
                        value: '11',
                        selected: false
                      },
                      {
                        label: '12',
                        value: '12',
                        selected: false
                      }
                    ]
                },
                {
                    type: 'select',
                    label: 'Year',
                    className: 'form-control',
                    name: 'year',
                    values: {!! $years !!}
                },
                {
                    type: 'text',
                    label: 'CVC',
                    name: 'cvc',
                    placeholder: 'CVC',
                    className: 'form-control',
                    required: true
                },
                {
                    type: 'header',
                    label: 'Card Billing Address',
                    subtype: 'h3'
                },
                {
                    type: 'text',
                    label: 'Address',
                    name: 'mailing_address_1',
                    className: 'form-control',
                    placeholder: 'e.g. 123 Example St.',
                    required: true
                },
                {
                    type: 'text',
                    label: 'City',
                    name: 'city',
                    className: 'form-control',
                    placeholder: 'City',
                    required: true
                },
                {
                    type: 'text',
                    label: 'Region',
                    name: 'region',
                    className: 'form-control',
                    placeholder: 'Region',
                    required: true
                },
                {
                    type: 'select',
                    label: 'Country',
                    name: 'country',
                    className: 'form-control',
                    values: {!! $countries !!}
                }
            ]
        },
        {
            label: 'Payment dropdown',
            name: 'payment_dropdown', // optional
            fields: [
                {
                    type: 'select',
                    label: 'Payment dropdown',
                    name: 'payment[]',
                    className: 'form-control',
                    values: [
                        {
                            label: 'ONE ITEM (Add $5.00)',
                            value: '5',
                            selected: true
                        },
                        {
                            label: 'TWO ITEMS (Add $10.00)',
                            value: '10',
                            selected: false
                        },
                        {
                            label: 'THREE ITEMS (Add $15.00)',
                            value: '15',
                            selected: false
                        }
                    ]
                }
            ],
            icon: '$'
        },
        {
            label: 'Payment input box',
            name: 'payment_input_box', // optional
            fields: [
                {
                    type: 'text',
                    label: 'Payment input box',
                    name: 'payment[]',
                    className: 'form-control'
                }
            ],
            icon: '$'
        },
        {
            label: 'Yes / No Answer',
            name: 'yes_no', // optional
            //showHeader: true, // optional
            fields: [
                {
                    type: 'select',
                    label: 'Lorem ipsum dolor sit amet?',
                    name: 'yes_no_answer',
                    className: 'form-control',
                    values: [
                        {
                            label: '(Choose below)',
                            value: '0',
                            selected: true
                        },
                        {
                            label: 'YES',
                            value: 'yes',
                            selected: false
                        },
                        {
                            label: 'NO',
                            value: 'no',
                            selected: false
                        }
                    ]
                }
            ]
        },
        {
            label: 'Date Field',
            name: 'calendar',
            fields: [
                {
                    "type": "text",
                    "label": "Date Field",
                    "className": "form-control calendar",
                    "name": "calendar",
                    "subtype": "text"
                }
            ]
        },
        {
            label: 'Prefilled amount',
            name: 'prefilled_amount', // optional
            //showHeader: true, // optional
            fields: [
                {
                    type: 'number',
                    label: 'Amount $',
                    name: 'prefilled_amount',
                    value: '1',
                    min: 0.00,
                    step: 1,
                    className: 'form-control',
                    required: true
                }
            ]
        }];
    
        var defaultFields = {!! $json !!};
        var disableFields = [
            'button',
            'date',
            'payment',
            'autocomplete',
            'prefilled_amount'
        ];
        
        var disableFieldsNoPayments = [
            'button',
            'date',
            'payment',
            'autocomplete',
            'prefilled_amount',
            'payment_dropdown',
            'payment_input_box'
        ];
        
        var controlOrder = [
            'payment_dropdown',
            'payment_input_box',
            'contact',
            'contact-address',
            'contact-profile-image',
            'yes_no',
            'calendar',
            'text',
            'textarea',
            'button',
            'number',
            'select',
            'checkbox-group',
            'radio-group',
            'paragraph',
            'header'
        ];
        
        var controlOrderNoPayments = [
            'contact',
            'contact-address',
            'contact-profile-image',
            'yes_no',
            'calendar',
            'text',
            'textarea',
            'button',
            'number',
            'select',
            'checkbox-group',
            'radio-group',
            'paragraph',
            'header'
        ];
