<?php
class TbInfoBancariaTipo extends Escola_Tabela
{
	protected $_name = "info_bancaria_tipo";
	protected $_rowClass = "InfoBancariaTipo";
	protected $_dependentTables = array("TbInfoBancaria");

	public function listar($dados = array())
	{
		$select = $this->select();
		$select->order("descricao");
		$rgs = $this->fetchAll($select);
		if ($rgs->count()) {
			return $rgs;
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
				"C" => "CONTA CORRENTE",
				"P" => "POUPANÇA"
			);
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}
