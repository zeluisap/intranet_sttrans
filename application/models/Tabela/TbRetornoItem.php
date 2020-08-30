<?php
class TbRetornoItem extends Escola_Tabela {
	protected $_name = "retorno_item";
	protected $_rowClass = "RetornoItem";
	protected $_referenceMap = array("Retorno" => array("columns" => array("id_retorno"),
                                                        "refTableClass" => "TbRetorno",
                                                        "refColumns" => array("id_retorno")),
                                     "Boleto" => array("columns" => array("id_boleto"),
                                                        "refTableClass" => "TbBoleto",
                                                        "refColumns" => array("id_boleto")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["filtro_id_retorno"]) && $dados["filtro_id_retorno"]) {
            $sql->where("id_retorno = {$dados["filtro_id_retorno"]}");
        }
        if (isset($dados["filtro_id_boleto"]) && $dados["filtro_id_boleto"]) {
            $sql->where("id_boleto = {$dados["filtro_id_boleto"]}");
        }
        $sql->order("id_retorno");
        $sql->order("nosso_numero");
        $sql->order("id_retorno_item");
        return $sql;
    }
}