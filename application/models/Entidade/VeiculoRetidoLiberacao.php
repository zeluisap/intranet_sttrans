<?php
class VeiculoRetidoLiberacao extends Escola_Entidade {
    
    public function pegaVeiculoRetido() {
        $obj = $this->findParentRow("TbVeiculoRetido");
        if ($obj && $obj->getId())  {
            return $obj;
        }
        return false;
    }
    
    public function pegaFuncionario() {
        $obj = $this->findParentRow("TbFuncionario");
        if ($obj && $obj->getId())  {
            return $obj;
        }
        return false;
    }
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->data_liberacao = date("Y-m-d");
            $this->hora_liberacao = date("H:i:s");
        }
    }
    
    public function getErrors() {
        $msgs = array();
        if (!$this->id_veiculo_retido) {
            $msgs[] = "CAMPO VEÍCULO RETIDO OBRIGATÓRIO!";
        }                
        if (!$this->id_funcionario) {
            $msgs[] = "CAMPO FUNCIONÁRIO OBRIGATÓRIO!";
        }                
        if (!Escola_Util::limpaNumero($this->data_liberacao)) {
            $msgs[] = "CAMPO DATA DE LIBERAÇÃO OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaData($this->data_liberacao)) {
            $msgs[] = "CAMPO DATA DE LIBERAÇÃO INVÁLIDO!";
        }
        $vr = $this->pegaVeiculoRetido();
        if ($vr) {
            if ($vr->liberado()) {
                $msgs[] = "VEÍCULO RETIDO JÁ LIBERADO!";
            }
            $vrl = $vr->pegaVeiculoRetidoLiberacao();
            if ($vrl) {
                $msgs[] = "JÁ EXISTE UMA LIBERAÇÃO PARA O VEÍCULO!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }     
}