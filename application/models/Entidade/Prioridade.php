<?php
class Prioridade extends Escola_Entidade {
	    
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
		$filter = new Zend_Validate_Digits();
		if (!$filter->isValid($this->tolerancia)) {
			$msgs[] = "CAMPO TOLERÂNCIA INVÁLIDO, SOMENTE NÚMEROS!";
		}
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_prioridade <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "PRIORIDADE JÁ CADASTRADA!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function save() {
    	if (!$this->tolerancia) {
    		$this->tolerancia = 0;
    	}
    	return parent::save();
    }
    
    public function normal() {
    	return ($this->chave == "N");
    }
}