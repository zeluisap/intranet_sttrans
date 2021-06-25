<?php

class Escola_Desconjuros_JurosSimples1PorcentoAoMes implements Escola_Desconjuros
{

    public function getTipo()
    {
        return "juros";
    }

    public function getDescricao()
    {
        return "Juros simples 1% ao mês.";
    }

    public function validar($ss)
    {
        if (!$ss) {
            return false;
        }

        $dataVencimento = TbDesconjuros::pegaDataVencimento($ss);
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

        if ($days < 30) {
            return false;
        }

        $quant = floor($days / 30);
        if (!$quant) {
            return false;
        }

        return $quant;
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

        $mesesAtraso = $this->validar($ss);
        if (!$mesesAtraso) {
            return null;
        }

        return [
            "tipo" => $this->getTipo(),
            "descricao" => $this->getDescricao(),
            "valor" => $vlr * $mesesAtraso / 100
        ];
    }
}
