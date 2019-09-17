<?php

namespace Saraiva\Framework\Ajax;

class Response {

    public static function response($isSuccess, $msg, array $data = array()) {
        $response = array(
            'IsSuccess' => $isSuccess,
            'msg' => $msg,
        );

        foreach ($data as $k => $v) {
            $response[$k] = $v;
        }

        return json_encode($response);
    }
    
    public static function responseException($log_id) {
        return static::response(FALSE, "Ocorreu um erro de sistema [$log_id]");
    }

}
