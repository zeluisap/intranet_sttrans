<?php
class TbFuncionarioOcorrencia extends Escola_Tabela {
	protected $_name = "funcionario_ocorrencia";
	protected $_rowClass = "FuncionarioOcorrencia";
	protected $_referenceMap = array("FuncionarioOcorrenciaTipo" => array("columns" => array("id_funcionario_ocorrencia_tipo"),
															 "refTableClass" => "TbFuncionarioOcorrenciaTipo",
															 "refColumns" => array("id_funcionario_ocorrencia_tipo")),
									 "Funcionario" => array("columns" => array("id_funcionario"),
															 "refTableClass" => "TbFuncionario",
															 "refColumns" => array("id_funcionario")));    	
}