<?php
class TbOnibusBdo extends Escola_Tabela {
    protected $_name = "onibus_bdo";
    protected $_rowClass = "OnibusBdo";
    protected $_referenceMap = array("TarifaOcorrencia" => array("columns" => array("id_tarifa_ocorrencia"),
                                                        "refTableClass" => "TbTarifaOcorrencia",
                                                        "refColumns" => array("id_tarifa_ocorrencia")),
                                    "Rota" => array("columns" => array("id_rota"),
                                                        "refTableClass" => "TbRota",
                                                        "refColumns" => array("id_rota")),
                                    "TransporteVeiculo" => array("columns" => array("id_transporte_veiculo"),
                                                        "refTableClass" => "TbTransporteVeiculo",
                                                        "refColumns" => array("id_transporte_veiculo")));
    protected $_dependentTables = array("TbOnibusBdoTarifa");
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("ob" => "onibus_bdo"));
        if (isset($dados["id_rota"]) && $dados["id_rota"]) {
            $sql->where("ob.id_rota = {$dados["id_rota"]}");
        }
        if (isset($dados["filtro_id_transporte_veiculo"]) && $dados["filtro_id_transporte_veiculo"]) {
            $sql->where("ob.id_transporte_veiculo = {$dados["filtro_id_transporte_veiculo"]}");
        }
        if (isset($dados["filtro_id_tarifa"]) && $dados["filtro_id_tarifa"]) {
            $sql->join(array("to" => "tarifa_ocorrencia"), "ob.id_tarifa_ocorrencia = to.id_tarifa_ocorrencia", array());
            $sql->where("to.id_tarifa_ocorrencia = {$dados["filtro_id_tarifa"]}");
        }
        if (isset($dados["filtro_data_inicial"]) && $dados["filtro_data_inicial"]) {
            $dados["filtro_data_inicial"] = Escola_Util::montaData($dados["filtro_data_inicial"]);
            $sql->where("ob.data_bdo >= '{$dados["filtro_data_inicial"]}'");
        }
        if (isset($dados["filtro_data_final"]) && $dados["filtro_data_final"]) {
            $dados["filtro_data_final"] = Escola_Util::montaData($dados["filtro_data_final"]);
            $sql->where("ob.data_bdo <= '{$dados["filtro_data_final"]}'");
        }
        $sql->order("id_rota");
        $sql->order("data_bdo");
        $sql->order("bdo");
        return $sql;
    }
}