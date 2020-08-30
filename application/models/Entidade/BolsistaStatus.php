<?php
class BolsistaStatus extends Escola_Entidade {
    
	public function toString() {
		return $this->descricao;
	}

	public function setFromArray(array $dados) {
        $filter = new Zend_Filter_StringToUpper();
		if (isset($dados["chave"])) {
			$dados["chave"] = $filter->filter(utf8_decode($dados["chave"]));
		}
		if (isset($dados["descricao"])) {
			$dados["descricao"] = $filter->filter(utf8_decode($dados["descricao"]));
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->chave)) {
			$msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
		}
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}' and id_bolsista_status <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "STATUS DO BOLSISTA JÁ CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function ativo() {
        return ($this->chave == "A");
    }
}