<?php
class TbBaixaMotivo extends Escola_Tabela {
	protected $_name = "baixa_motivo";
	protected $_rowClass = "BaixaMotivo";
    protected $_dependentTables = array("TbTransporteVeiculoBaixa");
									 
    public function getSql($dados = array()) {
        $sql = $this->select();
		$sql->order("descricao"); 
        return $sql;
    }
    
    public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}    
}