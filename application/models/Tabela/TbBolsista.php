<?php
class TbBolsista extends Escola_Tabela {
	protected $_name = "bolsista";
	protected $_rowClass = "Bolsista";
	protected $_dependentTables = array("TbBolsistaPgto", "TbBolsistaOcorrencia");
	protected $_referenceMap = array("Vinculo" => array("columns" => array("id_vinculo"),
												   "refTableClass" => "TbVinculo",
												   "refColumns" => array("id_vinculo")),
                                     "PessoaFisica" => array("columns" => array("id_pessoa_fisica"),
												   "refTableClass" => "TbPessoaFisica",
												   "refColumns" => array("id_pessoa_fisica")),
                                     "BolsaTipo" => array("columns" => array("id_bolsa_tipo"),
												   "refTableClass" => "TbBolsaTipo",
												   "refColumns" => array("id_bolsa_tipo")),
                                     "BolsistaStatus" => array("columns" => array("id_bolsista_status"),
												   "refTableClass" => "TbBolsistaStatus",
												   "refColumns" => array("id_bolsista_status")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("b" => "bolsista"));
        $sql->join(array("bt" => "bolsa_tipo"), "b.id_bolsa_tipo = bt.id_bolsa_tipo", array());
        $sql->join(array("pf" => "pessoa_fisica"), "b.id_pessoa_fisica = pf.id_pessoa_fisica", array());
        if (isset($dados["id_vinculo"]) && $dados["id_vinculo"]) {
            $sql->where("b.id_vinculo = {$dados["id_vinculo"]}");
        }
        if (isset($dados["id_bolsa_tipo"]) && $dados["id_bolsa_tipo"]) {
            $sql->where("b.id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
        }
        if (isset($dados["cpf"]) && $dados["cpf"]) {
            $dados["cpf"] = Escola_Util::limparNumero($dados["cpf"]);
            $sql->where("pf.cpf = '{$dados["cpf"]}'");
        }
        if (isset($dados["id_bolsista_status"]) && $dados["id_bolsista_status"]) {
            $sql->where("b.id_bolsista_status = {$dados["id_bolsista_status"]}");
        }
		$sql->order("bt.descricao");
        $sql->order("pf.nome"); 
        return $sql;
    }
}