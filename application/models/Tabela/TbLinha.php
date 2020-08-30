<?php
class TbLinha extends Escola_Tabela {
    protected $_name = "linha";
    protected $_rowClass = "Linha";
    protected $_dependentTables = array("TbRota");

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