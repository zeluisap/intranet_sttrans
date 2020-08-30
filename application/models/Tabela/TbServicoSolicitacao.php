<?php
class TbServicoSolicitacao extends Escola_Tabela
{
    protected $_name = "servico_solicitacao";
    protected $_rowClass = "ServicoSolicitacao";
    protected $_dependentTables = array("TbServicoSolicitacaoPagamento", "TbServicoSolicitacaoDesconto", "TbServicoSolicitacaoOcorrencia");
    protected $_referenceMap = array(
        "ServicoSolicitacaoStatus" => array(
            "columns" => array("id_servico_solicitacao_status"),
            "refTableClass" => "TbServicoSolicitacaoStatus",
            "refColumns" => array("id_servico_solicitacao_status")
        ),
        "ServicoTransporteGrupo" => array(
            "columns" => array("id_servico_transporte_grupo"),
            "refTableClass" => "TbServicoTransporteGrupo",
            "refColumns" => array("id_servico_transporte_grupo")
        ),
        "Valor" => array(
            "columns" => array("id_valor"),
            "refTableClass" => "TbValor",
            "refColumns" => array("id_valor")
        )
    );

    public function getSql($dados = array())
    {
        $sql = $this->select();
        $sql->from(array("ss" => "servico_solicitacao"));
        if (isset($dados["id_transporte"]) && $dados["id_transporte"]) {
            $wheres = array();

            $wheres[] = "ss.tipo = 'TR' and ss.chave = {$dados["id_transporte"]}";

            $tb = new TbTransporteVeiculo();
            $objs = $tb->listar(array("id_transporte" => $dados["id_transporte"]));
            if ($objs && count($objs)) {
                $tvs = array();
                foreach ($objs as $obj) {
                    if ($obj->ativo()) {
                        $tvs[] = $obj->getId();
                    }
                }
                if (count($tvs)) {
                    $wheres[] = "ss.tipo = 'TV' and chave in (" . implode(",", $tvs) . ")";
                }
            }
            $tb = new TbTransportePessoa();
            $objs = $tb->listar(array("id_transporte" => $dados["id_transporte"]));
            if ($objs && count($objs)) {
                $tps = array();
                foreach ($objs as $obj) {
                    //                    if ($obj->ativo()) {
                    $tps[] = $obj->getId();
                    //                    }
                }
                if (count($tvs)) {
                    $wheres[] = "ss.tipo = 'TP' and chave in (" . implode(",", $tps) . ")";
                }
            }

            $sql->where("( (" . implode(") or (", $wheres) . ") )");
        }
        if (isset($dados["id_servico_solicitacao_status"]) && $dados["id_servico_solicitacao_status"]) {
            $sql->where("ss.id_servico_solicitacao_status = {$dados["id_servico_solicitacao_status"]}");
        }
        if (isset($dados["filtro_id_servico_solicitacao_status"]) && $dados["filtro_id_servico_solicitacao_status"]) {
            $sql->where("ss.id_servico_solicitacao_status = {$dados["filtro_id_servico_solicitacao_status"]}");
        }
        if (isset($dados["id_servico_transporte_grupo"]) && $dados["id_servico_transporte_grupo"]) {
            $sql->where("ss.id_servico_transporte_grupo = {$dados["id_servico_transporte_grupo"]}");
        }
        if (isset($dados["filtro_id_servico"]) && $dados["filtro_id_servico"]) {
            $sql->join(array("stg" => "servico_transporte_grupo"), "ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo", array());
            $sql->join(array("s" => "servico"), "stg.id_servico = s.id_servico", array());
            $sql->where("s.id_servico = {$dados["filtro_id_servico"]}");
        }
        if (isset($dados["filtro_ano_referencia"]) && $dados["filtro_ano_referencia"]) {
            $sql->where("ss.ano_referencia = {$dados["filtro_ano_referencia"]}");
        }
        if (isset($dados["filtro_mes_referencia"]) && $dados["filtro_mes_referencia"]) {
            $sql->where("ss.mes_referencia = {$dados["filtro_mes_referencia"]}");
        }
        if (isset($dados["ano_referencia"]) && $dados["ano_referencia"]) {
            $sql->where("ss.ano_referencia = {$dados["ano_referencia"]}");
        }
        if (isset($dados["mes_referencia"]) && $dados["mes_referencia"]) {
            $sql->where("ss.mes_referencia = {$dados["mes_referencia"]}");
        }
        if (isset($dados["tipo"]) && $dados["tipo"]) {
            $sql->where("ss.tipo = '{$dados["tipo"]}'");
        }
        if (isset($dados["chave"]) && $dados["chave"]) {
            $sql->where("ss.chave = {$dados["chave"]}");
        }
        if (isset($dados["codigo"]) && $dados["codigo"]) {
            $sql->where("ss.codigo = {$dados["codigo"]}");
        }

        if (isset($dados["filtro_id_veiculo_tipo"]) && $dados["filtro_id_veiculo_tipo"]) {
            $sql->where("ss.tipo = 'TV'");
            $sql->join(array("tv" => "transporte_veiculo"), "ss.chave = tv.id_transporte_veiculo", array());
            $sql->join(array("v" => "veiculo"), "tv.id_veiculo = v.id_veiculo", array());
            $sql->where("v.id_veiculo_tipo = {$dados["filtro_id_veiculo_tipo"]}");
        }

        $sql->order("ss.ano_referencia desc");
        $sql->order("ss.codigo desc");
        return $sql;
    }
}
