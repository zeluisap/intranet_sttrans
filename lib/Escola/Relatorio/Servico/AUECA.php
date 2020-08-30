<?php

class Escola_Relatorio_Servico_AUECA extends Escola_Relatorio_Servico_Autorizacao
{

    public function getFilename()
    {
        return "autorizacao_primeiro_emplacamento_categoria_aluguel";
    }

    public function getServicoAFazer()
    {
        return "PRIMEIRO EMPLACAMENTO NA CATEGORIA ALUGUEL";
    }

    public function getOrgao()
    {
        return "DETRAN-AP";
    }
}
