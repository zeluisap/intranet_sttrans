<?php
class Escola_Relatorio_Servico_CA_CP extends Escola_Relatorio_Servico_CA_MOT
{

    public function getFilhos()
    {
        return [];
    }

    public function getPessoaFisica()
    {
        return $this->tp_pessoa_pf;
    }

    public function enabled()
    {
        // if (!parent::enabled()) {
        //     return false;
        // }

        if (!$this->transporte_grupo) {
            return false;
        }

        // if (!$this->transporte_grupo->moto_taxi()) {
        //     return false;
        // }

        if (!$this->tp) {
            return false;
        }

        if (!$this->tp->proprietario()) {
            return false;
        }

        return true;
    }

    public function validarEmitir()
    {

        if (!(isset($this->registro) && $this->registro)) {
            return ["Solicitação de Serviço não Definida!"];
        }

        if (!$this->registro->pessoa()) {
            return ["Serviço não equivalente com os dados!"];
        }

        if (!$this->tp) {
            return ["Nenhum registro de pessoa vinculado ao serviço!"];
        }

        if (!$this->tp_pessoa_pf) {
            return ["Nenhuma Pessoa Física vinculada ao serviço!"];
        }

        return null;
    }

    public function set_registro($registro)
    {
        parent::set_registro($registro);

        if (!$registro->pessoa()) {
            return;
        }

        $pessoa = $this->registro->pegaReferencia();
        $this->setPessoa($pessoa, "tp");
    }

    public function setPessoa($objeto, $prefixo = "proprietario")
    {
        parent::setPessoa($objeto, $prefixo);

        if (!$this->tp_pessoa_pf) {
            return;
        }

        $this->motorista = $this->tp_pessoa_pf->pegaPessoaMotorista();
    }

    public function getFilename()
    {
        return "carteira_de_permissionario";
    }

    public function getTipo()
    {

        return "PERMISSIONÁRIO";
    }

    public function getMatricula()
    {
        $transporte = $this->getTransporte();
        if (!$transporte) {
            return "--";
        }

        $codigo = $transporte->mostrar_codigo();
        if (!$codigo) {
            return "--";
        }

        return $codigo;
    }
}
