<?php
class Bairro extends Escola_Entidade
{

	public function setFromArray(array $dados)
	{
		if (isset($dados["descricao"])) {
			$filter = new Zend_Filter_StringToUpper();
			$dados["descricao"] = $filter->filter(utf8_decode($dados["descricao"]));
		}
		parent::setFromArray($dados);
	}

	public function getErrors()
	{
		$msgs = array();
		if (empty($this->id_municipio)) {
			$msgs[] = "CAMPO MUNICÍPIO OBRIGATÓRIO!";
		}
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}' and id_municipio = '{$this->id_municipio}' and id_bairro <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "BAIRRO JÁ CADASTRADO PARA ESTE MUNICÍPIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}

	public function toString()
	{
		$txt = array();
		$txt[] = $this->descricao;
		/*
        $mun = $this->findParentRow("TbMunicipio");
        if ($mun) {
            $txt[] = $mun->descricao;
            $uf = $mun->findParentRow("TbUf");
            if ($uf) {
                $txt[] = $uf->sigla;
            }
        }
*/
		return implode(" - ", $txt);;
	}

	public function toArray()
	{
		$array = parent::toArray();
		$municipio = $this->findParentRow("TbMunicipio");
		if ($municipio) {
			$array["municipio"] = $municipio->toArray();
		}
		return $array;
	}
}
