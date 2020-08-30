<?php 
class Acao extends Escola_Entidade {
	
	public function save() {
		if ($this->principal()) {
			$modulo = $this->findParentRow("TbModulo");
			$acaos = $modulo->findDependentRowSet("TbAcao");
			if ($acaos) {
				foreach ($acaos as $acao) {
					if ($acao->getId() != $this->getId()) {
						$acao->principal = "N";
						$acao->save();
					}
				}
			}
		}
		parent::save();
		$tb = new TbGrupo();
		$adm = $tb->pegaAdministrador();
		if ($adm) {
			$adm->permitir($this);
		}
	}
	
	public function delete() {
		$rg = $this->findDependentRowSet("TbPermissao");
		foreach ($rg as $obj) {
			$obj->delete();
		}
		parent::delete();
	}
	
	public function principal() {
		return ($this->principal == "S");
	}
	
	public function mostrarPrincipal() {
		if ($this->principal == "S") {
			return "SIM";
		} else {
			return "NÃO";
		}
	}
	
	public function getErrors() {
		$errors = array();
		if (!trim($this->descricao)) {
			$errors[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		if (!trim($this->action)) {
			$errors[] = "CAMPO ACTION OBRIGATÓRIO!";
		}
		if (!trim($this->principal)) {
			$errors[] = "CAMPO PRINCIPAL OBRIGATÓRIO!";
		}
		$sql = $this->getTable()->select();
		$sql->where("id_modulo = " . $this->id_modulo);
		$sql->where("action = '" . $this->action . "'");
		$sql->where("id_acao <> " . $this->getId());
		$rg = $this->getTable()->fetchAll($sql);
		if (count($rg)) {
			$errors[] = "ACTION [" . $this->action . "] JÁ CADASTRADA PARA ESSE MÓDULO!";
		}
		if (count($errors)) {
			return $errors;
		}
		return false;
	}
}