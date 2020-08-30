<?php
class TbRetorno extends Escola_Tabela {
	protected $_name = "retorno";
	protected $_rowClass = "Retorno";
	protected $_dependentTables = array("TbRetornoItem");
	protected $_referenceMap = array("RetornoTipo" => array("columns" => array("id_retorno_tipo"),
                                                        "refTableClass" => "TbRetornoTipo",
                                                        "refColumns" => array("id_retorno_tipo")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("data_importacao desc");
        $sql->order("hora_importacao desc");
        return $sql;
    }
}