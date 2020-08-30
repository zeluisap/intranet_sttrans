<?php
class InfoBancariaTipo extends Escola_Entidade {
	public function toString() {
		return $this->descricao;
	}

	public function setFromArray(array $dados) {
		if (isset($dados["chave"])) {
			$filter = new Zend_Filter_StringToUpper();
			$dados["chave"] = $filter->filter(utf8_decode($dados["chave"]));
		}
		if (isset($dados["descricao"])) {
			$filter = new Zend_Filter_StringToUpper();
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
		$rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}' and id_info_bancaria_tipo <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "TIPO DE INFORMAÇÃO BANCÁRIA JÁ EXISTE!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
}