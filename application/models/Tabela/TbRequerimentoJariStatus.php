<?php
class TbRequerimentoJariStatus extends Escola_Tabela {
	protected $_name = "requerimento_jari_status";
	protected $_rowClass = "RequerimentoJariStatus";
	protected $_dependentTables = array("TbRequerimentoJari");
	
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
        $dados = array("AR" => "AGUARDANDO RESPOSTA",
                       "DT" => "DEFERIMENTO TOTAL",
                       "DP" => "DEFERIMENTO PARCIAL",
                       "IN" => "INDEFERIDO");
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