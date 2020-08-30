<?php
class TbInfoBancaria extends Escola_Tabela {
	protected $_name = "info_bancaria";
	protected $_rowClass = "InfoBancaria";
	protected $_dependentTables = array("TbInfoBancariaRef");
	protected $_referenceMap = array("Banco" => array("columns" => array("id_banco"),
												   "refTableClass" => "TbBanco",
												   "refColumns" => array("id_banco")),
                                     "InfoBancariaTipo" => array("columns" => array("id_info_bancaria_tipo"),
												   "refTableClass" => "TbInfoBancariaTipo",
												   "refColumns" => array("id_info_bancaria_tipo")));	
}