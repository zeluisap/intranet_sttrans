<?php
class Banco extends Escola_Entidade {
	public function toString() {
		return $this->descricao;
	}

	public function setFromArray(array $dados) {
		if (isset($dados["sigla"])) {
			$dados["sigla"] = Escola_Util::maiuscula($dados["sigla"]);
		}
		if (isset($dados["descricao"])) {
			$dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->codigo)) {
			$msgs[] = "CAMPO CÓDIGO OBRIGATÓRIO!";
		}
        if (empty($this->sigla)) {
			$msgs[] = "CAMPO SIGLA OBRIGATÓRIO!";
		}
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("(codigo = '{$this->codigo}' or sigla = '{$this->sigla}' or descricao = '{$this->descricao}') and id_banco <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "BANCO JÁ CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
}