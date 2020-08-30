<?php
class VeiculoRetido extends Escola_Entidade {
    
    public function pegaVeiculo() {
        $obj = $this->findParentRow("TbVeiculo");
        if ($obj && $obj->getId())  {
            return $obj;
        }
        return false;
    }
    
    public function pegaAutoInfracaoNotificacao() {
        $obj = $this->findParentRow("TbAutoInfracaoNotificacao");
        if ($obj && $obj->getId())  {
            return $obj;
        }
        return false;
    }
    
    public function pegaVeiculoRetidoStatus() {
        $obj = $this->findParentRow("TbVeiculoRetidoStatus");
        if ($obj && $obj->getId())  {
            return $obj;
        }
        return false;
    }
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->data_veiculo_retido = date("Y-m-d");
            $this->hora_veiculo_retido = date("H:i:s");
            $tb = new TbVeiculoRetidoStatus();
            $vrs = $tb->getPorChave("AL");
            if ($vrs) {
                $this->id_veiculo_retido_status = $vrs->getId();
            }
        }
    }
    
    public function getErrors() {
        $msgs = array();
        if (!Escola_Util::limpaNumero($this->data_veiculo_retido)) {
            $msgs[] = "CAMPO DATA DE LANÇAMENTO OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaData($this->data_veiculo_retido)) {
            $msgs[] = "CAMPO DATA DE LANÇAMENTO INVÁLIDO!";
        }
        if (!$this->id_veiculo) {
            $msgs[] = "CAMPO VEÍCULO OBRIGATÓRIO!";
        }                
        if (!$this->id_auto_infracao_notificacao) {
            $msgs[] = "CAMPO NOTIFICAÇÃO OBRIGATÓRIO!";
        }                
        if (!$this->id_veiculo_retido_status) {
            $msgs[] = "CAMPO STATUS OBRIGATÓRIO!";
        }
        $veiculo = $this->findParentRow("TbVeiculo");
        if ($veiculo && $veiculo->retido()) {
            $msgs[] = "VEICULO JA SE ENCONTRA RETIDO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }     
    
    public function aguardando_liberacao() {
        $rjs = $this->pegaVeiculoRetidoStatus();
        if ($rjs) {
            return $rjs->aguardando_liberacao();
        }
        return false;
    }
    
    public function pegaVeiculoRetidoLiberacao() {
        $vrls = $this->findDependentRowSet("TbVeiculoRetidoLiberacao");
        if ($vrls && count($vrls)) {
            $vrl = $vrls->current();
            return $vrl;
        }
        return false;
    }
    
    public function liberado() {
        $vrs = $this->pegaVeiculoRetidoStatus();
        if ($vrs) {
            return $vrs->liberado();
        }
        return false;
    }
    
    public function liberar($dados = array()) {
        $in_transaction = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_transaction = false;
        }
        try {
            $funcionario = false;
            if (isset($dados["id_funcionario"]) && $dados["id_funcionario"]) {
                $funcionario = TbFuncionario::pegaPorId($dados["id_funcionario"]);
            }
            if (!$funcionario) {
                $tb = new TbFuncionario();
                $funcionario = $tb->pegaLogado();
            }
            if ($funcionario) {
                $dados["id_funcionario"] = $funcionario->getId();
            } else {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
            $ain = $this->pegaAutoInfracaoNotificacao();
            if ($ain && $ain->pendente()) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, NOTIFICAÇÃO COM PENDÊNCIA DE PAGAMENTO!");
            }
            $dados["id_veiculo_retido"] = $this->getId();
            $tb = new TbVeiculoRetidoLiberacao();
            $vrl = $tb->createRow();
            $vrl->setFromArray($dados);
            $errors = $vrl->getErrors();
            if ($errors) {
                throw new Exception(implode("<br>", $errors));
            }
            $vrl_id = $vrl->save();
            if (!$vrl_id) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!!");
            }
            $tb = new TbVeiculoRetidoStatus();
            $vrs = $tb->getPorChave("LI");
            if (!$vrs) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!!!");
            }
            $this->id_veiculo_retido_status = $vrs->getId();
            $this->save();
            if ($in_transaction) {
                $db->commit();
            }
            return true;
        } catch (Exception $ex) {
            if ($in_transaction) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
    
    public function cancelar_liberacao($dados = array()) {
        $in_transaction = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_transaction = false;
        }
        try {
            $vrl = $this->pegaVeiculoRetidoLiberacao();
            if (!$vrl) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
            if (!$this->liberado()) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, VEÍCULO NÃO LIBERADO!");
            }
            $vrl->delete();
            $tb = new TbVeiculoRetidoStatus();
            $vrs = $tb->getPorChave("AL");
            if (!$vrs) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!!");
            }
            $this->id_veiculo_retido_status = $vrs->getId();
            $this->save();
            if ($in_transaction) {
                $db->commit();
            }
            return true;
        } catch (Exception $ex) {
            if ($in_transaction) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
}