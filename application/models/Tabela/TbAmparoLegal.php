<?php
class TbAmparoLegal extends Escola_Tabela {
	protected $_name = "amparo_legal";
	protected $_rowClass = "AmparoLegal";
	protected $_dependentTables = array("TbAmparoLegalItem");
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("descricao"); 
        return $sql;
    }
	
	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
}