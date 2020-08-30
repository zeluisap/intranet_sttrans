<?php
class TbLogOperacao extends Escola_Tabela_Log {
	protected $_name = "log_operacao";
	protected $_rowClass = "LogOperacao";
	protected $_dependentTables = array("TbLog");
	
	public function init() {
            parent::init();
		$rows = $this->fetchAll();
		if (!count($rows)) {
			$dados = array();
			$dados[] = array("descricao" => "LOGIN", "chave" => "LOG");
			$dados[] = array("descricao" => "INSERIR", "chave" => "INS");
			$dados[] = array("descricao" => "ALTERAR", "chave" => "ALT");
			$dados[] = array("descricao" => "EXCLUIR", "chave" => "EXC");
			foreach ($dados as $dado) {
				$row = $this->createRow();
				$row->setFromArray($dado);
				$row->save();
			}
		}
	}
	
	public function getPorChave($chave) {
		$select = $this->select();
		$select->where("chave = '{$chave}'");
		$row = $this->fetchRow($select);
		if ($row) {
			return $row;
		}
		return false;
	}
}