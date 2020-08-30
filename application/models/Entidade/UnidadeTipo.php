<?php
class UnidadeTipo extends Escola_Entidade {
	public function setFromArray(array $dados) {
		$maiuscula = new Zend_Filter_StringToUpper();
		if (isset($dados["chave"]) && $dados["chave"]) {
			$dados["chave"] = $maiuscula->filter($dados["chave"]);
		}
		if (isset($dados["descricao"]) && $dados["descricao"]) {
			$dados["descricao"] = $maiuscula->filter($dados["descricao"]);
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
		$tb = new TbUnidadeTipo();
		$rg = $tb->fetchAll(" chave = '{$this->chave}' and id_unidade_tipo <> '{$this->id_unidade_tipo}' ");
		if ($rg->count()) {
			$msgs[] = "TIPO DE UNIDADE JÁ CADASTRADA!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}	
}