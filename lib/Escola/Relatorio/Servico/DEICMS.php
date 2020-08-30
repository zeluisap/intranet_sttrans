<?php

class Escola_Relatorio_Servico_DEICMS extends Escola_Relatorio_Servico_Declaracao
{

    public function getFilename()
    {
        return "declaracao_isencao_icms";
    }

    public function getAssunto()
    {
        return "Isenção de I.C.M.S.";
    }

    public function getAutoridade()
    {
        return "Secretário da Sefaz";
    }
}
