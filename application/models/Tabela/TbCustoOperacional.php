<?php
class TbCustoOperacional extends Escola_Tabela {
	protected $_name = "custo_operacional";
	protected $_rowClass = "CustoOperacional";
	protected $_referenceMap = array("PessoaJuridica" => array("columns" => array("id_pessoa_juridica"),
												   "refTableClass" => "TbPessoaJuridica",
												   "refColumns" => array("id_pessoa_juridica")));
}