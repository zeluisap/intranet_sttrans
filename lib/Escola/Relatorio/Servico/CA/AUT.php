<?php
class Escola_Relatorio_Servico_CA_AUT extends Escola_Relatorio_Servico_CA
{

    public function getFilhos()
    {
        return [];
    }

    public function setTransporteVeiculo($tv)
    {
        parent::setTransporteVeiculo($tv);
        if (!$tv) {
            $this->licenca_ativa = null;
            return;
        }

        if (!$this->licenca_ativa) {
            $licencas = $tv->pegaLicencaAtiva($this->getLicencaCodigo());
            if (!($licencas && count($licencas))) {
                $this->licenca_ativa = null;
                return;
            }
            $this->licenca_ativa = $licencas[0];
        }
    }

    public function validarEmitir()
    {
        $erros = parent::validarEmitir();

        if (Escola_Util::isResultado($erros)) {
            return $erros;
        }

        if (!(isset($this->licenca_ativa) && $this->licenca_ativa)) {
            return ["Nenhum Licença Ativa Detectada!"];
        }

        return null;
    }
}
