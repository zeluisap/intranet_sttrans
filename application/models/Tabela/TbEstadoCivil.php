<?php
class TbEstadoCivil extends Escola_Tabela {
	protected $_name = "estado_civil";
	protected $_rowClass = "EstadoCivil";
	protected $_dependentTables = array("PessoaFisiscas");
	
	public function init() {
		$rows = $this->fetchAll();
		if (!$rows->count()) {
			$dados = array("INDEFINIDO", "SOLTEIRO(A)", "CASADO(A)", "UNIÃO ESTÁVEL", "VIÚVO(A)", "SEPARADO(A)", "DIVORCIADO(A)");
			foreach ($dados as $k => $v) {
				$row = $this->createRow();
				$row->descricao = $v;
				$row->save();
			}
		}
	}
	
	public function listar() {
		$sql = $this->select();
		$sql->order("descricao");
		$rg = $this->fetchAll($sql);
		if (count($rg)) {
			return $rg;
		}
		return false;
	}
	
	public function getPorDescricao($descricao) {
		$ecs = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($ecs->count()) {
			return $ecs->current();
		}
		return false;
	}
}