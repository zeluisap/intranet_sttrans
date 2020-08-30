<?php
class UsuarioSituacao extends Escola_Entidade {
	public function setFromArray(array $dados) {
		$filter = new Zend_Filter_StringToUpper();
		if (isset($dados["chave"])) {
			$dados["chave"] = $filter->filter($dados["chave"]);
		}
		if (isset($dados["descricao"])) {
			$dados["descricao"] = $filter->filter($dados["descricao"]);
		}
		parent::setFromArray($dados);
	}

	public function ativo() {
		return ($this->chave == "A");
	}
	
	public function __toString() {
		return $this->toString();
	}
	
	public function toString() {
		return $this->descricao;
	}	
}