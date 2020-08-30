<?php
class TbCargoTipo extends Escola_Tabela {
	protected $_name = "cargo_tipo";
	protected $_rowClass = "CargoTipo";
	protected $_dependentTables = array("TbCargo");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("descricao");
        return $sql;
    }
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("T" => "TÃCNICO",
						   "D" => "DOCENTE",
						   "E" => "ESTAGIÃRIO");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}