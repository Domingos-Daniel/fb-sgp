<?php

return [ 
    'show_custom_fields' => true,
        'custom_fields' => [
            'email_secundary' => [
                'type' => 'text',
                'label' => 'Email Secundario',
                'placeholder' => 'Email Secundario',
                'required' => true,
                'rules' => 'required|string|max:255|email',
            ],
        ]
];
