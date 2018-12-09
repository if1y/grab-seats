<?php

class BaseException extends  Exception {
    public function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
}

class DBException extends BaseException {}

class CacheImplementsNotFound extends BaseException {}

