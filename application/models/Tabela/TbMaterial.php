<?php
class TbMaterial extends Escola_Tabela {
	protected $_name = "material";
	protected $_rowClass = "Material";
	protected $_referenceMap = array("MaterialUniadeTipo" => array("columns" => array("id_material_unidade_tipo"),
												   "refTableClass" => "TbMaterialUnidadeTipo",
												   "refColumns" => array("id_material_unidade_tipo")),
                                     "MaterialTipoItem" => array("columns" => array("id_material_tipo_item"),
												   "refTableClass" => "TbMaterialTipoItem",
												   "refColumns" => array("id_material_tipo_item")),
                                     "PessoaJuridica" => array("columns" => array("id_pessoa_juridica"),
												   "refTableClass" => "TbPessoaJuridica",
												   "refColumns" => array("id_pessoa_juridica")),
                                     "ValorUnitario" => array("columns" => array("id_valor_unitario"),
												   "refTableClass" => "TbValor",
												   "refColumns" => array("id_valor")));
}