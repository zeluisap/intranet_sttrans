<?php
class MenuTipo extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function interno() {
        return ($this->chave == 'I');
    }
    
    public function info() {
        return ($this->chave == 'N');
    }
    
    public function externo() {
        return ($this->chave == 'E');
    }
}