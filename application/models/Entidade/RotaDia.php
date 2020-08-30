<?php
class RotaDia extends Escola_Entidade {
	
    public function getErrors() {
        $msgs = array();
        if (!$this->id_rota) {
                $msgs[] = "CAMPO ROTA OCORRENCIA OBRIGATÓRIO!";
        }
        if (!$this->id_dia_tipo) {
                $msgs[] = "CAMPO TIPO DE DIA OBRIGATÓRIO!";
        }
        if (!$this->veiculos) {
                $msgs[] = "CAMPO VEICULOS OBRIGATÓRIO!";
        }
        if (!$this->viagens) {
                $msgs[] = "CAMPO VIAGENS OBRIGATÓRIO!";
        }
        if (count($msgs)) {
                return $msgs;
        }
        return false;
    }
    
    
}