<?php

class BaseException extends  Exception {
    public function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
}

class DBException extends BaseException {
    private $dbErrorInfo;
    private $dbErrorCode; 
    
    public function setDbErrorInfo($dbErrorInfo) {
        $this->dbErrorInfo = $dbErrorInfo;
    }
    public function getDbErrorInfo() {
        return $this->dbErrorInfo;
    }
    
    public function setDbErrorCode($dbErrorCode) {
        $this->dbErrorCode = $dbErrorCode;
    }
    public function getDbErrorCode() {
        return $this->dbErrorCode;
    }
}

class CacheImplementsNotFound extends BaseException {}

