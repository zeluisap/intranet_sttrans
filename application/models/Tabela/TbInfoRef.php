<?php
class TbInfoRef extends Escola_Tabela {
	protected $_name = "info_ref";
	protected $_rowClass = "InfoRef";
	protected $_referenceMap = array("Info" => array("columns" => array("id_info"),
															 "refTableClass" => "TbInfo",
															 "refColumns" => array("id_info")));
}