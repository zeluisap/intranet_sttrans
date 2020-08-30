<?php
class TbBanco extends Escola_Tabela {
	protected $_name = "banco";
	protected $_rowClass = "Banco";
	protected $_dependentTables = array("TbInfoBancaria");
	
    public function getSql($dados = array()) {
        $sql = $this->select();
		$sql->order("descricao"); 
        return $sql;
    }
	
	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
}