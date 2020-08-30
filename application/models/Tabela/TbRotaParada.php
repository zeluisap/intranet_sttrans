<?php
class TbRotaParada extends Escola_Tabela {
    protected $_name = "rota_parada";
    protected $_rowClass = "RotaParada";
    protected $_referenceMap = array("Rota" => array("columns" => array("id_rota"),
                                                        "refTableClass" => "TbRota",
                                                        "refColumns" => array("id_rota")),
                                     "OnibusParada" => array("columns" => array("id_onibus_parada"),
                                                        "refTableClass" => "TbOnibusParada",
                                                        "refColumns" => array("id_onibus_parada")));

    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_rota"]) && $dados["id_rota"]) {
            $sql->where("id_rota = {$dados["id_rota"]}");
        }
        if (isset($dados["id_onibus_parada"]) && $dados["id_onibus_parada"]) {
            $sql->where("id_onibus_parada = {$dados["id_onibus_parada"]}");
        }
        $sql->order("id_rota");
        $sql->order("ordem");
        return $sql;
    }
    
    public static function pega_ultima_ordem($rota) {
        if ($rota && $rota->getId()) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("rp" => "rota_parada"), array("maximo" => "max(ordem)"));
            $sql->where("id_rota = {$rota->getId()}");
            $stmt = $db->query($sql);
            if ($stmt && $stmt->rowCount()) {
                $obj = $stmt->fetchObject();
                return $obj->maximo;
            }
        }
        return 0;
    }
}