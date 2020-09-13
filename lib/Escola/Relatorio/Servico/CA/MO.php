<?php
class Escola_Relatorio_Servico_CA_MO extends Escola_Relatorio_Servico_CA_CP
{

    public function enabled()
    {
        // if (!parent::enabled()) {
        //     return false;
        // }

        if (!$this->transporte_grupo) {
            return false;
        }

        if (!$this->tp) {
            return false;
        }

        if (!$this->tp->auxiliar()) {
            return false;
        }

        return true;
    }

    public function getFilename()
    {
        return "carteira_motorista_auxiliar";
    }

    public function getTipo()
    {
        return "AUXILIAR";
    }
}
