<?php
class FuncionarioSituacao extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function ativo() {
    	return ($this->chave == "A");
    }
}