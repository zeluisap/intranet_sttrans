<?php
class TbRelatorioTipo extends Escola_Tabela {
	protected $_name = "relatorio_tipo";
	protected $_rowClass = "RelatorioTipo";
	protected $_dependentTables = array("TbRelatorio");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("descricao");
        return $sql;
    }
}