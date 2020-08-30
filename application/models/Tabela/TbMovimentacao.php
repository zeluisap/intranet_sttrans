<?php
class TbMovimentacao extends Escola_Tabela {
	protected $_name = "movimentacao";
	protected $_rowClass = "Movimentacao";
	protected $_referenceMap = array("MovimentacaoTipo" => array("columns" => array("id_movimentacao_tipo"),
															 "refTableClass" => "TbMovimentacaoTipo",
															 "refColumns" => array("id_movimentacao_tipo")),
									 "Documento" => array("columns" => array("id_documento"),
															 "refTableClass" => "TbDocumento",
															 "refColumns" => array("id_documento")),
									 "Funcionario" => array("columns" => array("id_funcionario"),
															 "refTableClass" => "TbFuncionario",
															 "refColumns" => array("id_funcionario")),
									 "Setor" => array("columns" => array("id_setor"),
															 "refTableClass" => "TbSetor",
															 "refColumns" => array("id_setor")));
}