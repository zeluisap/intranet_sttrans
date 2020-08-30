<?php
class MovimentacaoTipo extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function criacao() {
    	return ($this->chave == "I");
    }
    
    public function recebimento() {
    	return ($this->chave == "R");
    }
    
    public function envio() {
        return ($this->chave == "E");
    }
    
    public function cancelar_arquivar() {
        return ($this->chave == "C");
    }
}