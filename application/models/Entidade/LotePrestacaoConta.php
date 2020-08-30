<?php
class LotePrestacaoConta extends Escola_Entidade {

    protected $_arquivo;
    
    public function init() {
        parent::init();
        $this->_arquivo = $this->get_arquivo();
        if (!$this->pc_data) {
            $this->pc_data = date("Y-m-d");
        }
        if (!$this->pc_hora) {
            $this->pc_hora = date("H:i:s");
        }
    }
    
    public function get_arquivo() {
        if ($this->_arquivo) {
            return $this->_arquivo;
        }
        $tb = new TbArquivo();
        if ($this->id_arquivo) {
            $arquivo = $tb->getPorId($this->id_arquivo);
            if ($arquivo) {
                return $arquivo;
            }
        }
        return $tb->createRow();
    }
    
    public function setFromArray($dados = array()) {
        parent::setFromArray($dados);
        $this->_arquivo->setFromArray($dados);
    }    
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_vinculo_lote)) {
            $msgs[] = "CAMPO LOTE OBRIGATÓRIO!";
        }
        if (!trim($this->id_previsao_tipo)) {
            $msgs[] = "CAMPO DESPESA OBRIGATÓRIO!";
        }
        if (!trim($this->id_bolsa_tipo)) {
            $msgs[] = "CAMPO TIPO DE ÍTEM DE DESPESA OBRIGATÓRIO!";
        }
        if (!trim($this->descricao)) {
            $msgs[] = "CAMPO DESCRIÇÃO DO DOCUMENTO OBRIGATÓRIO!";
        }
        if (!trim($this->_arquivo->existe())) {
            $msgs[] = "DOCUMENTO DE COMPROVAÇÃO INVÁLIDO OU NÃO LOCALIZADO!";
        }        
        if (!count($msgs)) {
            $tb = new TbLotePrestacaoConta();
            $sql = $tb->select();
            $sql->where("id_vinculo_lote = {$this->id_vinculo_lote}");
            $sql->where("id_previsao_tipo = {$this->id_previsao_tipo}");
            $sql->where("id_bolsa_tipo = {$this->id_bolsa_tipo}");
            $sql->where("id_lote_prestacao_conta <> {$this->getId()}");
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    
    public function save($flag = false) {
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            
            $errors = $this->getErrors();
            if ($errors) {
                throw new Exception(implode("<br>", $errors));
            }
            
            $lote = $this->findParentRow("TbVinculoLote");
            if (!$lote) {
                throw new Exception("Falha ao Executar Operação, Tipo de Despesa Não Disponível!");
            }
            
            $dt = $this->findParentRow("TbPrevisaoTipo");
            if (!$dt) {
                throw new Exception("Falha ao Executar Operação, Tipo de Despesa Não Disponível!");
            }
            
            $bt = $this->findParentRow("TbBolsaTipo");
            if (!$bt) {
                throw new Exception("Falha ao Executar Operação, Tipo de Ítem de Lote Não Disponível!");
            }
            
            $this->_arquivo->save();
            if ($this->_arquivo->getId()) {
                $this->id_arquivo = $this->_arquivo->getId();
            }
            
            $return_id = parent::save($flag);
            
            if ($trans) {
                $db->commit();
            }
            
            return $return_id;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            
            throw $ex;
        }
    }
    
    public function delete() {
        $db = Zend_Registry::get("db");
        $trans = true;
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            $arquivo = $this->findParentRow("TbArquivo");
            
            $return_id = parent::delete();
            
            if ($arquivo) {
                $arquivo->delete();
            }            
            
            if ($trans) {
                $db->commit();
            }
            
            return $return_id;
            
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
}