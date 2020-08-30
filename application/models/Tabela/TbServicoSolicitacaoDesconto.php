<?php
class TbServicoSolicitacaoDesconto extends Escola_Tabela {
    protected $_name = "servico_solicitacao_desconto";
    protected $_rowClass = "ServicoSolicitacaoDesconto";
    protected $_referenceMap = array("ServicoSolicitacao" => array("columns" => array("id_servico_solicitacao"),
                                                                    "refTableClass" => "TbServicoSolicitacao",
                                                                    "refColumns" => array("id_servico_solicitacao")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("ssd" => "servico_solicitacao_desconto"));
        if (isset($dados["filtro_id_servico_solicitcao"]) && $dados["filtro_id_servico_solicitcao"]) {
            $sql->where("ssd.id_servico_solicitacao = {$dados["filtro_id_servico_solicitcao"]}");
        }
        $sql->order("ssd.id_servico_solicitacao desc");
        return $sql;
    }
}