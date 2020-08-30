<?php
class TbCredencial extends Escola_Tabela {
	protected $_name = "credencial";
	protected $_rowClass = "Credencial";
	protected $_dependentTables = array("TbCredencialOcorrencia");
	protected $_referenceMap = array("CredencialTipo" => array("columns" => array("id_credencial_tipo"),
                                                            "refTableClass" => "TbCredencialTipo",
                                                            "refColumns" => array("id_credencial_tipo")),
                                        "CredencialStatus" => array("columns" => array("id_credencial_status"),
                                                                   "refTableClass" => "TbCredencialStatus",
                                                                   "refColumns" => array("id_credencial_status")),
                                        "PessoaFisica" => array("columns" => array("id_pessoa_fisica"),
                                                                   "refTableClass" => "TbPessoaFisica",
                                                                   "refColumns" => array("id_pessoa_fisica")),	
                                        "PessoaFisicaResponsavel" => array("columns" => array("id_pessoa_fisica_responsavel"),
                                                                   "refTableClass" => "TbPessoaFisica",
                                                                   "refColumns" => array("id_pessoa_fisica")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("c" => "credencial"));
        $sql->join(array("pf" => "pessoa_fisica"), "c.id_pessoa_fisica = pf.id_pessoa_fisica", array());
        
        if (isset($dados["id_credencial_tipo"]) && $dados["id_credencial_tipo"]) {
            $sql->where("c.id_credencial_tipo = {$dados["id_credencial_tipo"]}");
        }
        if (isset($dados["id_credencial_status"]) && $dados["id_credencial_status"]) {
            $sql->where("c.id_credencial_status = {$dados["id_credencial_status"]}");
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            $sql->where("lower(pf.nome) like '%{$dados["filtro_nome"]}%'");
        }
        if (isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
            $filter = new Zend_Filter_Digits();
            $cpf = $filter->filter($dados["filtro_cpf"]);
            $sql->where("lower(pf.cpf) = '{$cpf}'");
        }
        
        $sql->order("credencial_data"); 
        $sql->order("credencial_hora");
        return $sql;
    }
    
    public static function geraNumero($id_ct, $ano) {
        if (!$ano) {
            return 1;
        }
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("c" => "credencial"), array("maximo" => "max(c.numero)"));
        $sql->join(array("ct" => "credencial_tipo"), "c.id_credencial_tipo = ct.id_credencial_tipo", array());
        $sql->where("ct.id_credencial_tipo = {$id_ct}");
        $sql->where("c.ano = {$ano}");
        $objs = $db->fetchAll($sql);
        if (!$objs || !count($objs)) {
            return 1;
        }
        
        $obj = current($objs);
        
        if (!isset($obj["maximo"])) {
            return 1;
        }
        
        return $obj["maximo"] + 1;
    }
}