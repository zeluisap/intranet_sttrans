<?php
class TbPrioridade extends Escola_Tabela {
	protected $_name = "prioridade";
	protected $_rowClass = "Prioridade";
	protected $_dependentTables = array("TbDocumento");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array(array("chave" => "N", "descricao" => "NORMAL", "tolerancia" => 5));
			foreach ($dados as $linha) {
				$item = $this->createRow();
				$item->setFromArray($linha);
				$item->save();
			}
		}
	}
}