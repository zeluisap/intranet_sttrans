<?php
class Permissao extends Zend_Db_Table_Row_Abstract {
	public function getErrors() {
		$db = Zend_Registry::get("db");
		$msg = array();
		$select = $this->select();
		$select->where("id_acao = " . $this->id_acao);
		$select->where("id_grupo = " . $this->id_grupo);
		$rg = $db->fetchAll($select);
		if (count($rg)) {
			$msg[] = "PERMISSÃO JÁ EFETUADA!";
		}
		if (count($msg)) {
			return $msg;
		}
		return false;
	}
}