<?php
class TbRotaDia extends Escola_Tabela {
    protected $_name = "rota_dia";
    protected $_rowClass = "RotaDia";
    protected $_referenceMap = array("Rota" => array("columns" => array("id_rota"),
                                                    "refTableClass" => "TbRota",
                                                    "refColumns" => array("id_rota")),
                                "DiaTipo" => array("columns" => array("id_dia_tipo"),
                                                    "refTableClass" => "TbDiaTipo",
                                                    "refColumns" => array("id_dia_tipo")));
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_rota"]) && $dados["id_rota"]) {
            $sql->where("id_rota = {$dados["id_rota"]}");
        }
        if (isset($dados["id_dia_tipo"]) && $dados["id_dia_tipo"]) {
            $sql->where("id_dia_tipo = {$dados["id_dia_tipo"]}");
        }
        return $sql;
    }
}