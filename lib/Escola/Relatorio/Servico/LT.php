<?php
class Escola_Relatorio_Servico_LT extends Escola_Relatorio_Servico_CA
{
    public function getFilhos()
    {
        $filhos = parent::getFilhos();
        return array_merge(["PJ"], $filhos);
    }

    public function getFilename()
    {
        return "licenca";
    }

    public function getLicenca()
    {
        return $this->registro;
    }

    public function setTransporte($transporte)
    {
        parent::setTransporte($transporte);
        $tp = $transporte->pegaProprietario();
        $this->setPessoa($tp, "tp");

        $licenca = $this->getLicenca();
        if (!$licenca) {
            throw new Escola_Exception("Falha ao Localizar Serviço!");
        }

        $tv = $licenca->pegaReferencia();
        $this->setTransporteVeiculo($tv);
    }
}
