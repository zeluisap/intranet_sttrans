<?php
class TbFuncionarioFuncao extends Escola_Tabela {
	protected $_name = "funcionario_funcao";
	protected $_rowClass = "FuncionarioFuncao";
	protected $_dependentTables = array("TbLotacao");
	protected $_referenceMap = array("FuncionarioFuncaoTipo" => array("columns" => array("id_funcionario_funcao_tipo"),
															 "refTableClass" => "TbFuncionarioFuncaoTipo",
															 "refColumns" => array("id_funcionario_funcao_tipo")));

	public function listarPagina($dados) {
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