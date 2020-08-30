<?php

class Escola_Relatorio_Servico_AUTRETAX extends Escola_Relatorio_Servico_Autorizacao
{

    public function getFilename()
    {
        return "autorizacao_retirada_taximetro";
    }

    public function getServicoAFazer()
    {
        return "RETIRADA DO TAXÍMETRO";
    }

    public function getOrgao()
    {
        return "INMETRO-AP";
    }
}
