<?php

abstract class Escola_Relatorio_Default {

    protected $dados;
    protected $relatorio = null;

    public function set_dados($dados) {
        $this->dados = $dados;
    }

    abstract public function toHTML();

    abstract public function toXLS();

    abstract public function toPDF();

    public function set_relatorio($relatorio) {
        $this->relatorio = $relatorio;
    }

    public function get_relatorio() {
        return $this->relatorio;
    }

    public function validarEmitir() {
        return false;
    }

}