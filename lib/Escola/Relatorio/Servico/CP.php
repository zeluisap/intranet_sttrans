<?php

class Escola_Relatorio_Servico_CP extends Escola_Relatorio_Servico {

    public function __construct() {
        parent::__construct();
        $this->setFilename("relatorio_carteira_proprietario");
        $this->SetTopMargin(5);
    }

    public function header() {
        
    }

    public function pegaVeiculo() {
        
    }

    public function validarEmitir() {
        $p_errors = parent::validarEmitir();
        $errors = array();
        $transporte = $this->registro->pegaTransporte();
        if (!$transporte) {
            $errors[] = "NENHUM TRANSPORTE VINCULADO!";
        } else {
            $pessoa = false;
        }
        if ($p_errors) {
            $errors = array_merge($p_errors, $errors);
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }

    public function toPDF() {
        if (!$this->registro) {
            throw new Exception("Falha ao Executar Operação, Nenhuma Solicitação Vinculada!");
        }
        $transporte = $this->registro->pegaTransporte();
        if (!$transporte) {
            throw new Exception("Falha ao Executar Operação, Dados Inválidos!");
        }
        $tp = $this->registro->pegaReferencia();
        if ($tp) {
            if (!is_a($tp, "TransportePessoa")) {
                $tp = $transporte->pegaProprietario();
            }
        }
        if (!$tp) {
            throw new Exception("Falha ao Executar Operação, Proprietário não Localizado!");
        }
        
        $tp->toPDF($this->registro);
    }

}