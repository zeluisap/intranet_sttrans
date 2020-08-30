<?php
class TbVeiculo extends Escola_Tabela {
	protected $_name = "veiculo";
	protected $_rowClass = "Veiculo";
    protected $_dependentTables = array("TbTransporteVeiculo", "TbVeiculoRetido");
	protected $_referenceMap = array("Uf" => array("columns" => array("id_uf"),
												   "refTableClass" => "TbUf",
												   "refColumns" => array("id_uf")),
                                     "Combustivel" => array("columns" => array("id_combustivel"),
												   "refTableClass" => "TbCombustivel",
												   "refColumns" => array("id_combustivel")),
                                     "Cor" => array("columns" => array("id_cor"),
												   "refTableClass" => "TbCor",
												   "refColumns" => array("id_cor")),
                                     "Fabricante" => array("columns" => array("id_fabricante"),
												   "refTableClass" => "TbFabricante",
												   "refColumns" => array("id_fabricante")),
                                     "VeiculoTipo" => array("columns" => array("id_veiculo_tipo"),
												   "refTableClass" => "TbVeiculoTipo",
												   "refColumns" => array("id_veiculo_tipo")),
                                     "Municipio" => array("columns" => array("id_municipio"),
												   "refTableClass" => "TbMunicipio",
												   "refColumns" => array("id_municipio")),
                                     "VeiculoCategoria" => array("columns" => array("id_veiculo_categoria"),
												   "refTableClass" => "TbVeiculoCategoria",
												   "refColumns" => array("id_veiculo_categoria")),
									"VeiculoEspecie" => array("columns" => array("id_veiculo_especie"),
											"refTableClass" => "TbVeiculoEspecie",
											"refColumns" => array("id_veiculo_especie")),
									"Pessoa" => array("columns" => array("proprietario_id_pessoa"),
												   "refTableClass" => "TbPessoa",
												   "refColumns" => array("id_pessoa")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("v" => "veiculo"));
        if (isset($dados["filtro_placa"]) && $dados["filtro_placa"]) {
            $filter = new Zend_Filter_Alnum();
            $dados["filtro_placa"] = $filter->filter($dados["filtro_placa"]);
            $sql->where("v.placa = '{$dados["filtro_placa"]}'");
        }
        if (isset($dados["filtro_chassi"]) && $dados["filtro_chassi"]) {
            $sql->where("v.chassi = '{$dados["filtro_chassi"]}'");
        }
        if (isset($dados["filtro_id_fabricante"]) && $dados["filtro_id_fabricante"]) {
            $sql->where("v.id_fabricante = {$dados["filtro_id_fabricante"]}");
        }
        if (isset($dados["filtro_id_veiculo_tipo"]) && $dados["filtro_id_veiculo_tipo"]) {
            $sql->where("v.id_veiculo_tipo = {$dados["filtro_id_veiculo_tipo"]}");
        }
        if (isset($dados["filtro_proprietario"]) && $dados["filtro_proprietario"]) {
            $db = Zend_Registry::get("db");
            $sql_pf = $db->select();
            $sql_pf->from(array("pf" => "pessoa_fisica"), array("id_pessoa"));
            $sql_pf->where("pf.nome like '%{$dados["filtro_proprietario"]}%'");
            $sql_pj = $db->select();
            $sql_pj->from(array("pj" => "pessoa_juridica"), array("id_pessoa"));
            $sql_pj->where("pj.razao_social like '%{$dados["filtro_proprietario"]}%'");
            $sql->Where("(v.proprietario_id_pessoa in ({$sql_pf}) or v.proprietario_id_pessoa in ({$sql_pj}))");
        }
        return $sql;
    }
	
    public function getPorChassi($chassi) {
        if ($chassi) {
            $rs = $this->fetchAll("chassi = '{$chassi}'");
            if ($rs && count($rs)) {
                return $rs->current();
            }
        }
        return false;
    }
	
    public function getPorPlaca($placa) {
        if ($placa) {
            $rs = $this->fetchAll("placa = '{$placa}'");
            if ($rs && count($rs)) {
                return $rs->current();
            }
        }
        return false;
    }
}