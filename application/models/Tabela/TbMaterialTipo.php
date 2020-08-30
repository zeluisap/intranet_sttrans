<?php
class TbMaterialTipo extends Escola_Tabela {
	protected $_name = "material_tipo";
	protected $_rowClass = "MaterialTipo";
	protected $_dependentTables = array("TbMaterialTipoItem");
	
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
		$items = $this->listar();
		if (!$items) {
			$dados = array("MC" => "MATERIAL DE CONSUMO",
						   "MP" => "MATERIAL PERMANENTE");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}