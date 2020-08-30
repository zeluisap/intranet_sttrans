<?php

class Escola_Relatorio_Servico_DEIPI extends Escola_Relatorio_Servico_Declaracao
{
    public function getFilename()
    {
        return "declaracao_isencao_ipi";
    }

    public function getAssunto()
    {
        return "Isenção de I.P.I.";
    }

    public function getAutoridade()
    {
        return "Delegado da Receita Federal";
    }
}
