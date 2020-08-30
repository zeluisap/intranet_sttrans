<?php
class TbVinculoLoteOcorrencia extends Escola_Tabela {
	protected $_name = "vinculo_lote_ocorrencia";
	protected $_rowClass = "VinculoLoteOcorrencia";
        protected $_dependentTables = array("TbVinculoLoteOcorrenciaPgto");
	protected $_referenceMap = array("VinculoLote" => array("columns" => array("id_vinculo_lote"),
												   "refTableClass" => "TbVinculoLote",
												   "refColumns" => array("id_vinculo_lote")),
                                     "VinculoLoteOcorrenciaTipo" => array("columns" => array("id_vinculo_lote_ocorrencia_tipo"),
												   "refTableClass" => "TbVinculoLoteOcorrenciaTipo",
												   "refColumns" => array("id_vinculo_lote_ocorrencia_tipo")),
                                     "Usuario" => array("columns" => array("id_usuario"),
												   "refTableClass" => "TbUsuario",
												   "refColumns" => array("id_usuario")),
                                     "Arquivo" => array("columns" => array("id_arquivo_pc"),
												   "refTableClass" => "TbArquivo",
												   "refColumns" => array("id_arquivo")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_vinculo_lote"]) && $dados["id_vinculo_lote"]) {
            $sql->where("id_vinculo_lote = {$dados["id_vinculo_lote"]}");
        }
        if (isset($dados["id_usuario"]) && $dados["id_usuario"]) {
            $sql->where("id_usuario = {$dados["id_usuario"]}");
        }
        if (isset($dados["id_vinculo_lote_ocorrencia_tipo"]) && $dados["id_vinculo_lote_ocorrencia_tipo"]) {
            $sql->where("id_vinculo_lote_ocorrencia_tipo = {$dados["id_vinculo_lote_ocorrencia_tipo"]}");
        }
        $sql->order("data desc");
        $sql->order("hora desc");
        return $sql;
    }
}