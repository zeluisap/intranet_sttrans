<?php
class ChamadoStatus extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function pendente() {
    	return ($this->chave == "P");
    }
    
    public function em_atendimento() {
    	return ($this->chave == "E");
    }
    
    public function atendido() {
    	return ($this->chave == "A");
    }
    
    public function finalizado() {
    	return ($this->chave == "F");
    }
}