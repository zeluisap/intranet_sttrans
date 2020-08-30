<?php
class TbMoeda extends Escola_Tabela {
	protected $_name = "moeda";
	protected $_rowClass = "Moeda";
    protected $_dependentTables = array("TbValor");
	
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
    
    public function pega_padrao() {
        $objs = $this->fetchAll("padrao = 'S'");
        if ($objs && count($objs)) {
            return $objs->current();
        }
        return false;
    }
	
	public function getPorSimbolo($simbolo) {
		$uss = $this->fetchAll(" simbolo = '{$simbolo}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
}