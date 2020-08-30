<?php
class DocumentoModo extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function circular() {
        return ($this->chave == "C");
    }
    
    public function normal() {
        return ($this->chave == "N");
    }
}