<?php
class TbRequerimentoJari extends Escola_Tabela {
	protected $_name = "requerimento_jari";
	protected $_rowClass = "RequerimentoJari";
        protected $_dependentTables = array("TbRequerimentoJariResposta");
	protected $_referenceMap = array("AutoInfracaoNotificacao" => array("columns" => array("id_auto_infracao_notificacao"),
                                                                "refTableClass" => "TbAutoInfracaoNotificacao",
                                                                "refColumns" => array("id_auto_infracao_notificacao")), 
                                         "Documento" => array("columns" => array("id_documento"),
                                                                "refTableClass" => "TbDocumento",
                                                                "refColumns" => array("id_documento")),
                                         "RequerimentoJariStatus" => array("columns" => array("id_requerimento_jari_status"),
                                                                "refTableClass" => "TbRequerimentoJariStatus",
                                                                "refColumns" => array("id_requerimento_jari_status")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("rj" => "requerimento_jari"));
        $sql->join(array("ain" => "auto_infracao_notificacao"), "rj.id_auto_infracao_notificacao = ain.id_auto_infracao_notificacao", array());
        $sql->join(array("aio" => "auto_infracao_ocorrencia"), "ain.id_auto_infracao_notificacao = aio.id_auto_infracao_notificacao", array());
        $sql->join(array("ai" => "auto_infracao"), "aio.id_auto_infracao = ai.id_auto_infracao", array());
        if (isset($dados["id_auto_infracao_notificacao"]) && $dados["id_auto_infracao_notificacao"]) {
            $sql->where("rj.id_auto_infracao_notificacao = {$dados["id_auto_infracao_notificacao"]}");
        }
        if (isset($dados["id_documento"]) && $dados["id_documento"]) {
            $sql->where("rj.id_documento = {$dados["id_documento"]}");
        }
        if (isset($dados["id_requerimento_jari_status"]) && $dados["id_requerimento_jari_status"]) {
            $sql->where("rj.id_requerimento_jari_status = {$dados["id_requerimento_jari_status"]}");
        }
        if ((isset($dados["filtro_placa"]) && $dados["filtro_placa"]) || (isset($dados["filtro_chassi"]) && $dados["filtro_chassi"])) {
            $sql->join(array("v" => "veiculo"), "ain.id_veiculo = v.id_veiculo", array());
            if (isset($dados["filtro_placa"]) && $dados["filtro_placa"]) {
                $sql->where("v.placa = '{$dados["filtro_placa"]}'");
            }
            if (isset($dados["filtro_chassi"]) && $dados["filtro_chassi"]) {
                $sql->where("v.chassi = '{$dados["filtro_chassi"]}'");
            }
        }
        if (isset($dados["filtro_alfa"]) && $dados["filtro_alfa"]) {
            $sql->where("ai.alfa = '{$dados["filtro_alfa"]}'");
        }
        if (isset($dados["filtro_codigo"]) && $dados["filtro_codigo"]) {
            if (is_numeric($dados["filtro_codigo"])) {
                $dados["filtro_codigo"] = (int)$dados["filtro_codigo"];
            }
            $sql->where("ai.codigo = {$dados["filtro_codigo"]}");
        }
        if (isset($dados["filtro_pf_nome"]) && $dados["filtro_pf_nome"]) {
            $sql->join(array("pf" => "pessoa_fisica"), "ain.id_pessoa_fisica = pf.id_pessoa_fisica", array());
            $sql->where("pf.nome like '%{$dados["filtro_pf_nome"]}%'");
        }
        if (isset($dados["filtro_data_infracao"]) && $dados["filtro_data_infracao"]) {
            $dados["filtro_data_infracao"] = Escola_Util::montaData($dados["filtro_data_infracao"]);
            $sql->where("ain.data_infracao = '{$dados["filtro_data_infracao"]}'");
        }
        $sql->order("data_jari");
        $sql->order("hora_jari");
        return $sql;
    }
}