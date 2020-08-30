<?php
class TbPais extends Escola_Tabela {
	protected $_name = "pais";
	protected $_rowClass = "Pais";
	protected $_dependentTables = array("TbUf");
	
	public function listar() {
		$select = $this->select();
		$select->order("descricao");
		$result = $this->fetchAll($select);
		if ($result->count()) {
			return $result;
		}
		return false;
	}
	
	public function getPorDescricao($descricao) {
		if ($descricao) {
			$dados = $this->fetchAll("descricao = '$descricao'");
			if ($dados->count()) {
				return $dados->current();
			}
		}
		return false;
	}
	
	public function recuperar() {
		$paises = $this->listar();
		if (!$paises) {
			$pais = $this->createRow();
			$pais->descricao = "BRASIL";
			$pais->save();
		}
	}
}