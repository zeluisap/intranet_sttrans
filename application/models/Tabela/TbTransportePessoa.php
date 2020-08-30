<?php
class TbTransportePessoa extends Escola_Tabela {
	protected $_name = "transporte_pessoa";
	protected $_rowClass = "TransportePessoa";
	protected $_referenceMap = array("Pessoa" => array("columns" => array("id_pessoa"),
												   "refTableClass" => "TbPessoa",
												   "refColumns" => array("id_pessoa")),
                                     "TransportePessoaTipo" => array("columns" => array("id_transporte_pessoa_tipo"),
												   "refTableClass" => "TbTransportePessoaTipo",
												   "refColumns" => array("id_transporte_pessoa_tipo")),
                                     "TransportePessoaStatus" => array("columns" => array("id_transporte_pessoa_status"),
												   "refTableClass" => "TbTransportePessoaStatus",
												   "refColumns" => array("id_transporte_pessoa_status")),
                                     "Transporte" => array("columns" => array("id_transporte"),
												   "refTableClass" => "TbTransporte",
												   "refColumns" => array("id_transporte")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_transporte"]) && $dados["id_transporte"]) {
            $sql->where("id_transporte = {$dados["id_transporte"]}");
        }
        if (isset($dados["id_transporte_pessoa_tipo"]) && $dados["id_transporte_pessoa_tipo"]) {
            $sql->where("id_transporte_pessoa_tipo = {$dados["id_transporte_pessoa_tipo"]}");
        }
        if (isset($dados["id_pessoa"]) && $dados["id_pessoa"]) {
            $sql->where("id_pessoa = {$dados["id_pessoa"]}");
        }
        $sql->order("id_transporte_pessoa_tipo");
        $sql->order("id_transporte");
        return $sql;
    }
}