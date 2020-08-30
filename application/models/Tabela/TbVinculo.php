<?php
class TbVinculo extends Escola_Tabela {
	protected $_name = "vinculo";
	protected $_rowClass = "Vinculo";
	protected $_dependentTables = array("TbBolsaTipo", "TbBolsista", "TbPrevisao", "TbVinculoPessoa");
	protected $_referenceMap = array("VinculoStatus" => array("columns" => array("id_vinculo_status"),
												   "refTableClass" => "TbVinculoStatus",
												   "refColumns" => array("id_vinculo_status")),
                                     "VinculoTipo" => array("columns" => array("id_vinculo_tipo"),
												   "refTableClass" => "TbVinculoTipo",
												   "refColumns" => array("id_vinculo_tipo")),
                                     "PessoaJuridica" => array("columns" => array("id_pessoa_juridica"),
												   "refTableClass" => "TbPessoaJuridica",
												   "refColumns" => array("id_pessoa_juridica")),
                                     "Valor" => array("columns" => array("id_valor"),
												   "refTableClass" => "TbValor",
												   "refColumns" => array("id_valor")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("v" => "vinculo"));
        if (isset($dados["filtro_id_vinculo_tipo"]) && $dados["filtro_id_vinculo_tipo"]) {
            $sql->where("v.id_vinculo_tipo = {$dados["filtro_id_vinculo_tipo"]}");
        }
        if (isset($dados["filtro_id_vinculo_status"]) && $dados["filtro_id_vinculo_status"]) {
            $sql->where("v.id_vinculo_status = {$dados["filtro_id_vinculo_status"]}");
        }
        if (isset($dados["filtro_codigo"]) && $dados["filtro_codigo"]) {
            $sql->where("v.codigo = '{$dados["filtro_codigo"]}'");
        }
        if (isset($dados["filtro_ano"]) && $dados["filtro_ano"]) {
            $sql->where("v.ano = '{$dados["filtro_ano"]}'");
        }
        if (isset($dados["filtro_descricao"]) && $dados["filtro_descricao"]) {
            $sql->where("v.descricao like '%{$dados["filtro_descricao"]}%'");
        }
        if (isset($dados["filtro_id_pessoa_fisica"]) && $dados["filtro_id_pessoa_fisica"]) {
            $tb = new TbVinculoPessoa();
            $sql_vp = $tb->select();
            $sql_vp->from(array("vp" => "vinculo_pessoa"), array("vp.id_vinculo"));
            $sql_vp->where("vp.id_vinculo = v.id_vinculo");
            $tb = new TbVinculoPessoaTipo();
            $vpt = $tb->getPorChave("CO");
            if ($vpt) {
                $sql_vp->where("vp.id_vinculo_pessoa_tipo = {$vpt->getId()}");
            }
            $sql_vp->where("vp.id_pessoa_fisica = '{$dados["filtro_id_pessoa_fisica"]}'");
            $sql->where("v.id_vinculo in ({$sql_vp})");
        }
        $sql->order("v.ano desc"); 
        $sql->order("v.codigo desc");
        return $sql;
    }
}