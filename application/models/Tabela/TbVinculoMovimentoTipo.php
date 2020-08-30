<?php
class TbVinculoMovimentoTipo extends Escola_Tabela {
	protected $_name = "vinculo_movimento_tipo";
	protected $_rowClass = "VinculoMovimentoTipo";
	protected $_dependentTables = array("TbVinculoMovimento");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
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
        $sql->order("descricao");
        return $sql;
    }

    public function recuperar() {
        $dados = array("RE" => "RECEITA",
                       "DE" => "DESPESA");
        foreach ($dados as $chave => $descricao) {
            $obj = $this->getPorChave($chave);
            if (!$obj) {
                $item = $this->createRow();
                $item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
                $item->save();
            }
        }
	}
}