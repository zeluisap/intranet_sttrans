<?php
class TbSetor extends Escola_Tabela {
	protected $_name = "setor";
	protected $_rowClass = "Setor";
	protected $_dependentTables = array("TbLotacao", "TbSetor", "TbChamado", "TbChamadoOcorrencia");
	protected $_referenceMap = array("FuncionarioFuncaoTipo" => array("columns" => array("id_funcionario_funcao_tipo"),
															 "refTableClass" => "TbFuncionarioFuncaoTipo",
															 "refColumns" => array("id_funcionario_funcao_tipo")),
									 "SetorNivel" => array("columns" => array("id_setor_nivel"),
															 "refTableClass" => "TbSetorNivel",
															 "refColumns" => array("id_setor_nivel")),
									 "SetorSuperior" => array("columns" => array("id_setor_superior"),
															 "refTableClass" => "TbSetor",
															 "refColumns" => array("id_setor")),
									 "SetorTipo" => array("columns" => array("id_setor_tipo"),
															 "refTableClass" => "TbSetorTipo",
															 "refColumns" => array("id_setor_tipo")));
		
	public function listar($dados = array()) {
		$qtd_por_pagina = 50;
		if (isset($dados["qtd_por_pagina"]) && $dados["qtd_por_pagina"]) {
			$qtd_por_pagina = $dados["qtd_por_pagina"];
		}
		$select = $this->select();
		if (isset($dados["filtro_sigla"]) && $dados["filtro_sigla"]) {
			$select->where(" sigla = '{$dados["filtro_sigla"]}' ");
		}
		if (isset($dados["filtro_descricao"]) && $dados["filtro_descricao"]) {
			$select->where(" descricao like '%{$dados["filtro_descricao"]}%' ");
		}
		if (isset($dados["filtro_id_setor_nivel"]) && $dados["filtro_id_setor_nivel"]) {
			$select->where(" id_setor_nivel = {$dados["filtro_id_setor_nivel"]} ");
		}
		if (isset($dados["filtro_id_setor_tipo"]) && $dados["filtro_id_setor_tipo"]) {
			$select->where(" id_setor_tipo = {$dados["filtro_id_setor_tipo"]} ");
		}
		if (isset($dados["filtro_criterio"]) && $dados["filtro_criterio"]) {
			$select->where(" sigla like '%{$dados["filtro_criterio"]}%' or descricao like '%{$dados["filtro_criterio"]}%' ");
		}
		if (isset($dados["filtro_id_setor_superior"]) && $dados["filtro_id_setor_superior"]) {
			$select->where(" id_setor_superior = {$dados["filtro_id_setor_superior"]} ");
		}
		$select->order("descricao");
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);			
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage($qtd_por_pagina);
		return $paginator;
	}
	
	public function getPorSigla($sigla) {
		$tts = $this->fetchAll(" sigla = '{$sigla}' ");
		if (count($tts)) {
			return $tts->current();
		}
		return false;
	}
	
	public function pegaInstituicao() {
		$tb = new TbSetorTipo();
		$st = $tb->getPorChave("I");
		$tb = new TbSetorNivel();
		$sn = $tb->getPorChave("T");
		if ($st && $sn) {
			$tb = new TbSetor();
			$rg = $tb->fetchAll(" id_setor_tipo = " . $st->getId() . " and id_setor_nivel = " . $sn->getId());
			if ($rg && count($rg)) {
				return $rg->current();
			}
		}
		return false;
	}
}