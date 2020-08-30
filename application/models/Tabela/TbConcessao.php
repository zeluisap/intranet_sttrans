<?php
class TbConcessao extends Escola_Tabela {
	protected $_name = "concessao";
	protected $_rowClass = "Concessao";
	protected $_referenceMap = array("ConcessaoTipo" => array("columns" => array("id_concessao_tipo"),
												   "refTableClass" => "TbConcessaoTipo",
												   "refColumns" => array("id_concessao_tipo")),
                                     "ConcessaoValidade" => array("columns" => array("id_concessao_validade"),
												   "refTableClass" => "TbConcessaoValidade",
												   "refColumns" => array("id_concessao_validade")));
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("descricao");
        return $sql;
    }	
}