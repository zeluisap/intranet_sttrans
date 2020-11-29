<?php

/**
 * carteira base de permissionário
 * o permissionário só está disponível para moto-taxi
 */
class Escola_Relatorio_Servico_CA_BaseTransporteMotorista extends Escola_Relatorio_Servico_CA_BaseAutorizatario
{
    public function getFilhos()
    {
        return [];
    }

    public function enabled()
    {

        if ($this->tp && $this->tp->proprietario()) {
            return false;
        }

        return true;
    }

    public function getFilename()
    {
        return "carteira_transporte_motorista";
    }

    public function getTipo()
    {
        return "AUXILIAR";
    }
}
