<?php
class TbEndereco extends Zend_Db_Table_Abstract {
	protected $_name = "endereco";
	protected $_rowClass = "Endereco";
	protected $_referenceMap = array("Bairro" => array("columns" => array("id_bairro"),
													   "refTableClass" => "TbBairro",
													   "refColumns" => array("id_bairro")));
}