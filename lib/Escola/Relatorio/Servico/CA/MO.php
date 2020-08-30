<?php
class Escola_Relatorio_Servico_CA_MO extends Escola_Relatorio_Servico_CA
{
    public function getFilhos()
    {
        return [];
    }

    public function enabled()
    {
        if (!parent::enabled()) {
            return false;
        }

        if (!$this->transporte_grupo) {
            return false;
        }

        if (!$this->tp) {
            return false;
        }

        if (!$this->tp->auxiliar()) {
            return false;
        }

        return true;
    }

    public function set_registro($registro)
    {
        parent::set_registro($registro);

        if (!(isset($this->tp_pessoa) && $this->tp_pessoa)) {
            return;
        }

        if (!$this->tp_pessoa->pf()) {
            return;
        }

        if (!(isset($this->tp_pessoa_pf) && $this->tp_pessoa_pf)) {
            return;
        }

        $this->motorista = $this->tp_pessoa_pf->pegaMotorista();
    }

    public function getCarteiraCodigo()
    {
        return $this->registro->codigo;
    }

    public function getCarteiraAno()
    {
        return $this->registro->ano_referencia;
    }

    public function getDataValidade()
    {
        return $this->registro->data_validade;
    }

    public function getFilename()
    {
        return "carteira_motorista_auxiliar";
    }

    public function getNomenclaturaLicenca()
    {
        return "LICENÇA DE CONDUTOR";
    }

    public function getMatricula()
    {

        if (!$this->motorista) {
            return "";
        }

        $mat = $this->motorista->matricula;

        if ($mat) {
            return $mat;
        }

        return parent::getMatricula();
    }

    public function getConcessaoCodigo()
    {
        return $this->transporte->codigo;
    }

    public function getTipo()
    {
        return "AUXILIAR";
    }
}
