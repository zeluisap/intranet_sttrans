<?php
class TbTelefone extends Escola_Tabela {
    protected $_name = "telefone";
    protected $_rowClass = "Telefone";
    protected $_referenceMap = array("TelefoneTipo" => array("columns" => array("id_telefone_tipo"),
                                                             "refTableClass" => "TbTelefoneTipo",
                                                             "refColumns" => array("id_telefone_tipo")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("t" => "telefone"));
        if (isset($dados["id_pessoa"]) && $dados["id_pessoa"]) {
            $sql->join(array("pr" => "pessoa_ref"), "t.id_telefone = pr.chave", array());
            $sql->where("pr.tipo = 'T'");
            $sql->where("pr.id_pessoa = {$dados["id_pessoa"]}");
        }
        if (isset($dados["telefone_tipo"]) && $dados["telefone_tipo"]) {
            $tb = new TbTelefoneTipo();
            $tt = $tb->getPorChave($dados["telefone_tipo"]);
            if ($tt) {
                $sql->where("t.id_telefone_tipo = {$tt->getId()}");
            }
        }
        $sql->order("ddd");
        $sql->order("numero");
        return $sql;
    }
}