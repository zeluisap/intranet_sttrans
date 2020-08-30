<?php
class TbInterdicao extends Escola_Tabela {
	protected $_name = "interdicao";
	protected $_rowClass = "Interdicao";
	protected $_referenceMap = array("Pessoa" => array("columns" => array("id_pessoa"),
												   "refTableClass" => "TbPessoa",
												   "refColumns" => array("id_pessoa")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("i" => "interdicao"));
        if (isset($dados["filtro_titulo"]) && $dados["filtro_titulo"]) {
            $sql->where("titulo like '%{$dados["filtro_titulo"]}%'");
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            $db = Zend_Registry::get("db");
            $sql_pf = $db->select();
            $sql_pf->from(array("pf" => "pessoa_fisica"), array("id_pessoa"));
            $sql_pf->where("pf.nome like '%{$dados["filtro_nome"]}%'");
            $sql_pj = $db->select();
            $sql_pj->from(array("pj" => "pessoa_juridica"), array("id_pessoa"));
            $sql_pj->where("pj.razao_social like '%{$dados["filtro_nome"]}%'");
            $sql->Where("(i.id_pessoa in ({$sql_pf}) or i.id_pessoa in ({$sql_pj}))");            
        }
        $sql->from(array("ss" => "servico_solicitacao"), array());
        $sql->where("ss.tipo = 'IN'");
        $sql->where("ss.chave = i.id_interdicao");
        if (isset($dados["filtro_id_servico_solicitacao_status"]) && $dados["filtro_id_servico_solicitacao_status"]) {
            $sql->where("ss.id_servico_solicitacao_status = {$dados["filtro_id_servico_solicitacao_status"]}");
        }
        if (isset($dados["filtro_id_pessoa_tipo"]) && $dados["filtro_id_pessoa_tipo"]) {
            $sql->join(array("p" => "pessoa"), "i.id_pessoa = p.id_pessoa", array());
            $sql->where("p.id_pessoa_tipo = {$dados["filtro_id_pessoa_tipo"]}");
            $pt = TbPessoaTipo::pegaPorId($dados["filtro_id_pessoa_tipo"]);
            if ($pt) {
                if ($pt->pf() && isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
                    $dados["filtro_cpf"] = Escola_Util::limpaNumero($dados["filtro_cpf"]);
                    $sql->join(array("pf1" => "pessoa_fisica"), "p.id_pessoa = pf1.id_pessoa", array());
                    $sql->where("pf1.cpf = '{$dados["filtro_cpf"]}'");
                } elseif ($pt->pj() && isset($dados["filtro_cnpj"]) && $dados["filtro_cnpj"]) {
                    $dados["filtro_cnpj"] = Escola_Util::limpaNumero($dados["filtro_cnpj"]);
                    $sql->join(array("pj1" => "pessoa_juridica"), "p.id_pessoa = pj1.id_pessoa", array());
                    $sql->where("pj1.cnpj = '{$dados["filtro_cnpj"]}'");
                }
            }
        }
        $sql->order("ss.ano_referencia desc");
        $sql->order("ss.codigo desc");
        return $sql;
    }
}