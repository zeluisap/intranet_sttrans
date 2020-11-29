<?php

/**
 * carteira base de permissionário
 * o permissionário só está disponível para moto-taxi
 */
class Escola_Relatorio_Servico_CA_BasePermissionario extends Escola_Relatorio_Servico_CA_BaseAutorizatario
{
    public function getFilhos()
    {
        return [];
    }

    public function enabled()
    {
        if (!($this->transporte_grupo && $this->transporte_grupo->moto_taxi())) {
            return false;
        }

        if (!($this->tp && $this->tp->proprietario())) {
            return false;
        }

        return true;
    }

    public function getFilename()
    {
        return "carteira_permissionario";
    }

    public function getTipo()
    {
        return "PERMISSIONÁRIO";
    }
}
