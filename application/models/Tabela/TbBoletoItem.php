<?php
class TbBoletoItem extends Escola_Tabela {
	protected $_name = "boleto_item";
	protected $_rowClass = "BoletoItem";
    protected $_dependentTables = array("TbBoleto");
	protected $_referenceMap = array("BoletoItemTipo" => array("columns" => array("id_boleto_item_tipo"),
                                                            "refTableClass" => "TbBoletoItemTipo",
                                                            "refColumns" => array("id_boleto_item_tipo")),
                                     "Boleto" => array("columns" => array("id_boleto"),
                                                        "refTableClass" => "TbBoleto",
                                                        "refColumns" => array("id_boleto")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_boleto_item_tipo"]) && $dados["id_boleto_item_tipo"]) {
            $sql->where("id_boleto_item_tipo = {$dados["id_boleto_item_tipo"]}");
        }
        if (isset($dados["chave"]) && $dados["chave"]) {
            $sql->where("chave = {$dados["chave"]}");
        }
        $sql->order("id_boleto_item");
        return $sql;
    }
}