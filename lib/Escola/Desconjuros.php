<?php

interface Escola_Desconjuros
{

    public function getTipo();
    public function getDescricao();
    public function validar($ss);
    public function calcular($ss);
}
