<?php
class TbSetorNivel extends Escola_Tabela {
	protected $_name = "setor_nivel";
	protected $_rowClass = "SetorNivel";
	protected $_dependentTables = array("TbSetor");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function listar() {
		$sql = $this->select();
		$sql->order("descricao");
		$rg = $this->fetchAll($sql);
		if (count($rg)) {
			return $rg;
		}
		return false;
	}
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("G" => "GERÃNCIA",
						   "D" => "DEPARTAMENTO",
						   "I" => "DIVISÃO",
						   "S" => "SETOR",
						   "C" => "SEÃÃO");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}