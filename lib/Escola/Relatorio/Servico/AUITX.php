<?php

class Escola_Relatorio_Servico_AUITX extends Escola_Relatorio_Servico_Autorizacao
{

    public function getFilename()
    {
        return "autorizacao_instalacao_taximetro";
    }

    public function getServicoAFazer()
    {
        return "INSTALAÇÃO DO TAXÍMETRO";
    }

    public function getOrgao()
    {
        return "INMETRO-AP";
    }
}
