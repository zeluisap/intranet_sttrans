<?php
class TbAutoInfracaoNotificacao extends Escola_Tabela {
	protected $_name = "auto_infracao_notificacao";
	protected $_rowClass = "AutoInfracaoNotificacao";
	protected $_referenceMap = array("PessoaFisica" => array("columns" => array("id_pessoa_fisica"),
                                                            "refTableClass" => "TbPessoaFisica",
                                                            "refColumns" => array("id_pessoa_fisica")),
                                     "Veiculo" => array("columns" => array("id_veiculo"),
                                                            "refTableClass" => "TbVeiculo",
                                                            "refColumns" => array("id_veiculo")),
                                     "Arquivo" => array("columns" => array("id_arquivo"),
                                                            "refTableClass" => "TbArquivo",
                                                            "refColumns" => array("id_arquivo")));
	protected $_dependentTables = array("TbAutoInfracaoOcorrencia", "TbVeiculoRetido", "TbRequerimentoJari");
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("ain" => "auto_infracao_notificacao"));
        $sql->join(array("aio" => "auto_infracao_ocorrencia"), "ain.id_auto_infracao_notificacao = aio.id_auto_infracao_notificacao", array());
        $sql->join(array("ai" => "auto_infracao"), "aio.id_auto_infracao = ai.id_auto_infracao", array());
        if (isset($dados["filtro_alfa"]) && $dados["filtro_alfa"]) {
            $dados["filtro_alfa"] = Escola_Util::maiuscula($dados["filtro_alfa"]);
            $sql->where("ai.alfa = '{$dados["filtro_alfa"]}'");
        }
        if (isset($dados["filtro_codigo"]) && $dados["filtro_codigo"]) {
            $dados["filtro_codigo"] = (int)$dados["filtro_codigo"];
            $sql->where("ai.codigo = {$dados["filtro_codigo"]}");
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
        if (isset($dados["filtro_pf_nome"]) && $dados["filtro_pf_nome"]) {
            $sql->join(array("pf" => "pessoa_fisica"), "ain.id_pessoa_fisica = pf.id_pessoa_fisica", array());
            $sql->where("pf.nome like '%{$dados["filtro_pf_nome"]}%'");
        }
        if (isset($dados["filtro_data_infracao"]) && $dados["filtro_data_infracao"]) {
            $dados["filtro_data_infracao"] = Escola_Util::montaData($dados["filtro_data_infracao"]);
            $sql->where("ain.data_infracao = '{$dados["filtro_data_infracao"]}'");
        }
        if (isset($dados["filtro_id_servico_solicitacao_status"]) && $dados["filtro_id_servico_solicitacao_status"]) {
            $sql->join(array("ss" => "servico_solicitacao"), "ain.id_auto_infracao_notificacao = ss.chave", array());
            $sql->where("ss.tipo = 'NO'");
            $sql->where("ss.id_servico_solicitacao_status = '{$dados["filtro_id_servico_solicitacao_status"]}'");
        }
        if (isset($dados["id_pessoa_fisica"]) && $dados["id_pessoa_fisica"]) {
            $sql->where("ain.id_pessoa_fisica = {$dados["id_pessoa_fisica"]}");
        }
        if (isset($dados["id_veiculo"]) && $dados["id_veiculo"]) {
            $sql->where("ain.id_veiculo = {$dados["id_veiculo"]}");
        }
        $sql->order("ain.data_infracao"); 
        $sql->order("ain.hora_infracao");
        
        return $sql;
    }
}