<?php
class TbVinculoLoteItemStatus extends Escola_Tabela
{
	protected $_name = "vinculo_lote_item_status";
	protected $_rowClass = "VinculoLoteItemStatus";
	protected $_dependentTables = array("TbVinculoLoteItem");

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
				"PP" => "PAGAMENTO PENDENTE",
				"PG" => "PAGAMENTO CONFIRMADO",
				"FL" => "FALHA NO PAGAMENTO",
				"IN" => "INAPTO"
			);
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}
