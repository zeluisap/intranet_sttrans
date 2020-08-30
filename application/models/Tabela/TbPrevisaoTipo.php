<?php
class TbPrevisaoTipo extends Escola_Tabela
{
	protected $_name = "previsao_tipo";
	protected $_rowClass = "PrevisaoTipo";
	protected $_dependentTables = array("TbPrevisao", "TbBolsaTipo");

	public function getSql($dados = array())
	{
		$sql = $this->select();
		$sql->order("descricao");
		return $sql;
	}

	public function recuperar()
	{
		$items = $this->listar();
		if (!$items) {
			$dados = array(
				"BO" => "BOLSISTA",
				"PF" => "PESSOA FÍSICA",
				"PJ" => "PESSOA JURÍDICA"
			);
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}

	public function getPorDescricao($descricao)
	{
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}

	public function getPorChave($chave)
	{
		$pts = $this->fetchAll(" chave = '{$chave}' ");
		if ($pts && count($pts)) {
			return $pts->current();
		}
		return false;
	}
}
