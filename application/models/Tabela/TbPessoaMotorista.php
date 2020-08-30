<?php
class TbPessoaMotorista extends Escola_Tabela {
	protected $_name = "pessoa_motorista";
	protected $_rowClass = "PessoaMotorista";
	protected $_referenceMap = array("PessoaFisica" => array("columns" => array("id_pessoa_fisica"),
												   "refTableClass" => "TbPessoaFisica",
												   "refColumns" => array("id_pessoa_fisica")),
                                     "CnhCategoria" => array("columns" => array("id_cnh_categoria"),
												   "refTableClass" => "TbCnhCategoria",
												   "refColumns" => array("id_cnh_categoria")),
                                     "Uf" => array("columns" => array("id_uf"),
												   "refTableClass" => "TbUf",
												   "refColumns" => array("id_uf")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("pm" => "pessoa_motorista"));
        $sql->join(array("pf" => "pessoa_fisica"), "pm.id_pessoa_fisica = pf.id_pessoa_fisica", array());
        if (isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
            $dados["filtro_cpf"] = Escola_Util::limparNumero($dados["filtro_cpf"]);
            $sql->where("pf.cpf = '{$dados["filtro_cpf"]}'");
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            $sql->where("pf.nome like '%{$dados["filtro_nome"]}%'");
        }
        if (isset($dados["filtro_id_cnh_categoria"]) && $dados["filtro_id_cnh_categoria"]) {
            $sql->where("pm.id_cnh_categoria = '{$dados["filtro_id_cnh_categoria"]}'");
        }
        $sql->order("pf.nome");
        return $sql;
    }
}