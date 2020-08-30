<?php
class TbTarifa extends Escola_Tabela {
	
    protected $_name = "tarifa";
    protected $_rowClass = "Tarifa";
    protected $_referenceMap = array("Valor" => array("columns" => array("id_valor_atual"),
                                     "refTableClass" => "TbValor",
                                     "refColumns" => array("id_valor")));
    protected $_dependentTables = array("TbTarifaOcorrencia");

    public function getPorDescricao($descricao) {
        $uss = $this->fetchAll(" descricao = '{$descricao}' ");
        if ($uss->count()) {
            return $uss->current();
        }
        return false;
    }

    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("descricao");
        return $sql;
    }
}