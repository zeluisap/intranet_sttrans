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

        $dataVencimento = $ss->data_vencimento;
        if (!$dataVencimento) {
            return false;
        }

        $dtVencimento = new Zend_Date($dataVencimento);
        $dtHoje = new Zend_Date();

        if ($dtHoje->isEarlier($dtVencimento)) {
            return false;
        }

        $diff = $dtHoje->sub($dtVencimento);
        $days = $diff->toString(Zend_Date::DAY);

        if (!$days) {
            return false;
        }

        $quant = floor(30 / $days);
        if (!$quant) {
            return false;
        }
    }

    public function calcular($ss)
    {
    }
}
