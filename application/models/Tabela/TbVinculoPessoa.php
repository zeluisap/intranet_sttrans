<?php
class TbVinculoPessoa extends Escola_Tabela {
	protected $_name = "vinculo_pessoa";
	protected $_rowClass = "VinculoPessoa";
	protected $_referenceMap = array("VinculoPessoaTipo" => array("columns" => array("id_vinculo_pessoa_tipo"),
                                                                                        "refTableClass" => "TbVinculoPessoaTipo",
                                                                                        "refColumns" => array("id_vinculo_pessoa_tipo")),
                                         "Vinculo" => array("columns" => array("id_vinculo"),
                                                                                        "refTableClass" => "TbVinculo",
                                                                                        "refColumns" => array("id_vinculo")),
                                         "PessoaFisica" => array("columns" => array("id_pessoa_fisica"),
                                                                                        "refTableClass" => "TbPessoaFisica",
                                                                                        "refColumns" => array("id_pessoa_fisica")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_vinculo"]) && $dados["id_vinculo"]) {
            $sql->where("id_vinculo = {$dados["id_vinculo"]}");
        }
        if (isset($dados["id_pessoa_fisica"]) && $dados["id_pessoa_fisica"]) {
            $sql->where("id_pessoa_fisica = {$dados["id_pessoa_fisica"]}");
        }
        if (isset($dados["vinculo_pessoa_tipo"]) && $dados["vinculo_pessoa_tipo"]) {
            $tb = new TbVinculoPessoaTipo();
            $vpt = $tb->getPorChave("CO");
            if ($vpt) {
                $dados["id_vinculo_pessoa_tipo"] = $vpt->getId();
            }
        }
        if (isset($dados["id_vinculo_pessoa_tipo"]) && $dados["id_vinculo_pessoa_tipo"]) {
            $sql->where("id_vinculo_pessoa_tipo = {$dados["id_vinculo_pessoa_tipo"]}");
        }
        $sql->order("id_vinculo_pessoa");
        return $sql;
    }
}