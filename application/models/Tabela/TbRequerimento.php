<?php
class TbRequerimento extends Escola_Tabela
{
	protected $_name = "requerimento";
	protected $_rowClass = "Requerimento";
	protected $_dependentTables = array("TbRequerimentoItem");
	protected $_referenceMap = array("Pessoa" => array(
		"columns" => array("id_pessoa"),
		"refTableClass" => "TbPessoa",
		"refColumns" => array("id_pessoa")
	));

	public function getSql($dados = array())
	{
		$sql = $this->select();
		$sql->from(array("r" => "requerimento"));

		if (isset($dados["filtro_numero"]) && $dados["filtro_numero"]) {
			$sql->where("r.numero = ?", $dados["filtro_numero"]);
		}

		if (isset($dados["filtro_ano"]) && $dados["filtro_ano"]) {
			$sql->where("r.ano = ?", $dados["filtro_ano"]);
		}

		if (isset($dados["filtro_situacao"]) && $dados["filtro_situacao"]) {
			$sql->where("lower(r.situacao) = lower(?)", $dados["filtro_situacao"]);
		}

		if (isset($dados["filtro_data_criacao_inicio"]) && $dados["filtro_data_criacao_inicio"]) {
			try {
				$dt = DateTime::createFromFormat('d/m/Y', $dados["filtro_data_criacao_inicio"]);
				$sql->where("r.data_criacao >= ?", $dt->format('Y-m-d'));
			} catch (Exception $ex) {
				throw new Escola_Exception("Data criação início inválida.");
			}
		}

		if (isset($dados["filtro_data_criacao_fim"]) && $dados["filtro_data_criacao_fim"]) {
			try {
				$dt = DateTime::createFromFormat('d/m/Y', $dados["filtro_data_criacao_fim"]);
				$sql->where("r.data_criacao <= ?", $dt->format('Y-m-d'));
			} catch (Exception $ex) {
				throw new Escola_Exception("Data criação final inválida.");
			}
		}

		if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
			$sql->join(array("p" => "pessoa"), "r.id_pessoa = p.id_pessoa", []);
			$sql->join(array("pf" => "pessoa_fisica"), "p.id_pessoa = pf.id_pessoa", []);
			$sql->where("lower(pf.nome) like lower(?)", '%' . $dados["filtro_nome"] . '%');
		}

		$sql->order("r.ano");
		$sql->order("r.numero");
		return $sql;
	}
}
