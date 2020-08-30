<?php
class TbArquivoRef extends Escola_Tabela {
	protected $_name = "arquivo_ref";
	protected $_rowClass = "ArquivoRef";
	protected $_referenceMap = array("Arquivo" => array("columns" => array("id_arquivo"),
															 "refTableClass" => "TbArquivo",
															 "refColumns" => array("id_arquivo")));
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["tipo"]) && $dados["tipo"]) {
            $sql->where("tipo = '{$dados["tipo"]}'");
        }
        if (isset($dados["chave"]) && $dados["chave"]) {
            $sql->where("chave = {$dados["chave"]}");
        }
        if (isset($dados["id_arquivo"]) && $dados["id_arquivo"]) {
            $sql->where("id_arquivo = {$dados["id_arquivo"]}");
        }
        $sql->order("id_arquivo_ref");
        return $sql;
    }
}