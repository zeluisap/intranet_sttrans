<?php
class TbUnidadeTipo extends Escola_Tabela {
	protected $_name = "unidade_tipo";
	protected $_rowClass = "UnidadeTipo";
	protected $_dependentTables = array("TbUnidade");
}