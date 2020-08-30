<?php

class Escola_Relatorio_Servico_AMCP extends Escola_Relatorio_Servico_Autorizacao
{

    public function getFilename()
    {
        return "autorizacao_mudanca_categoria_particular";
    }

    public function getServicoAFazer()
    {
        return "MUDANÇA DE CATEGORIA PARA PARTICULAR";
    }

    public function getOrgao()
    {
        return "DETRAN-AP";
    }
}
