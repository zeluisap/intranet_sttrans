<?php
class TbSistema extends Zend_Db_Table_Abstract {
	protected $_name = "sistema";
	protected $_rowClass = "Sistema";
	protected $_referenceMap = array("PessoaJuridica" => array("columns" => array("id_pessoa_juridica"),
															   "refTableClass" => "TbPessoaJuridica",
															   "refColumns" => array("id_pessoa_juridica")),
									"PortalStatus" => array("columns" => array("id_portal_status"),
															   "refTableClass" => "TbPortalStatus",
															   "refColumns" => array("id_portal_status")),
                                    "PortalLayout" => array("columns" => array("id_portal_layout"),
															   "refTableClass" => "TbPortalLayout",
															   "refColumns" => array("id_portal_layout")));
															   
	public function pegaSistema() {
		$registros = $this->fetchAll();
		if ($registros->count()) {
			return $registros->current();
		}		
		return false;
	}
}