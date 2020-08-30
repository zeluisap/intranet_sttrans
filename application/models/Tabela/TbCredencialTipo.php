<?php
class TbCredencialTipo extends Escola_Tabela
{
	protected $_name = "credencial_tipo";
	protected $_rowClass = "CredencialTipo";
	protected $_dependentTables = array("TbCredencial");

	public function getSql($dados = array())
	{
		$sql = $this->select();
		$sql->order("descricao");
		return $sql;
	}

	public function getPorChave($chave)
	{
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}

	public function getPorDescricao($descricao)
	{
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}

	public function recuperar()
	{
		$items = $this->listar();
		if (!$items) {
			$dados = array(
				"I" => "IDOSO",
				"D" => "DEFICIENTE"
			);
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}
