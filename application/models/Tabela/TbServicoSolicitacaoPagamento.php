<?php
class TbServicoSolicitacaoPagamento extends Escola_Tabela {
	protected $_name = "servico_solicitacao_pagamento";
	protected $_rowClass = "ServicoSolicitacaoPagamento";
	protected $_referenceMap = array("ServicoSolicitacaoPagamentoStatus" => array("columns" => array("id_servico_solicitacao_pagamento_status"),
                                                                                  "refTableClass" => "TbServicoSolicitacaoPagamentoStatus",
                                                                                  "refColumns" => array("id_servico_solicitacao_pagamento_status")),
                                     "ServicoSolicitacao" => array("columns" => array("id_servico_solicitacao"),
                                                                    "refTableClass" => "TbServicoSolicitacao",
                                                                    "refColumns" => array("id_servico_solicitacao")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_servico_solicitacao"]) && $dados["id_servico_solicitacao"]) {
            $sql->where("id_servico_solicitacao = {$dados["id_servico_solicitacao"]}");
        }
        $sql->order("data_pagamento");
        return $sql;
    }
}