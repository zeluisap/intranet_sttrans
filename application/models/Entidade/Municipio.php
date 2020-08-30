<?php
class Municipio extends Escola_Entidade
{

	public function setFromArray(array $dados)
	{
		if (isset($dados["descricao"])) {
			$dados["descricao"] = Escola_Util::maiuscula(utf8_decode($dados["descricao"]));
		}
		parent::setFromArray($dados);
	}

	public function getErrors()
	{
		$msgs = array();
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$tb = new TbMunicipio();
		$rg = $tb->fetchAll(" descricao = '{$this->descricao}' and id_uf = {$this->id_uf} and id_municipio <> '" . $this->getId() . "' ");
		if ($rg->count()) {
			$msgs[] = "EXISTE OUTRO MUNICÍPIO CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}

	public function toString()
	{
		return $this->descricao;
	}

	public function toArray()
	{
		$array = parent::toArray();
		$uf = $this->findParentRow("TbUf");
		if ($uf) {
			$array["uf"] = $uf->toArray();
		}
		return $array;
	}
}
