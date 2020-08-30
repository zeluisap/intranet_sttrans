<?php
class TbPessoa extends Escola_Tabela {
	protected $_name = "pessoa";
	protected $_rowClass = "Pessoa";
	protected $_dependentTables = array("TbPessoaJuridica", "TbPessoaFisica", "TbPessoaRef", "TbTransportePessoa", "TbBoleto");	
	protected $_referenceMap = array("PessoaTipo" => array("columns" => array("id_pessoa_tipo"),
														   "refTableClass" => "TbPessoaTipo",
														   "refColumns" => array("id_pessoa_tipo")));
    
    public function getSql($dados = array()) {
        $pf1 = $pj1 = false;
        $sql = $this->select();
        $sql->from(array("p" => "pessoa"));
        if (isset($dados["filtro_id_pessoa_tipo"]) && $dados["filtro_id_pessoa_tipo"]) {
            $sql->where("id_pessoa_tipo = {$dados["filtro_id_pessoa_tipo"]}");
            $tb = new TbPessoaTipo();
            $pt = $tb->pegaPorId($dados["filtro_id_pessoa_tipo"]);
            if ($pt) {
                if ($pt->pf()) {
                    $pf1 = true;
                    $sql->join(array("pf1" => "pessoa_fisica"), "p.id_pessoa = pf1.id_pessoa", array());
                    $sql->order("pf1.nome");
                } elseif ($pt->pj()) {
                    $pj1 = true;
                    $sql->join(array("pj1" => "pessoa_juridica"), "p.id_pessoa = pj1.id_pessoa", array());
                    $sql->order("pj1.razao_social");
                }
            }
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            $db = Zend_Registry::get("db");
            $sql_pf = $db->select();
            $sql_pf->from(array("pf" => "pessoa_fisica"), array("id_pessoa"));
            $sql_pf->where("pf.nome like '%{$dados["filtro_nome"]}%'");
            $sql_pj = $db->select();
            $sql_pj->from(array("pj" => "pessoa_juridica"), array("id_pessoa"));
            $sql_pj->where("pj.razao_social like '%{$dados["filtro_nome"]}%'");
            $sql->Where("(p.id_pessoa in ({$sql_pf}) or p.id_pessoa in ({$sql_pj}))");
        }
        if (isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
            $dados["filtro_cpf"] = Escola_Util::limpaNumero($dados["filtro_cpf"]);
            if (!$pf1) {
                $sql->join(array("pf1" => "pessoa_fisica"), "p.id_pessoa = pf1.id_pessoa", array());
            }
            $sql->where("pf1.cpf = '{$dados["filtro_cpf"]}'");
        }
        if (isset($dados["filtro_cnpj"]) && $dados["filtro_cnpj"]) {
            $dados["filtro_cnpj"] = Escola_Util::limpaNumero($dados["filtro_cnpj"]);
            if (!$pj1) {
                $sql->join(array("pj1" => "pessoa_juridica"), "p.id_pessoa = pj1.id_pessoa", array());
            }
            $sql->where("pj1.cnpj = '{$dados["filtro_cnpj"]}'");
        }
        return $sql;
    }
}