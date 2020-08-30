<?php
class TbLotacao extends Escola_Tabela {
	protected $_name = "lotacao";
	protected $_rowClass = "Lotacao";
	protected $_referenceMap = array("LotacaoTipo" => array("columns" => array("id_lotacao_tipo"),
															 "refTableClass" => "TbLotacaoTipo",
															 "refColumns" => array("id_lotacao_tipo")),
									 "FuncionarioFuncao" => array("columns" => array("id_funcionario_funcao"),
															 "refTableClass" => "TbFuncionarioFuncao",
															 "refColumns" => array("id_funcionario_funcao")),
									 "Setor" => array("columns" => array("id_setor"),
															 "refTableClass" => "TbSetor",
															 "refColumns" => array("id_setor")),
									 "Funcionario" => array("columns" => array("id_funcionario"),
															 "refTableClass" => "TbFuncionario",
															 "refColumns" => array("id_funcionario")));
	
	public function listar($dados) {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("l" => "lotacao"), array("id_lotacao"));
		$sql->join(array("f" => "funcionario"), "l.id_funcionario = f.id_funcionario", array());
		$sql->join(array("p" => "pessoa_fisica"), "f.id_pessoa_fisica = p.id_pessoa_fisica", array());
		$sql->join(array("c" => "cargo"), "f.id_cargo = c.id_cargo", array());
		$sql->join(array("lt" => "lotacao_tipo"), "lt.id_lotacao_tipo = l.id_lotacao_tipo", array());
		if (isset($dados["filtro_id_setor"]) && $dados["filtro_id_setor"]) {
			$sql->where(" id_setor = {$dados["filtro_id_setor"]} ");
		}
		if (isset($dados["filtro_cargo"]) && $dados["filtro_cargo"]) {
			$sql->where(" c.descricao like '%{$dados["filtro_cargo"]}%' ");
		}
		if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
			$sql->where(" p.nome like '%{$dados["filtro_nome"]}%' ");
		}
		if (isset($dados["filtro_lotacao_tipo"]) && $dados["filtro_lotacao_tipo"]) {
			$sql->where(" lt.chave = '{$dados["filtro_lotacao_tipo"]}' ");
		}
		if (isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
			$filter = new Zend_Filter_Digits();
			$dados["filtro_cpf"] = $filter->filter($dados["filtro_cpf"]);
			$sql->where(" p.cpf = '{$dados["filtro_cpf"]}' ");
		}
		$sql->order("p.nome");
		$stmt = $db->query($sql);
		$rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
		if (count($rg)) {
			$items = array();
			foreach ($rg as $obj) {
				$items[] = $this->getPorId($obj->id_lotacao);
			}
			return $items;
		}
		return false;
	}
}