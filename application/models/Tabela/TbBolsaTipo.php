<?php
class TbBolsaTipo extends Escola_Tabela {
	protected $_name = "bolsa_tipo";
	protected $_rowClass = "BolsaTipo";
        protected $_dependentTables = array("TbVinculoLoteItem");
	protected $_referenceMap = array("Vinculo" => array("columns" => array("id_vinculo"),
												   "refTableClass" => "TbVinculo",
												   "refColumns" => array("id_vinculo")),
                                         "Valor" => array("columns" => array("id_valor"),
												   "refTableClass" => "TbValor",
												   "refColumns" => array("id_valor")),
                                         "PrevisaoTipo" => array("columns" => array("id_previsao_tipo"),
												   "refTableClass" => "TbPrevisaoTipo",
												   "refColumns" => array("id_previsao_tipo")));	
									 
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_vinculo"]) && $dados["id_vinculo"]) {
            $sql->where("id_vinculo = {$dados["id_vinculo"]}");
        }
        if (isset($dados["id_previsao_tipo"]) && $dados["id_previsao_tipo"]) {
            $sql->where("id_previsao_tipo = {$dados["id_previsao_tipo"]}");
        }
        $sql->order("descricao"); 
        return $sql;
    }
}