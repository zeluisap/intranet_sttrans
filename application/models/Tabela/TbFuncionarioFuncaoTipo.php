<?php
class TbFuncionarioFuncaoTipo extends Escola_Tabela {
	protected $_name = "funcionario_funcao_tipo";
	protected $_rowClass = "FuncionarioFuncaoTipo";
	protected $_dependentTables = array("TbFuncionarioFuncao", "TbSetor");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
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
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("G" => "GERENTE",
						   "D" => "CHEFE DE DEPARTAMENTO",
						   "I" => "CHEFE DE DIVISÃO",
						   "S" => "CHEFE DE SETOR",
						   "C" => "CHEFE DE SEÃÃO");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}