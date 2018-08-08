<?php
namespace App;


class Response
{
    public static function warning($msg) {

        return json_encode('Warning: ' . $msg);

    }

    public static function error($msg) {

        return json_encode('Error: ' . $msg);

    }

    public static function json($str, $options = 0) {

        return json_encode($str, $options);

    }
}