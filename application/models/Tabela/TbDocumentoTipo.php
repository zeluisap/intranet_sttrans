<?php
class TbDocumentoTipo extends Escola_Tabela {
	protected $_name = "documento_tipo";
	protected $_rowClass = "DocumentoTipo";
	protected $_dependentTables = array("TbDocumento");
	protected $_referenceMap = array("DocumentoTipoTarget" => array("columns" => array("id_documento_tipo_target"),
															 "refTableClass" => "TbDocumentoTipoTarget",
															 "refColumns" => array("id_documento_tipo_target")));
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
    
    public function getSql($dados = array()) {
        $sql = $this->select();
		if (isset($dados["filtro_id_documento_tipo_target"]) && $dados["filtro_id_documento_tipo_target"]) {
			$sql->where(" id_documento_tipo_target = {$dados["filtro_id_documento_tipo_target"]} ");
		}
		$sql->order("descricao");
        return $sql;
    }

	public function listarPorPagina($dados = array()) {
        return $this->listar_por_pagina($dados);
	}	
}