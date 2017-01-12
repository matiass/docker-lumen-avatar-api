<?php
return [
    'rules' => [
        "v404" => [
            'code' => 400000,
            'link' => "http://some.url/docs",
            'message' => "avatar not found"
        ],
        'email.required' => [
            'code' => 400001,
            'link' => "http://some.url/docs",
            'message' =>  "missing email address"
        ],
        'unknown' => [
            'code' => 0,
            'link' => "http://some.url/docs",
            'message' => "Unknown error"
        ]
    ]
];