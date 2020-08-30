<?php
class TbServicoTransporteGrupo extends Escola_Tabela
{
    protected $_name = "servico_transporte_grupo";
    protected $_rowClass = "ServicoTransporteGrupo";
    protected $_dependentTables = array("TbServicoSolicitacao");
    protected $_referenceMap = array(
        "Servico" => array(
            "columns" => array("id_servico"),
            "refTableClass" => "TbServico",
            "refColumns" => array("id_servico")
        ),
        "TransporteGrupo" => array(
            "columns" => array("id_transporte_grupo"),
            "refTableClass" => "TbTransporteGrupo",
            "refColumns" => array("id_transporte_grupo")
        ),
        "Valor" => array(
            "columns" => array("id_valor"),
            "refTableClass" => "TbValor",
            "refColumns" => array("id_valor")
        ),
        "Periodicidade" => array(
            "columns" => array("id_periodicidade"),
            "refTableClass" => "TbPeriodicidade",
            "refColumns" => array("id_periodicidade")
        )
    );

    public function getSql($dados = array())
    {
        $sql = $this->select();
        $sql->from(array("stg" => "servico_transporte_grupo"));
        $sql->join(array("s" => "servico"), "stg.id_servico = s.id_servico", array());
        if (isset($dados["id_transporte_grupo"]) && $dados["id_transporte_grupo"]) {
            $sql->where("stg.id_transporte_grupo = {$dados["id_transporte_grupo"]}");
        }
        if (isset($dados["id_servico"]) && $dados["id_servico"]) {
            $sql->where("stg.id_servico = {$dados["id_servico"]}");
        }
        if (isset($dados["obrigatorio"]) && $dados["obrigatorio"]) {
            $sql->where("stg.obrigatorio = '{$dados["obrigatorio"]}'");
        }
        if (isset($dados["id_servico_referencia"]) && $dados["id_servico_referencia"]) {
            $sql->where("s.id_servico_referencia = {$dados["id_servico_referencia"]}");
        }
        if (isset($dados["servico_referencia_chave"]) && $dados["servico_referencia_chave"]) {
            $sql->join(array("sr" => "servico_referencia"), "s.id_servico_referencia = sr.id_servico_referencia", array());
            $sql->where("sr.chave = '{$dados["servico_referencia_chave"]}'");
        }
        $sql->order("s.descricao");
        return $sql;
    }
}
