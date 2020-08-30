<?php

class Escola_Exception_List extends Escola_Exception {

    private $errors = [];

    public function __construct($errors, $code = null, $previous = null) {
        parent::__construct(implode(", ", $errors), $code, $previous);
        $this->setErrors($errors);
    }

    public function setErrors($errors) {
        $this->errors = $errors;
    }

    public function getErrors() {
        return $this->errors;
    }

}