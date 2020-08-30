<?php
class TbPassagem extends Escola_Tabela {
	protected $_name = "passagem";
	protected $_rowClass = "Passagem";
	protected $_referenceMap = array("PessoaJuridica" => array("columns" => array("id_pessoa_juridica"),
												   "refTableClass" => "TbPessoaJuridica",
												   "refColumns" => array("id_pessoa_juridica")));
}