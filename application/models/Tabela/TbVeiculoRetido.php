<?php
class TbVeiculoRetido extends Escola_Tabela {
	protected $_name = "veiculo_retido";
	protected $_rowClass = "VeiculoRetido";
	protected $_referenceMap = array("Veiculo" => array("columns" => array("id_veiculo"),
                                                                "refTableClass" => "TbVeiculo",
                                                                "refColumns" => array("id_veiculo")), 
                                         "AutoInfracaoNotificacao" => array("columns" => array("id_auto_infracao_notificacao"),
                                                                "refTableClass" => "TbAutoInfracaoNotificacao",
                                                                "refColumns" => array("id_auto_infracao_notificacao")),
                                         "VeiculoRetidoStatus" => array("columns" => array("id_veiculo_retido_status"),
                                                                "refTableClass" => "TbVeiculoRetidoStatus",
                                                                "refColumns" => array("id_veiculo_retido_status")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("vr" => "veiculo_retido"));
        $sql->join(array("ain" => "auto_infracao_notificacao"), "vr.id_auto_infracao_notificacao = ain.id_auto_infracao_notificacao", array());
        $sql->join(array("aio" => "auto_infracao_ocorrencia"), "ain.id_auto_infracao_notificacao = aio.id_auto_infracao_notificacao", array());
        $sql->join(array("ai" => "auto_infracao"), "aio.id_auto_infracao = ai.id_auto_infracao", array());
        if (isset($dados["filtro_veiculo_retido_status"]) && $dados["filtro_veiculo_retido_status"]) {
            $sql->join(array("vrs" => "veiculo_retido_status"), "vr.id_veiculo_retido_status = vrs.id_veiculo_retido_status", array());
            $sql->where("vrs.chave = '{$dados["filtro_veiculo_retido_status"]}'");
        }
        if (isset($dados["filtro_id_veiculo"]) && $dados["filtro_id_veiculo"]) {
            $sql->where("vr.id_veiculo = {$dados["filtro_id_veiculo"]}");
        }
        if ((isset($dados["filtro_placa"]) && $dados["filtro_placa"]) || (isset($dados["filtro_chassi"]) && $dados["filtro_chassi"])) {
            $sql->join(array("v" => "veiculo"), "vr.id_veiculo = v.id_veiculo", array());
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
        $sql->order("vr.data_veiculo_retido");
        $sql->order("vr.hora_veiculo_retido");
        return $sql;
    }
    
    public function inserirVeiculo($veiculo, $not) {
        $vr = $this->createRow();
        if ($veiculo) {
            $vr->id_veiculo = $veiculo->getId();
        }
        if ($not) {
            $vr->id_auto_infracao_notificacao = $not->getId();
        }
        $erros = $vr->getErrors();
        if (!$erros) {
            $vr->save();
        }
    }
    
    public function retido($veiculo) {
        if ($veiculo && $veiculo->getId()) {
            $vrs = $this->listar(array("filtro_id_veiculo" => $veiculo->getId(), "filtro_veiculo_retido_status" => "AL"));
            if ($vrs) {
                return true;
            }
        }
        return false;
    }
}