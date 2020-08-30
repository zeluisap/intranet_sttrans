<?php
class TbVinculoLoteItem extends Escola_Tabela {
	protected $_name = "vinculo_lote_item";
	protected $_rowClass = "VinculoLoteItem";
	protected $_dependentTables = array("TbBolsistaOcorrencia");
	protected $_referenceMap = array("VinculoLote" => array("columns" => array("id_vinculo_lote"),
												   "refTableClass" => "TbVinculoLote",
												   "refColumns" => array("id_vinculo_lote")),
                                     "Valor" => array("columns" => array("id_valor"),
												   "refTableClass" => "TbValor",
												   "refColumns" => array("id_valor")),
                                     "VinculoLoteItemStatus" => array("columns" => array("id_vinculo_lote_item_status"),
												   "refTableClass" => "TbVinculoLoteItemStatus",
												   "refColumns" => array("id_vinculo_lote_item_status")),
                                     "BolsaTipo" => array("columns" => array("id_bolsa_tipo"),
												   "refTableClass" => "TbBolsaTipo",
												   "refColumns" => array("id_bolsa_tipo")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("vl" => "vinculo_lote_item"));
        if (isset($dados["id_vinculo_lote"]) && $dados["id_vinculo_lote"]) {
            $sql->where("vl.id_vinculo_lote = {$dados["id_vinculo_lote"]}");
        }
        if (isset($dados["tipo"]) && $dados["tipo"]) {
            $sql->where("vl.tipo = '{$dados["tipo"]}'");
        }
        if (isset($dados["chave"]) && $dados["chave"]) {
            $sql->where("vl.chave = {$dados["chave"]}");
        }
        if (isset($dados["vinculo_lote_item_status"]) && $dados["vinculo_lote_item_status"]) {
            $sql->join(array("vlis" => "vinculo_lote_item_status"), "vl.id_vinculo_lote_item_status = vlis.id_vinculo_lote_item_status", array());
            $sql->where("vlis.chave = '{$dados["vinculo_lote_item_status"]}'");
        }
        if (isset($dados["id_vinculo_lote_item_status"]) && $dados["id_vinculo_lote_item_status"]) {
            $sql->where("vl.id_vinculo_lote_item_status = {$dados["id_vinculo_lote_item_status"]}");
        }
        if (isset($dados["id_bolsa_tipo"]) && $dados["id_bolsa_tipo"]) {
            $sql->where("vl.id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
/*
            $sql->where("vl.tipo = 'BO'");
            $sql->join(array("b" => "bolsista"), "vl.chave = b.id_bolsista", array());
            $sql->where("b.id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
*/
        }
        $sql->order("vl.tipo");
        $sql->order("vl.id_vinculo_lote_item");
        return $sql;
    }
    
    public function listar_tipo() {
        $tb = new TbPrevisaoTipo();
        $objs = $tb->listar();
        if ($objs && count($objs)) {
            $items = array();
            foreach ($objs as $obj) {
                $items[$obj->chave] = $obj->descricao;
            }
            return $items;
        }
        return false;
    }
}