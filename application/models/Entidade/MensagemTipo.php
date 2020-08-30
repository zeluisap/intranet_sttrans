<?php
class MensagemTipo extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setor_subordinado() {
        return ($this->chave == "S");
    }
    
    public function setor_atual() {
        return ($this->chave == "A");
    }
    
    public function pessoal() {
        return ($this->chave == "P");
    }
    
    public function todos() {
        return ($this->chave == "T");
    }
    
    public function setor() {
        return ($this->chave == "E");
    }
}