<?php
class TbRequerimentoJariResposta extends Escola_Tabela {
	protected $_name = "requerimento_jari_resposta";
	protected $_rowClass = "RequerimentoJariResposta";
	protected $_referenceMap = array("RequerimentoJari" => array("columns" => array("id_requerimento_jari"),
                                                                "refTableClass" => "TbRequerimentoJari",
                                                                "refColumns" => array("id_requerimento_jari")), 
                                         "Funcionario" => array("columns" => array("id_funcionario"),
                                                                "refTableClass" => "TbFuncionario",
                                                                "refColumns" => array("id_funcionario")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_requerimento_jari"]) && $dados["id_requerimento_jari"]) {
            $sql->where("id_requerimento_jari = {$dados["id_requerimento_jari"]}");
        }
        if (isset($dados["id_funcionario"]) && $dados["id_funcionario"]) {
            $sql->where("id_funcionario = {$dados["id_funcionario"]}");
        }
        $sql->order("data_resposta");
        $sql->order("id_requerimento_jari_resposta");
        return $sql;
    }
}