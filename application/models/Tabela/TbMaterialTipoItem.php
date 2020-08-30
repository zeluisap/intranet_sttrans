<?php
class TbMaterialTipoItem extends Escola_Tabela {
	protected $_name = "material_tipo_item";
	protected $_rowClass = "MaterialTipoItem";
	protected $_dependentTables = array("TbMaterial");
	protected $_referenceMap = array("MaterialTipo" => array("columns" => array("id_material_tipo"),
												   "refTableClass" => "TbMaterialTipo",
												   "refColumns" => array("id_material_tipo")));
	
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
        if (isset($dados["id_material_tipo"]) && $dados["id_material_tipo"]) {
            $sql->where("id_material_tipo = {$dados["id_material_tipo"]}");
        }
        $sql->order("descricao");
        return $sql;
    }
}