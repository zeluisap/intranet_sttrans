<?php
class TbPessoaFisica extends Escola_Tabela {
	protected $_name = "pessoa_fisica";
	protected $_rowClass = "PessoaFisica";
        protected $_dependentTables = array("TbCredencial");
	protected $_referenceMap = array("Pessoa" => array("columns" => array("id_pessoa"),
													   "refTableClass" => "TbPessoa",
													   "refColumns" => array("id_pessoa")),
									 "Uf" => array("columns" => array("id_uf"),
												   "refTableClass" => "TbUf",
												   "refColumns" => array("id_uf")),
									 "EstadoCivil" => array("columns" => array("id_estado_civil"),
															"refTableClass" => "TbEstadoCivil",
															"refColumns" => array("id_estado_civil")),
									 "NascMunicipio" => array("columns" => array("nascimento_id_municipio"),
															"refTableClass" => "TbMunicipio",
															"refColumns" => array("id_municipio")));
	
	public function getPorCPF($cpf) {
		$filtro = new Zend_Filter_Digits();
		$cpf = $filtro->filter($cpf);
		$rg = $this->fetchAll(" cpf = '{$cpf}' ");
		if (count($rg)) {
			return $rg->current();
		}
		return false;
	}
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $digitos = new Zend_Filter_Digits();
		if (isset($dados["filtro_cpf"])) {
			$dados["filtro_cpf"] = $digitos->filter($dados["filtro_cpf"]);
			if ($dados["filtro_cpf"]) {
				$sql->where("cpf = '{$dados["filtro_cpf"]}'");
			}			
		}
		if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
			$sql->where("nome like '%{$dados["filtro_nome"]}%'");
		}
        $sql->order("nome");
        return $sql;
    }
}