<?php
class TbPermissao extends Zend_Db_Table_Abstract {
	protected $_name = "permissao";
	protected $_rowClass = "Permissao";
	protected $_referenceMap = array("Acao" => array("columns" => array("id_acao"),
													 "refTableClass" => "TbAcao",
													 "refColumns" => array("id_acao")),
									 "Grupo" => array("columns" => array("id_grupo"),
													 "refTableClass" => "TbGrupo",
													 "refColumns" => array("id_grupo")));
	public function listar($dados) {
		$select = $this->select();
		if (isset($dados["id_acao"]) && $dados["id_acao"]) {
			$select->where("id_acao = " . $dados["id_acao"]);
		}
		if (isset($dados["id_grupo"]) && $dados["id_grupo"]) {
			$select->where("id_grupo = " . $dados["id_grupo"]);
		}
		$rg = $this->fetchAll($select);
		if (count($rg)) {
			return $rg;
		}
		return false;
	}
}