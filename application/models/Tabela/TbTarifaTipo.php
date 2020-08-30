<?php
class TbTarifaTipo extends Escola_Tabela {
    
    protected $_name = "tarifa_tipo";
    protected $_rowClass = "TarifaTipo";
    protected $_dependentTables = array("TbOnibusBdoTarifa");

    public function getPorChave($chave) {
        $uss = $this->fetchAll(" chave = '{$chave}' ");
        if ($uss->count()) {
            return $uss->current();
        }
        return false;
    }
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("chave");
        return $sql;
    }
    
    public function recuperar() {
        $dados = array("IN" => "INTEGRAL",
                       "MP" => "MEIA PASSAGEM",
                       "VP" => "VALE TRANSPORTE");
        foreach ($dados as $chave => $descricao) {
            $obj = $this->getPorChave($chave);
            if (!$obj) {
                $item = $this->createRow();
                $item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
                $item->save();
            }
        }
    }
}