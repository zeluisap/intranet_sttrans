<?php
class EstadoCivil extends Escola_Entidade {
	public function __toString() {
		return $this->toString();
	}
	
	public function toString() {
		return $this->descricao;
	}
	
	public function getErrors() {
		$errors = array();
		if (!trim($this->descricao)) {
			$errors[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}' and id_estado_civil <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$errors[] = "ESTADO CIVIL JÁ CADASTRADO!";
		}
		if (count($errors)) {
			return $errors;
		}
		return false;
	}
	
	public function setFromArray(array $dados) {
		$maiuscula = new Zend_Filter_StringToUpper();
		if (isset($dados["descricao"])) {
			$dados["descricao"] = $maiuscula->filter(utf8_decode($dados["descricao"]));
		}
		parent::setFromArray($dados);
	}
}