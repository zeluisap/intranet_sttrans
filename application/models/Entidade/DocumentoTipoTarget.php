<?php
class DocumentoTipoTarget extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function eAdministrativo() {
        return ($this->chave == "A");
    }
    
    public function normal() {
        return ($this->chave == "D");
    }
    
    public function pessoal() {
        return ($this->chave == "P");
    }
    
    public function administrativo() {
    	return $this->eAdministrativo();
    }
}