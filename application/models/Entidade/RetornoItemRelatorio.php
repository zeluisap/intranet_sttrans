<?php

class RetornoItemRelatorio extends Escola_Entidade {

    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_retorno_item)) {
            $msgs[] = "CAMPO RETORNO OBRIGATÓRIO!";
        }
        if (!trim($this->id_boleto_item)) {
            $msgs[] = "CAMPO ÍTEM DO BOLETO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function confirmado() {
        return ($this->confirmado == "S");
    }
}