<?php
class Infracao extends Escola_Entidade {
        
    protected $_valor = false;
    
    public function pega_valor() {
        if ($this->_valor) {
            return $this->_valor;
        }
        $valor = $this->findParentRow("TbValor");
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }
    
    public function init() {
        parent::init();
        $this->_valor = $this->pega_valor();
        if (!$this->getId()) {
            $this->pontuacao = 0;
        }
    }
    
    public function setFromArray(array $data) {
        if (isset($data["codigo"])) {
            $data["codigo"] = Escola_Util::maiuscula($data["codigo"]);
        }
        if (isset($data["descricao"])) {
            $data["descricao"] = Escola_Util::maiuscula($data["descricao"]);
        }
        $this->_valor->setFromArray($data);
        parent::setFromArray($data);
    }
    
    public function save($flag = false) {
        $this->id_valor = $this->_valor->save();
        parent::save($flag);
    }
    
	public function toString() {
        $txt = array();
        $txt[] = $this->codigo;
        $txt[] = $this->descricao;
        $txt[] = $this->_valor->toString();
		return implode(" - ", $txt);
	}
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->codigo)) {
			$msgs[] = "CAMPO CÓDIGO OBRIGATÓRIO!";
		}
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" id_amparo_legal = {$this->id_amparo_legal} and descricao = '{$this->descricao}' and id_infracao <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "INFRAÇÃO JÁ CADASTRADA!";
        }
        if ($this->codigo) { 
            $rg = $this->getTable()->fetchAll(" codigo = '{$this->codigo}' and id_infracao <> '" . $this->getId() . "' ");
            if ($rg && count($rg)) {
                $msgs[] = "JÁ EXISTE UMA INFRAÇÃO COM O CÓDIGO [{$this->codigo}]!";
            }
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("notificacao_infracao"));
        $sql->where("id_infracao = {$this->getId()}");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $msgs[] = "Existem Registros vinculados a esta Infração!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
}