<?php
class TbCnhCategoria extends Escola_Tabela {
	protected $_name = "cnh_categoria";
	protected $_rowClass = "CnhCategoria";
	protected $_dependentTables = array("TbPessoaMotorista");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" codigo = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function getPorCodigo($codigo) {
		$uss = $this->fetchAll(" codigo = '{$codigo}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("codigo");
        return $sql;
    }
}