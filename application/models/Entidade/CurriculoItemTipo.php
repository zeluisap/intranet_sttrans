<?php
class CurriculoItemTipo extends Escola_Entidade {
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
		$tb = new TbCurriculoItemTipo();
		$rg = $tb->fetchAll(" chave = '{$this->chave}' and id_curriculo_item_tipo <> '{$this->id_curriculo_item_tipo}' ");
		if ($rg->count()) {
			$msgs[] = "TIPO DE ÍTEM DE CURRÍCULO JÁ CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}	
}