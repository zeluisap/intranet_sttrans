<?php
class TbPessoaJuridica extends Escola_Tabela {
	protected $_name = "pessoa_juridica";
	protected $_rowClass = "PessoaJuridica";
	protected $_dependentTables = array("TbSistema");	
	protected $_referenceMap = array("Pessoa" => array("columns" => array("id_pessoa"),
													   "refTableClass" => "TbPessoa",
													   "refColumns" => array("id_pessoa")));
    
    public function getSql($dados = array()) {
        $select = $this->select();
		if (isset($dados["filtro_cnpj"]) && Escola_Util::limparNumero($dados["filtro_cnpj"])) {
			$filter = new Zend_Filter_Digits();
			$dados["filtro_cnpj"] = $filter->filter($dados["filtro_cnpj"]);
			$select->where(" cnpj = '{$dados["filtro_cnpj"]}' ");
		}
		if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
			$select->where(" sigla = '{$dados["filtro_nome"]}' or razao_social like '%{$dados["filtro_nome"]}%' or nome_fantasia like '%{$dados["filtro_nome"]}%' ");
		}
		$select->order("nome_fantasia");
        return $select;
    }
    
    public function getPorCNPJ($cnpj) {
		$filtro = new Zend_Filter_Digits();
		$cnpj = $filtro->filter($cnpj);
		$rg = $this->fetchAll(" cnpj = '{$cnpj}' ");
		if (count($rg)) {
			return $rg->current();
		}
		return false;
	}
}