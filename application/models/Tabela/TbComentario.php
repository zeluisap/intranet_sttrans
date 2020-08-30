<?php
class TbComentario extends Escola_Tabela {
	protected $_name = "comentario";
	protected $_rowClass = "Comentario";
	protected $_referenceMap = array("ComentarioStatus" => array("columns" => array("id_comentario_status"),
															 "refTableClass" => "TbComentarioStatus",
															 "refColumns" => array("id_comentario_status")),
									 "Info" => array("columns" => array("id_info"),
															 "refTableClass" => "TbInfo",
															 "refColumns" => array("id_info")));
		
	public function listarPorPagina($dados = array()) {
		$select = $this->select();
		if (isset($dados["filtro_id_comentario_status"]) && $dados["filtro_id_comentario_status"]) {
			$select->where(" id_comentario_status = {$dados["filtro_id_comentario_status"]} ");
		}
		if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
			$select->where(" nome like '%{$dados["filtro_nome"]}%' ");
		}
		$select->order("data");
		$select->order("hora");
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage(50);
		return $paginator;
	}	
}