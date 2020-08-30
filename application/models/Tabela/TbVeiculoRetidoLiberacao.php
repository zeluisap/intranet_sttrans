<?php
class TbVeiculoRetidoLiberacao extends Escola_Tabela {
	protected $_name = "veiculo_retido_liberacao";
	protected $_rowClass = "VeiculoRetidoLiberacao";
	protected $_referenceMap = array("VeiculoRetido" => array("columns" => array("id_veiculo_retido"),
                                                                "refTableClass" => "TbVeiculoRetido",
                                                                "refColumns" => array("id_veiculo_retido")), 
                                         "Funcionario" => array("columns" => array("id_funcionario"),
                                                                "refTableClass" => "TbFuncionario",
                                                                "refColumns" => array("id_funcionario")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_veiculo_retido"]) && $dados["id_veiculo_retido"]) {
            $sql->where("id_veiculo_retido = {$dados["id_veiculo_retido"]}");
        }
        if (isset($dados["id_funcionario"]) && $dados["id_funcionario"]) {
            $sql->where("id_funcionario = {$dados["id_funcionario"]}");
        }
        if (isset($dados["filtro_data_liberacao"]) && $dados["filtro_data_liberacao"]) {
            $sql->where("data_liberacao = '{$dados["filtro_data_liberacao"]}'");
        }
        $sql->order("data_liberacao");
        $sql->order("hora_liberacao");
        $sql->order("id_veiculo_retido_liberacao");
        return $sql;
    }
}