<?php

class Escola_Validate_Cpf extends Zend_Validate_Abstract {

    function isValid($value) {
        $filter = new Zend_Filter_Digits();
        $cpf = $filter->filter($value);
        if (!preg_match('/^[0-9]{11}$/i', $cpf)) {
            return false;
        }
        if ($cpf == "00000000000" || $cpf == "11111111111" || $cpf == "22222222222" ||
                $cpf == "33333333333" || $cpf == "44444444444" || $cpf == "55555555555" ||
                $cpf == "66666666666" || $cpf == "77777777777" || $cpf == "88888888888" ||
                $cpf == "99999999999")
            return false;
        $soma = 0;
        for ($i = 0; $i < 9; $i ++)
            $soma += (int) (substr($cpf, $i, 1)) * (10 - $i);
        $resto = 11 - ($soma % 11);
        if ($resto == 10 || $resto == 11)
            $resto = 0;
        if ($resto != (int) (substr($cpf, 9, 1)))
            return false;
        $soma = 0;
        for ($i = 0; $i < 10; $i ++)
            $soma += (int) (substr($cpf, $i, 1)) * (11 - $i);
        $resto = 11 - ($soma % 11);
        if ($resto == 10 || $resto == 11)
            $resto = 0;
        if ($resto != (int) (substr($cpf, 10, 1)))
            return false;

        return true;
    }

}