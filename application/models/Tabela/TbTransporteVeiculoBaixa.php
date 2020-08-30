<?php
class TbTransporteVeiculoBaixa extends Escola_Tabela {
	protected $_name = "transporte_veiculo_baixa";
	protected $_rowClass = "TransporteVeiculoBaixa";
	protected $_referenceMap = array("Usuario" => array("columns" => array("id_usuario"),
												   "refTableClass" => "TbUsuario",
												   "refColumns" => array("id_usuario")),
                                     "TransporteVeiculo" => array("columns" => array("id_transporte_veiculo"),
												   "refTableClass" => "TbTransporteVeiculo",
												   "refColumns" => array("id_transporte_veiculo")),
                                     "BaixaMotivo" => array("columns" => array("id_baixa_motivo"),
												   "refTableClass" => "TbBaixaMotivo",
												   "refColumns" => array("id_baixa_motivo")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_transporte_veiculo"]) && $dados["id_transporte_veiculo"]) {
            $sql->where("id_transporte_veiculo = '{$dados["id_transporte_veiculo"]}'");
        }
        if (isset($dados["id_usuario"]) && $dados["id_usuario"]) {
            $sql->where("id_usuario = {$dados["id_usuario"]}");
        }
        return $sql;
    }
	
}