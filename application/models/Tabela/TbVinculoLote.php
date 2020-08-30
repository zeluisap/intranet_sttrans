<?php
class TbVinculoLote extends Escola_Tabela {
	protected $_name = "vinculo_lote";
	protected $_rowClass = "VinculoLote";
	protected $_dependentTables = array("TbVinculoLoteItem", "TbVinculoLoteOcorrencia");
	protected $_referenceMap = array("Vinculo" => array("columns" => array("id_vinculo"),
												   "refTableClass" => "TbVinculo",
												   "refColumns" => array("id_vinculo")),
                                     "VinculoLoteStatus" => array("columns" => array("id_vinculo_lote_status"),
												   "refTableClass" => "TbVinculoLoteStatus",
												   "refColumns" => array("id_vinculo_lote_status")),
                                     "Arquivo" => array("columns" => array("id_arquivo_pc"),
												   "refTableClass" => "TbArquivo",
												   "refColumns" => array("id_arquivo")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_vinculo"]) && $dados["id_vinculo"]) {
            $sql->where("id_vinculo = {$dados["id_vinculo"]}");
        }
        if (isset($dados["id_vinculo_lote_status"]) && $dados["id_vinculo_lote_status"]) {
            $sql->where("id_vinculo_lote_status = {$dados["id_vinculo_lote_status"]}");
        }
        if (isset($dados["ano"]) && $dados["ano"]) {
            $sql->where("ano = {$dados["ano"]}");
        }
        if (isset($dados["mes"]) && $dados["mes"]) {
            $sql->where("mes = {$dados["mes"]}");
        }
        if (isset($dados["id_pessoa_fisica_coordenador"]) && $dados["id_pessoa_fisica_coordenador"]) {
            $tb = new TbVinculoPessoaTipo();
            $vpt = $tb->getPorChave("CO");
            if ($vpt) {
                $db = Zend_Registry::get("db");
                $sqli = $db->select();
                $sqli->from(array("vp" => "vinculo_pessoa"), array("vp.id_vinculo"));
                $sqli->where("vp.id_vinculo_pessoa_tipo = {$vpt->getId()}");
                $sqli->where("vp.id_pessoa_fisica = {$dados["id_pessoa_fisica_coordenador"]}");
                $sqli->group("vp.id_vinculo");
                $sql->where("id_vinculo in ({$sqli})");
            }
        }
        $sql->order("ano"); 
        $sql->order("mes");
        return $sql;
    }
}