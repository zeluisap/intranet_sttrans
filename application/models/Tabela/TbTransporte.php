<?php
class TbTransporte extends Escola_Tabela {
	protected $_name = "transporte";
	protected $_rowClass = "Transporte";
    protected $_dependentTables = array("TbTransportePessoa", "TbTransporteVeiculo");
	protected $_referenceMap = array("TransporteGrupo" => array("columns" => array("id_transporte_grupo"),
												   "refTableClass" => "TbTransporteGrupo",
												   "refColumns" => array("id_transporte_grupo")),
                                     "Concessao" => array("columns" => array("id_concessao"),
												   "refTableClass" => "TbConcessao",
												   "refColumns" => array("id_concessao")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("t" => "transporte"));
        if (isset($dados["filtro_id_transporte_grupo"]) && $dados["filtro_id_transporte_grupo"]) {
            $sql->where("id_transporte_grupo = {$dados["filtro_id_transporte_grupo"]}");
        }
        if (isset($dados["filtro_codigo"]) && $dados["filtro_codigo"]) {
            $sql->where("codigo = '{$dados["filtro_codigo"]}'");
        }
        if (isset($dados["filtro_placa"]) && $dados["filtro_placa"]) {
            $sql->join(array("tv" => "transporte_veiculo"), "t.id_transporte = tv.id_transporte", array());
            $sql->join(array("tvs" => "transporte_veiculo_status"), "tv.id_transporte_veiculo_status = tvs.id_transporte_veiculo_status", array());
            $sql->join(array("v" => "veiculo"), "tv.id_veiculo = v.id_veiculo", array());
            //$sql->where("tvs.chave = 'A'"); //ativo
            $sql->where("v.placa = '{$dados["filtro_placa"]}'");
        }
        if (isset($dados["filtro_proprietario_nome"]) && $dados["filtro_proprietario_nome"]) {
            $sql->join(array("tp" => "transporte_pessoa"), "t.id_transporte = tp.id_transporte", array());
            $sql->join(array("tps" => "transporte_pessoa_status"), "tp.id_transporte_pessoa_status = tps.id_transporte_pessoa_status", array());
            $sql->join(array("tpt" => "transporte_pessoa_tipo"), "tp.id_transporte_pessoa_tipo = tpt.id_transporte_pessoa_tipo", array());
            $sql->where("tps.chave = 'A'"); //ativo
            $sql->where("tpt.chave = 'PR'"); //proprietario
            $db = Zend_Registry::get("db");
            $sql_pf = $db->select();
            $sql_pf->from(array("pf" => "pessoa_fisica"), array("id_pessoa"));
            $sql_pf->where("pf.nome like '%{$dados["filtro_proprietario_nome"]}%'");
            $sql_pj = $db->select();
            $sql_pj->from(array("pj" => "pessoa_juridica"), array("id_pessoa"));
            $sql_pj->where("pj.razao_social like '%{$dados["filtro_proprietario_nome"]}%'");
            $sql->Where("(tp.id_pessoa in ({$sql_pf}) or tp.id_pessoa in ({$sql_pj}))");
        }
        $sql->order("id_transporte_grupo");
        $sql->order("codigo");
        return $sql;
    }
}