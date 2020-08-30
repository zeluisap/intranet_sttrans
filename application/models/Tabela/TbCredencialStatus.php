<?php
class TbCredencialStatus extends Escola_Tabela
{
	protected $_name = "credencial_status";
	protected $_rowClass = "CredencialStatus";
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
				"P" => "PENDENTE",
				"D" => "DEFERIDO",
				"I" => "INDEFERIDO"
			);
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}
