<?php

class Escola_Desconjuros_MultaDe91DiasEmDiante implements Escola_Desconjuros
{

    public function getTipo()
    {
        return "multa";
    }

    public function getDescricao()
    {
        return "Multa de 91 dias em diante.";
    }

    public function validar($ss)
    {
    }

    public function calcular($ss)
    {
    }
}
