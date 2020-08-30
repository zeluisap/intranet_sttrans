<?php
class TbBolsistaStatus extends Escola_Tabela {
	protected $_name = "bolsista_status";
	protected $_rowClass = "BolsistaStatus";
	protected $_dependentTables = array("TbBolsista");
				
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

	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("A" => "ATIVO",
						   "I" => "INATIVO");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}