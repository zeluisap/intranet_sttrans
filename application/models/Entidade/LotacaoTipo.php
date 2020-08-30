<?php
class LotacaoTipo extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function normal() {
        return ($this->chave == "N");
    }
}