<?php
class SetorTipo extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function interno() {
        return ($this->chave == "I");
    }
}