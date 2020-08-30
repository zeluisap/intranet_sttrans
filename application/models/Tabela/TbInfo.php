<?php
class TbInfo extends Escola_Tabela {
	protected $_name = "info";
	protected $_rowClass = "Info";
	protected $_dependentTables = array("TbInfoRef");
	protected $_referenceMap = array("InfoTipo" => array("columns" => array("id_info_tipo"),
															 "refTableClass" => "TbInfoTipo",
															 "refColumns" => array("id_info_tipo")),
									 "InfoStatus" => array("columns" => array("id_info_status"),
															 "refTableClass" => "TbInfoStatus",
															 "refColumns" => array("id_info_status")),
									 "Arquivo" => array("columns" => array("id_arquivo"),
															 "refTableClass" => "TbArquivo",
															 "refColumns" => array("id_arquivo")));
		
	public function listarPorPagina($dados = array()) {
		$select = $this->select();
		if (isset($dados["filtro_id_info_tipo"]) && $dados["filtro_id_info_tipo"]) {
			$select->where("id_info_tipo = {$dados["filtro_id_info_tipo"]}");
		}
		if (isset($dados["filtro_id_info_status"]) && $dados["filtro_id_info_status"]) {
			$select->where("id_info_status = {$dados["filtro_id_info_status"]}");
		}
		if (isset($dados["filtro_titulo"]) && $dados["filtro_titulo"]) {
			$select->where("titulo like '%{$dados["filtro_titulo"]}%'");
		}
		if (isset($dados["filtro_destaque"]) && $dados["filtro_destaque"]) {
			$select->where(" destaque = '{$dados["filtro_destaque"]}'");
		}
		if (isset($dados["filtro_id_arquivo"]) && $dados["filtro_id_arquivo"]) {
			if (is_bool($dados["filtro_id_arquivo"])) {
				$select->where(" not id_arquivo is null and id_arquivo > 0 ");
			} elseif (is_numeric($dados["filtro_id_arquivo"])) {
				$select->where(" id_arquivo = '{$dados["filtro_id_arquivo"]}'");
			}
		}
		if (isset($dados["filtro_busca"]) && $dados["filtro_busca"]) {
			$sql = " ((titulo like '%{$dados["filtro_busca"]}%') 
			 or (resumo like '%{$dados["filtro_busca"]}%') 
			 or (conteudo like '%{$dados["filtro_busca"]}%')) ";
			$select->where($sql);
		}
		if (isset($dados["order"]) && $dados["order"]) {
			$select->order($dados["order"]);
		} else {
			$select->order("data desc");
			$select->order("id_info desc");
		}
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$qtd_por_pagina = 50;
		if (isset($dados["qtd_por_pagina"]) && $dados["qtd_por_pagina"]) {
			$qtd_por_pagina = $dados["qtd_por_pagina"];
		}
		$paginator->setItemCountPerPage($qtd_por_pagina);
		return $paginator;
	}	
}