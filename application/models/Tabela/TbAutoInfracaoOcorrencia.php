<?php
class TbAutoInfracaoOcorrencia extends Escola_Tabela {
	protected $_name = "auto_infracao_ocorrencia";
	protected $_rowClass = "AutoInfracaoOcorrencia";
	protected $_referenceMap = array("AutoInfracaoOcorrenciaTipo" => array("columns" => array("id_auto_infracao_ocorrencia_tipo"),
												   "refTableClass" => "TbAutoInfracaoOcorrenciaTipo",
												   "refColumns" => array("id_auto_infracao_ocorrencia_tipo")),
                                     "AutoInfracaoDevolucaoStatus" => array("columns" => array("id_auto_infracao_devolucao_status"),
												   "refTableClass" => "TbAutoInfracaoDevolucaoStatus",
												   "refColumns" => array("id_auto_infracao_devolucao_status")),
                                     "AutoInfracao" => array("columns" => array("id_auto_infracao"),
												   "refTableClass" => "TbAutoInfracao",
												   "refColumns" => array("id_auto_infracao")),
                                     "Funcionario" => array("columns" => array("id_funcionario"),
												   "refTableClass" => "TbFuncionario",
												   "refColumns" => array("id_funcionario")),
                                     "AutoInfracaoNotificacao" => array("columns" => array("id_auto_infracao_notificacao"),
												   "refTableClass" => "TbAutoInfracaoNotificacao",
												   "refColumns" => array("id_auto_infracao_notificacao")));
	protected $_dependentTables = array("TbAutoInfracao");
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_auto_infracao"]) && $dados["id_auto_infracao"]) {
            $sql->where("id_auto_infracao = {$dados["id_auto_infracao"]}");
        }
        if (isset($dados["id_auto_infracao_devolucao_status"]) && $dados["id_auto_infracao_devolucao_status"]) {
            $sql->where("id_auto_infracao_devolucao_status = {$dados["id_auto_infracao_devolucao_status"]}");
        }
        $sql->order("data_ocorrencia"); 
        $sql->order("hora_ocorrencia"); 
        $sql->order("id_auto_infracao_ocorrencia_tipo");
        return $sql;
    }
}