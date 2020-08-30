<?php
class PessoaTipo extends Escola_Entidade {
    
    public function pf() {
        return ($this->chave == "PF");
    }
    
    public function pj() {
        return ($this->chave == "PJ");
    }
    
    public function toString() {
        return $this->descricao;
    }
}