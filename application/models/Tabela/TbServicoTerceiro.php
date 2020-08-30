<?php
class TbServicoTerceiro extends Escola_Tabela {
	protected $_name = "servico_terceiro";
	protected $_rowClass = "ServicoTerceiro";
	protected $_referenceMap = array("Pessoa" => array("columns" => array("id_pessoa"),
												   "refTableClass" => "TbPessoa",
												   "refColumns" => array("id_pessoa")));
}