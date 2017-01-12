<?php
if (!function_exists('getExtension')) {

    /*
     *
     */
    function getExtension($mimeType)
    {
        $ext = [
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            "image/gif" => 'gif',
            "image/bmp" => 'bmp'
        ];

        return $ext[$mimeType];

    }
}