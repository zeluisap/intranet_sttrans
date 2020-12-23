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
    }

    public function calcular($ss)
    {
    }
}
