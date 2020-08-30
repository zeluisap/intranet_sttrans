<?php
class ComentarioStatus extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function permitido() {
        return ($this->chave == "P");
    }
    
    public function negado() {
        return ($this->chave == "N");
    }
}