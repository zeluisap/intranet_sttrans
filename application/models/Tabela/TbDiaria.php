<?php
class TbDiaria extends Escola_Tabela {
	protected $_name = "diaria";
	protected $_rowClass = "Diaria";
	protected $_referenceMap = array("PessoaFisica" => array("columns" => array("id_pessoa_fisica"),
												   "refTableClass" => "TbPessoaFisica",
												   "refColumns" => array("id_pessoa_fisica")));
}