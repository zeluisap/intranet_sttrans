<?php
class TbRotaViagem extends Escola_Tabela {
    protected $_name = "rota_viagem";
    protected $_rowClass = "RotaViagem";
    protected $_referenceMap = array("Rota" => array("columns" => array("id_rota"),
                                                    "refTableClass" => "TbRota",
                                                    "refColumns" => array("id_rota")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_rota"]) && $dados["id_rota"]) {
            $sql->where("id_rota = {$dados["id_rota"]}");
        }
        if (isset($dados["dia_semana"])) {
            $sql->where("dia_semana = {$dados["dia_semana"]}");
        }
        $sql->order("id_rota");
        $sql->order("dia_semana");
        $sql->order("hora_saida");
        return $sql;
    }
}