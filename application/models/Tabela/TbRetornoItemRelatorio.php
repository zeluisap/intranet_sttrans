<?php
class TbRetornoItemRelatorio extends Escola_Tabela {
	protected $_name = "retorno_item_relatorio";
	protected $_rowClass = "RetornoItemRelatorio";
	protected $_referenceMap = array("RetornoItem" => array("columns" => array("id_retornoItem"),
                                                        "refTableClass" => "TbRetornoItem",
                                                        "refColumns" => array("id_retorno_item")),
                                     "BoletoItem" => array("columns" => array("id_boleto_item"),
                                                        "refTableClass" => "TbBoletoItem",
                                                        "refColumns" => array("id_boleto_item")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["filtro_id_retorno_item"]) && $dados["filtro_id_retorno_item"]) {
            $sql->where("id_retorno_item = {$dados["filtro_id_retorno_item"]}");
        }
        if (isset($dados["filtro_id_boleto_item"]) && $dados["filtro_id_boleto_item"]) {
            $sql->where("id_boleto_item = {$dados["filtro_id_boleto_item"]}");
        }
        $sql->order("id_retorno_item_relatorio");
        return $sql;
    }
}