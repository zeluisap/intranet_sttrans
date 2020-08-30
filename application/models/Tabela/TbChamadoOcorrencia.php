<?php
class TbChamadoOcorrencia extends Escola_Tabela {
	protected $_name = "chamado_ocorrencia";
	protected $_rowClass = "ChamadoOcorrencia";
	protected $_referenceMap = array("ChamadoOcorrenciaTipo" => array("columns" => array("id_chamado_ocorrencia_tipo"),
															 "refTableClass" => "TbChamadoOcorrenciaTipo",
															 "refColumns" => array("id_chamado_ocorrencia_tipo")),
									 "Setor" => array("columns" => array("id_setor"),
															 "refTableClass" => "TbSetor",
															 "refColumns" => array("id_setor")),
									 "Funcionario" => array("columns" => array("id_funcionario"),
															 "refTableClass" => "TbFuncionario",
															 "refColumns" => array("id_funcionario")));    	
}