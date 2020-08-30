<?php
class Escola_Retorno {
    protected $_arquivo;
    protected $_linhas;
    
    public function set_arquivo($arquivo) {
        $this->_arquivo = $arquivo;
    }
    
    public function get_arquivo() {
        return $this->_arquivo;
    }
    
    public function set_linhas($linhas) {
        $this->_linhas = $linhas;
    }
    
    public function get_linhas() {
        return $this->_linhas;
    }
    
    public function processar_arquivo($arquivo) {
        $this->set_arquivo($arquivo);
        if ($this->_arquivo) {
            $filename = $this->_arquivo->pegaNomeCompleto();
            $this->set_linhas(Escola_Util::carregaArquivo($filename));
        }
    }
}