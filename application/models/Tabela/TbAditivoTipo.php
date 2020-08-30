<?php
class TbAditivoTipo extends Escola_Tabela {
	protected $_name = "aditivo_tipo";
	protected $_rowClass = "AditivoTipo";
	protected $_dependentTables = array("TbAditivo");						 
	
    public function getSql($dados = array()) {
        $sql = $this->select();
		$sql->order("descricao"); 
        return $sql;
    }

    public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("V" => "ADITIVO DE VALOR",
						   "D" => "ADITIVO DE DATA");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
	
	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
}