<?php
class DocumentoStatus extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function em_tramite() {
        return ($this->chave == "E");
    }
    
    public function aguardando() {
        return ($this->chave == "R");
    }

    public function arquivado() {
        return ($this->chave == "A");
    }

    public function processo() {
        return ($this->chave == "P");
    }

    public function vinculado() {
        return ($this->chave == "V");
    }
    
    public function possui_principal() {
    	return ($this->processo() || $this->vinculado());
    }
}