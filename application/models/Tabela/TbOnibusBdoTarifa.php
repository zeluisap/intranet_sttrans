<?php
class TbOnibusBdoTarifa extends Escola_Tabela {
    protected $_name = "onibus_bdo_tarifa";
    protected $_rowClass = "OnibusBdoTarifa";
    protected $_referenceMap = array("OnibusBdo" => array("columns" => array("id_onibus_bdo"),
                                                    "refTableClass" => "OnibusBdo",
                                                    "refColumns" => array("id_onibus_bdo")),
                                    "TarifaTipo" => array("columns" => array("id_tarifa_tipo"),
                                                    "refTableClass" => "TbTarifaTipo",
                                                    "refColumns" => array("id_tarifa_tipo")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_onibus_bdo"]) && $dados["id_onibus_bdo"]) {
            $sql->where("id_onibus_bdo = {$dados["id_onibus_bdo"]}");
        }
        if (isset($dados["id_tarifa_tipo"]) && $dados["id_tarifa_tipo"]) {
            $sql->where("id_tarifa_tipo = {$dados["id_tarifa_tipo"]}");
        }
        return $sql;
    }
}