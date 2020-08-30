<?php

class Escola_Relatorio_Servico_APCA extends Escola_Relatorio_Servico_Autorizacao
{

    public function getFilename()
    {
        return "autorizacao_permanencia_categoria_aluguel";
    }

    public function getServicoAFazer()
    {
        return "PERMANÊNCIA NA CATEGORIA ALUGUEL";
    }

    public function getOrgao()
    {
        return "DETRAN-AP";
    }
}
