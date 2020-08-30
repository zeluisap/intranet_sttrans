<?php 
class TbAcao extends Zend_Db_Table_Abstract {
	protected $_name = "acao";
	protected $_rowClass = "Acao";
	protected $_dependentTables = array("TbPermissao");
	protected $_referenceMap = array("Modulo" => array("columns" => array("id_modulo"),
													   "refTableClass" => "TbModulo",
													   "refColumns" => array("id_modulo")));
													   
	public function getPorAction($modulo, $action) {
		$rg = $this->fetchAll(" id_modulo = {$modulo->id_modulo} and action = '{$action}' ");
		if (count($rg)) {
			return $rg->current();
		}
		return false;
	}
}