<?php
class UsuarioSenha extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->status = "P";
        }
    }
    
	public function pendente() {
		return ($this->status == "P");
	}
}