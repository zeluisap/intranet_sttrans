<?php
class TbVinculoStatus extends Escola_Tabela {
	protected $_name = "vinculo_status";
	protected $_rowClass = "VinculoStatus";
	protected $_dependentTables = array("TbVinculo");
									 
	public function listar($dados = array()) {
		$select = $this->select();
		$select->order("descricao"); 
		$rgs = $this->fetchAll($select);
		if ($rgs->count()) {
			return $rgs;
		}
		return false;
	}
    
    public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}

	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("A" => "ATIVO",
						   "F" => "FINALIZADO");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}