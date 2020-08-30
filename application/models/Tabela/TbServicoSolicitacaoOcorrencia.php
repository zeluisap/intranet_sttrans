<?php
class TbServicoSolicitacaoOcorrencia extends Escola_Tabela {
    protected $_name = "servico_solicitacao_ocorrencia";
    protected $_rowClass = "ServicoSolicitacaoOcorrencia";
    protected $_referenceMap = array("ServicoSolicitacaoOcorrenciaTipo" => array("columns" => array("id_servico_solicitacao_ocorrencia_tipo"),
                                                                                 "refTableClass" => "TbServicoSolicitacaoOcorrenciaTipo",
                                                                                 "refColumns" => array("id_servico_solicitacao_ocorrencia_tipo")),
                                     "ServicoSolicitacao" => array("columns" => array("id_servico_solicitacao"),
                                                                   "refTableClass" => "TbServicoSolicitacao",
                                                                   "refColumns" => array("id_servico_solicitacao")),
                                     "Usuario" => array("columns" => array("id_usuario"),
                                                                   "refTableClass" => "TbUsuario",
                                                                   "refColumns" => array("id_usuario")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("ss" => "servico_solicitacao_ocorrencia"));
        if (isset($dados["filtro_id_servico_solicitacao"]) && $dados["filtro_id_servico_solicitacao"]) {
            $sql->where("ss.id_servico_solicitacao = {$dados["filtro_id_servico_solicitacao"]}");
        }
        $sql->order("ss.ocorrencia_data");
        $sql->order("ss.ocorrencia_hora");
        $sql->order("ss.id_servico_solicitacao_ocorrencia");
        return $sql;
    }
}