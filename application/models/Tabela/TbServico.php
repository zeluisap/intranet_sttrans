<?php
class TbServico extends Escola_Tabela {
	protected $_name = "servico";
	protected $_rowClass = "Servico";
    protected $_dependentTables = array("TbServicoTransporteGrupo");
	protected $_referenceMap = array("ServicoTipo" => array("columns" => array("id_servico_tipo"),
												   "refTableClass" => "TbServicoTipo",
												   "refColumns" => array("id_servico_tipo")),
                                     "ServicoReferencia" => array("columns" => array("id_servico_referencia"),
												   "refTableClass" => "TbServicoReferencia",
												   "refColumns" => array("id_servico_referencia")));
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("s" => "servico"));
        if (isset($dados["filtro_codigo"]) && $dados["filtro_codigo"]) {
            $sql->where("s.codigo = '{$dados["filtro_codigo"]}'");
        }
        if (isset($dados["filtro_descricao"]) && $dados["filtro_descricao"]) {
            $sql->where("s.descricao like '%{$dados["filtro_descricao"]}%'");
        }
        $sql->order("s.descricao");
        return $sql;
    }	

    public function getPorCodigo($codigo) {
        $uss = $this->fetchAll(" codigo = '{$codigo}' ");
        if ($uss->count()) {
            return $uss->current();
        }
        return false;
    }
	
}