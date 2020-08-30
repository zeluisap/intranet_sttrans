<?php
class TbAutoInfracaoDevolucaoStatus extends Escola_Tabela {
	protected $_name = "auto_infracao_devolucao_status";
	protected $_rowClass = "AutoInfracaoDevolucaoStatus";
	protected $_dependentTables = array("TbAutoInfracao");
	
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
        $sql->order("id_auto_infracao_devolucao_status");
        return $sql;
    }

    public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("O" => "OK",
						   "R" => "RASURADO",
                           "C" => "CANCELADO",
                           "E" => "EXTRAVIADO",
                           "D" => "DADOS INCORRETOS");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}