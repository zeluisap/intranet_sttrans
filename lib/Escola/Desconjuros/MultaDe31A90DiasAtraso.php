<?php

class Escola_Desconjuros_MultaDe31A90DiasAtraso implements Escola_Desconjuros
{

    public function getTipo()
    {
        return "multa";
    }

    public function getDescricao()
    {
        return "Multa de 31 a 90 dias de atraso.";
    }

    public function validar($ss)
    {
        if (!$ss) {
            return false;
        }

        $dataVencimento = $ss->data_vencimento;
        if (!$dataVencimento) {
            return false;
        }

        $dtVencimento = new Zend_Date($dataVencimento);
        $dtHoje = new Zend_Date();

        if ($dtHoje->isEarlier($dtVencimento)) {
            return false;
        }

        $diff = $dtHoje->sub($dtVencimento)->toValue();
        $days = ceil($diff / 60 / 60 / 24) + 1;

        if (!$days) {
            return false;
        }

        return (($days > 30) && ($days <= 90));
    }

    public function calcular($ss)
    {
        $valor = $ss->pega_valor();
        if (!$valor) {
            return null;
        }

        $vlr = $valor->valor;
        if (!$vlr) {
            return null;
        }

        if (!$this->validar($ss)) {
            return null;
        }

        $percentual = 20;

        return [
            "tipo" => $this->getTipo(),
            "descricao" => $this->getDescricao(),
            "valor" => $vlr * $percentual / 100
        ];
    }
}
