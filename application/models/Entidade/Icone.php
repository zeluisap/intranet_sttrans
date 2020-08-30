<?php
class Icone extends Escola_Entidade {
	public function toString() {
		return $this->descricao;
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->id_icone_tipo)) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}'");
		if ($rg && count($rg)) {
			$msgs[] = "ÍCONE JÁ CADASTRADO PARA ESTE MUNICÍPIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
}