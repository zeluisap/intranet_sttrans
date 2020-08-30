<?php
class TbFuncionarioOcorrenciaTipo extends Escola_Tabela {
	protected $_name = "funcionario_ocorrencia_tipo";
	protected $_rowClass = "FuncionarioOcorrenciaTipo";
	protected $_dependentTables = array("TbFuncionarioOcorrencia");
	
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

	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("F" => "FÃRIAS",
						   "L" => "LICENÃA");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}