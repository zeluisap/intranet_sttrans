<?php
class TbValor extends Escola_Tabela {
	protected $_name = "valor";
	protected $_rowClass = "Valor";	
    protected $_dependentTables = array("TbBolsaTipo", "TbVinculo", "TbPrevisao", "TbAditivo", "TbVinculoLoteItem");
    protected $_referenceMap = array("Moeda" => array("columns" => array("id_moeda"),
												   "refTableClass" => "TbMoeda",
												   "refColumns" => array("id_moeda")));	
}