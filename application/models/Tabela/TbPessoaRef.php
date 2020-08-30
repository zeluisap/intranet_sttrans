<?php
class TbPessoaRef extends Escola_Tabela {
	protected $_name = "pessoa_ref";
	protected $_rowClass = "PessoaRef";
	protected $_referenceMap = array("Pessoa" => array("columns" => array("id_pessoa"),
													   "refTableClass" => "TbPessoa",
													   "refColumns" => array("id_pessoa")));

    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_pessoa"]) && $dados["id_pessoa"]) {
            $sql->where("id_pessoa = {$dados["id_pessoa"]}");
        }
        if (isset($dados["tipo"]) && $dados["tipo"]) {
            $sql->where("tipo = '{$dados["tipo"]}'");
        }
        if (isset($dados["chave"]) && $dados["chave"]) {
            $sql->where("chave = {$dados["chave"]}");
        }
        $sql->order("id_pessoa");
        return $sql;
    }
}