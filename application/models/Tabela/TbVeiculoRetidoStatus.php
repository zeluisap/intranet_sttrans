<?php
class TbVeiculoRetidoStatus extends Escola_Tabela {
	protected $_name = "veiculo_retido_status";
	protected $_rowClass = "VeiculoRetidoStatus";
	protected $_dependentTables = array("TbVeiculoRetido");
	
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
        $dados = array("AL" => "AGUARDANDO LIBERAÇAO",
                       "LI" => "LIBERADO");
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