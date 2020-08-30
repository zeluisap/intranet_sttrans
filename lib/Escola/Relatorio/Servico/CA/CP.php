<?php
class Escola_Relatorio_Servico_CA_CP extends Escola_Relatorio_Servico_CA_AUT
{

    public function getFilhos()
    {
        return [];
    }

    public function enabled()
    {
        if (!parent::enabled()) {
            return false;
        }

        if (!$this->transporte_grupo) {
            return false;
        }

        if (!$this->transporte_grupo->moto_taxi()) {
            return false;
        }

        if (!$this->tp) {
            return false;
        }

        if (!$this->tp->proprietario()) {
            return false;
        }

        return true;
    }

    public function getFilename()
    {
        return "carteira_de_permissionario";
    }

    public function getTipo()
    {
        return "PERMISSIONÁRIO";
    }
}
