<?php 
class Uf extends Escola_Entidade {
	public function __toString() {
		return $this->descricao;
	}
	
	public function toString() {
		return $this->__toString();
	}
	
	public function setFromArray(array $dados) {
		$filter = new Zend_Filter_StringToUpper();
		if (isset($dados["sigla"])) {
			$dados["sigla"] = $filter->filter($dados["sigla"]);
		}
		if (isset($dados["descricao"])) {
			$dados["descricao"] = $filter->filter($dados["descricao"]);
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		if (empty($this->id_pais)) {
			$msgs[] = "CAMPO PAÍS OBRIGATÓRIO!";
		}
		$tb = new TbUf();
		$rg = $tb->fetchAll(" descricao = '{$this->descricao}' and id_pais = {$this->id_pais} and id_uf <> '{$this->id_uf}' ");
		if ($rg->count()) {
			$msgs[] = "UNIDADE FEDERATIVA JÁ CADASTRADA!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
}