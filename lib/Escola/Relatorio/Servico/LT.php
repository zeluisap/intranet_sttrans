<?php
class Escola_Relatorio_Servico_LT extends Escola_Relatorio_Servico_CA
{
    public function getFilhos()
    {
        // $filhos = parent::getFilhos();
        // return array_merge(["PJ"], $filhos);
        return ["Escola_Relatorio_Servico_LT_PJ"];
    }

    public function getFilename()
    {
        return "licenca";
    }

    public function getLicenca()
    {
        return $this->registro;
    }

    public function getNomeLicenca()
    {
        return "AUTORIZACAO DE TRÁFEGO";
    }

    public function getTituloLicenca()
    {
        $txt_licenca_numero = $this->getCarteiraCodigo();
        $txt_licenca_ano = $this->getCarteiraAno();
        $txt_nome_licenca = $this->getNomeLicenca();

        return $txt_nome_licenca . " No: " . $txt_licenca_numero . " / " . $txt_licenca_ano;
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
