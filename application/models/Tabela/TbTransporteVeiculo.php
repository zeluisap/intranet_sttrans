<?php
class TbTransporteVeiculo extends Escola_Tabela {
	protected $_name = "transporte_veiculo";
	protected $_rowClass = "TransporteVeiculo";
    protected $_dependentTables = array("TbTransporteVeiculoBaixa");
	protected $_referenceMap = array("Transporte" => array("columns" => array("id_transporte"),
												   "refTableClass" => "TbTransporte",
												   "refColumns" => array("id_transporte")),
                                     "Veiculo" => array("columns" => array("id_veiculo"),
												   "refTableClass" => "TbVeiculo",
												   "refColumns" => array("id_veiculo")),
                                     "TransporteVeiculoStatus" => array("columns" => array("id_transporte_veiculo_status"),
												   "refTableClass" => "TbTransporteVeiculoStatus",
												   "refColumns" => array("id_transporte_veiculo_status")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("tv" => "transporte_veiculo"));
        $sql->join(array("v" => "veiculo"), "tv.id_veiculo = v.id_veiculo", array());
        if (isset($dados["id_transporte"]) && $dados["id_transporte"]) {
            $sql->where("tv.id_transporte = {$dados["id_transporte"]}");
        }
        if (isset($dados["id_veiculo"]) && $dados["id_veiculo"]) {
            $sql->where("tv.id_veiculo = {$dados["id_veiculo"]}");
        }
        if (isset($dados["filtro_placa"]) && $dados["filtro_placa"]) {
            $sql->where("v.placa = '{$dados["filtro_placa"]}'");
        }
        if (isset($dados["filtro_chassi"]) && $dados["filtro_chassi"]) {
            $sql->where("v.chassi = '{$dados["filtro_chassi"]}'");
        }
        $sql->order("tv.data_cadastro desc");
        $sql->order("v.placa");
        return $sql;
    }
}