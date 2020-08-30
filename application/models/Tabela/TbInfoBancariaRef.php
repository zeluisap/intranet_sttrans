<?php
class TbInfoBancariaRef extends Escola_Tabela {
	protected $_name = "info_bancaria_ref";
	protected $_rowClass = "InfoBancariaRef";
	protected $_referenceMap = array("InfoBancaria" => array("columns" => array("id_info_bancaria"),
												   "refTableClass" => "TbInfoBancaria",
												   "refColumns" => array("id_info_bancaria")));	
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("ibr" => "info_bancaria_ref"));
        $sql->join(array("ib" => "info_bancaria"), "ibr.id_info_bancaria = ib.id_info_bancaria", array());
        $sql->join(array("b" => "banco"), "ib.id_banco = b.id_banco", array());
        if (isset($dados["tipo"]) && $dados["tipo"]) {
            $sql->where("tipo = '{$dados["tipo"]}'");
        }
        if (isset($dados["chave"]) && $dados["chave"]) {
            $sql->where("chave = {$dados["chave"]}");
        }
        if (isset($dados["id_info_bancaria"]) && $dados["id_info_bancaria"]) {
            $sql->where("id_info_bancaria = {$dados["id_info_bancaria"]}");
        }
		$sql->order("b.codigo"); 
        $sql->order("ib.agencia");
        $sql->order("conta");
        return $sql;
    }
    
}