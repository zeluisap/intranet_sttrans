<?php
class TbAditivo extends Escola_Tabela {
	protected $_name = "aditivo";
	protected $_rowClass = "Aditivo";
	protected $_referenceMap = array("Vinculo" => array("columns" => array("id_vinculo"),
												   "refTableClass" => "TbVinculo",
												   "refColumns" => array("id_vinculo")),
                                     "Valor" => array("columns" => array("id_valor"),
												   "refTableClass" => "TbValor",
												   "refColumns" => array("id_valor")),
                                     "AditivoTipo" => array("columns" => array("id_aditivo_tipo"),
												   "refTableClass" => "TbAditivoTipo",
												   "refColumns" => array("id_aditivo_tipo")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_vinculo"]) && $dados["id_vinculo"]) {
            $sql->where("id_vinculo = {$dados["id_vinculo"]}");
        }
        $sql->order("ano desc");
        $sql->order("numero desc");
        $sql->order("data desc");
        return $sql;
    }
}