<?php
class TbVinculoTipo extends Escola_Tabela
{
	protected $_name = "vinculo_tipo";
	protected $_rowClass = "VinculoTipo";
	protected $_dependentTables = array("TbVinculo");

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
				"CO" => "CONVÊNIO",
				"CT" => "CONTRATO"
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
}
