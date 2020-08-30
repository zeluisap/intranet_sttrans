<?php

class Escola_Relatorio_Servico_AMCA extends Escola_Relatorio_Servico_Autorizacao
{

    public function getFilename()
    {
        return "autorizacao_mudanca_categoria_aluguel";
    }

    public function getServicoAFazer()
    {
        return "MUDANÇA DE CATEGORIA PARA ALUGUEL";
    }

    public function getOrgao()
    {
        return "DETRAN-AP";
    }
}
