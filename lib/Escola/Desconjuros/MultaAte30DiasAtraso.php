<?php

class Escola_Desconjuros_MultaAte30DiasAtraso implements Escola_Desconjuros
{

    public function getTipo()
    {
        return "multa";
    }

    public function getDescricao()
    {
        return "Multa até 30 dias de atraso.";
    }

    public function validar($ss)
    {
    }

    public function calcular($ss)
    {
    }
}
