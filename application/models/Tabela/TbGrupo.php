<?php
class TbGrupo extends Escola_Tabela {
	protected $_name = "grupo";
	protected $_rowClass = "Grupo";
	protected $_dependentTables = array("TbPermissao");
	protected $_referenceMap = array("Grupo" => array("columns" => array("id_grupo_inferior"),
													  "refTableClass" => "TbGrupo",
													  "refColumns" => array("id_grupo")));
	
	public function init() {
		$grupos = $this->fetchAll();
		if (!$grupos->count()) {
			$array = array(array("descricao" => "administrador"),
						   array("descricao" => "usuario", "padrao" => "S"));
			foreach ($array as $arr) {
				$grupo = $this->createRow();
				$grupo->setFromArray($arr);
				$grupo->save();
			}
		}
	}
	
	public function getPadrao() {
		return $this->fetchRow(" padrao = 'S' ");
	}
	
	public function getPorDescricao($descricao) {
		$rg = $this->fetchAll("descricao = '{$descricao}'");
		if ($rg) {
			return $rg->current();
		}
		return false;
	}
	
	public function pegaAdministrador() {
		return $this->getPorDescricao("ADMINISTRADOR");
	}
	
	public function listar($dados) {
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
}