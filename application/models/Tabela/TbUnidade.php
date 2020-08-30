<?php
class TbUnidade extends Escola_Tabela {
	protected $_name = "unidade";
	protected $_rowClass = "Unidade";
	protected $_dependentTables = array("Andares");
	protected $_referenceMap = array("UnidadeTipo" => array("columns" => array("id_unidade_tipo"),
															 "refTableClass" => "TbUnidadeTipo",
															 "refColumns" => array("id_unidade_tipo")),
									 "PessoaJuridica" => array("columns" => array("id_pessoa_juridica"),
															 "refTableClass" => "TbPessoaJuridica",
															 "refColumns" => array("id_pessoa_juridica")),
									 "Endereco" => array("columns" => array("id_endereco"),
															 "refTableClass" => "TbEndereco",
															 "refColumns" => array("id_endereco")));
}