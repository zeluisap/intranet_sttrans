<?php
class TbOnibusParadaTipo extends Escola_Tabela {
	
    protected $_name = "onibus_parada_tipo";
    protected $_rowClass = "OnibusParadaTipo";
    protected $_dependentTables = array("TbOnibusParada");
	
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
        $dados = array("TE" => "TERMINAL",
                       "PA" => "PARADA DE ÔNIBUS");
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