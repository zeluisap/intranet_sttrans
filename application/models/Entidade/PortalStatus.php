<?php
class PortalStatus extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["chave"])) {
            $dados["chave"] = Escola_Util::maiuscula($dados["chave"]);
        }
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->chave)) {
			$msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
		}
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_portal_status <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "STATUS DO PORTAL JÁ CADASTRADO!";
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
        $sql->from(array("s" => "sistema"));
        $sql->where("s.id_portal_status = {$this->getId()}");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $msgs[] = "Status utilizado pelo Sistema!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;   
    }
    
    public function ativo() {
        return ($this->chave == "A");
    }
    
    public function inativo() {
        return ($this->chave == "I");
    }
    
    public function manutencao() {
        return ($this->chave == "M");
    }
}