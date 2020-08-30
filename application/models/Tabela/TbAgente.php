<?php
class TbAgente extends Escola_Tabela {
	protected $_name = "agente";
	protected $_rowClass = "Agente";
	protected $_referenceMap = array("Funcionario" => array("columns" => array("id_funcionario"),
												   "refTableClass" => "TbFuncionario",
												   "refColumns" => array("id_funcionario")));	    
	protected $_dependentTables = array("TbAutoInfracao");
	
    public function getSql($dados = array()) {
        $f = $pf = false;
        $sql = $this->select();
        $sql->from(array("a" => "agente"));
        if (isset($dados["filtro_codigo"]) && $dados["filtro_codigo"]) {
            $sql->where("a.codigo = '{$dados["filtro_codigo"]}'");
        }
        if (isset($dados["filtro_id_funcionario"]) && $dados["filtro_id_funcionario"]) {
            $sql->where("a.id_funcionario = {$dados["filtro_id_funcionario"]}");
        }
        if (isset($dados["filtro_matricula"]) && $dados["filtro_matricula"]) {
            $f = true;
            $sql->join(array("f" => "funcionario"), "a.id_funcionario = f.id_funcionario", array());
            $sql->where("f.matricula = '{$dados["filtro_matricula"]}'");
        }
        if (isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
            $pf = true;
            $dados["filtro_cpf"] = Escola_Util::limpaNumero($dados["filtro_cpf"]);
            if (!$f) {
                $sql->join(array("f" => "funcionario"), "a.id_funcionario = f.id_funcionario", array());
            }
            $sql->join(array("pf" => "pessoa_fisica"), "f.id_pessoa_fisica = pf.id_pessoa_fisica", array());
            $sql->where("pf.cpf = '{$dados["filtro_cpf"]}'");
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            if (!$pf) {
                $pf = true;
                if (!$f) {
                    $sql->join(array("f" => "funcionario"), "a.id_funcionario = f.id_funcionario", array());
                    $f = true;
                }
                $sql->join(array("pf" => "pessoa_fisica"), "f.id_pessoa_fisica = pf.id_pessoa_fisica", array());
            }
            $sql->where("pf.nome like '%{$dados["filtro_nome"]}%'");
        }
		$sql->order("codigo"); 
        return $sql;
    }
	
	public function getPorCodigo($codigo) {
		$uss = $this->fetchAll(" codigo = {$codigo} ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
}