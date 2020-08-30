<?php
class TbCredencialOcorrencia extends Escola_Tabela {
	protected $_name = "credencial_ocorrencia";
	protected $_rowClass = "CredencialOcorrencia";
	protected $_referenceMap = array("Credencial" => array("columns" => array("id_credencial"),
                                                                "refTableClass" => "TbCredencial",
                                                                "refColumns" => array("id_credencial")),
                                        "CredencialOcorrenciaTipo" => array("columns" => array("id_credencial_ocorrencia_tipo"),
                                                                "refTableClass" => "TbCredencialOcorrenciaTipo",
                                                                "refColumns" => array("id_credencial_ocorrencia_tipo")),
                                        "Usuario" => array("columns" => array("id_usuario"),
                                                                "refTableClass" => "TbUsuario",
                                                                "refColumns" => array("id_usuario")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["filtro_id_credencial_ocorrencia_tipo"]) && $dados["filtro_id_credencial_ocorrencia_tipo"]) {
            $sql->where("id_credencial_ocorrencia_tipo = {$dados["filtro_id_credencial_ocorrencia_tipo"]}");
        }
        if (isset($dados["filtro_id_credencial"]) && $dados["filtro_id_credencial"]) {
            $sql->where("id_credencial = {$dados["filtro_id_credencial"]}");
        }
        $sql->order("ocorrencia_data"); 
        $sql->order("ocorrencia_hora");
        return $sql;
    }
}