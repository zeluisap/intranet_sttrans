<?php
class TbCargo extends Escola_Tabela {
	protected $_name = "cargo";
	protected $_rowClass = "Cargo";
	protected $_dependentTables = array("TbFuncionario");
	protected $_referenceMap = array("CargoTipo" => array("columns" => array("id_cargo_tipo"),
															 "refTableClass" => "TbCargoTipo",
															 "refColumns" => array("id_cargo_tipo")));
		
	public function listar($dados = array()) {
		$select = $this->select();
		$select->order("descricao");
		return $this->fetchAll($select);
	}
			
	public function listarporpagina($dados = array()) {
		$select = $this->select();
		$select->order("descricao");
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage(50);
		return $paginator;
	}

	public function getPorDescricao($descricao) {
		$rg = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($rg->count()) {
			return $rg->current();
		}
		return false;
	}
}