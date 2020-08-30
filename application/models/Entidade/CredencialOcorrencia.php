<?php
class CredencialOcorrencia extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->ocorrencia_data = date("Y-m-d");
            $this->ocorrencia_hora = date("H:i:s");
        }
    }
    
    public function getErrors() {
        $msgs = array();
        if (empty($this->id_credencial_ocorrencia_tipo)) {
            $msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
        }
        if (empty($this->id_credencial)) {
            $msgs[] = "CAMPO CREDENCIAL OBRIGATÓRIO!";
        }
        if (empty($this->id_usuario)) {
            $msgs[] = "CAMPO USUÁRIO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
}