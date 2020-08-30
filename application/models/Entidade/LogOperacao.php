<?php
class LogOperacao extends Zend_Db_Table_Row_Abstract {
	
	public function getErrors() {
		$errors = array();
		if (empty($this->chave) || empty($this->descricao)) {
			$errors[] = "VERIFIQUE CAMPOS OBRIGATÓRIOS!";
		} else {
			$sql = "select *
					from log_operacao
					where (chave = '$this->chave')
					and (id_log_operacao <> $this->id_log_operacao)";
			$select = $this->select();
			$select->where("chave = '$this->chave'");
			$select->where("id_log_operacao <> $this->id_log_operacao");
			$rows = $this->fetchAll($select);
			if (count($rows)) {
				$errors[] = "JÁ CADASTRADO!";
			}
		}
		if (count($errors)) {
			return $errors;
		}
		return false;
	}
	
	public function toString() {
		return $this->descricao;
	}
}