<?php

/**
 * carteira base de permissionário
 * o permissionário só está disponível para moto-taxi
 */
class Escola_Relatorio_Servico_CA_BaseMotorista extends Escola_Relatorio_Servico_CA_BaseAutorizatario
{
    public function getFilhos()
    {
        return [];
    }

    public function enabled()
    {

        if (!$this->registro->motorista()) {
            return false;
        }

        return true;
    }

    public function getFilename()
    {
        return "carteira_motorista";
    }

    public function getTipo()
    {
        return "AUXILIAR";
    }


    public function set_registro($registro)
    {
        parent::set_registro($registro);

        if (!$this->enabled()) {
            return;
        }

        $motorista = $this->registro->pegaReferencia();
        $this->setMotorista($motorista);
    }

    public function setMotorista($motorista)
    {

        $this->motorista = null;
        $this->motorista_pessoa = null;
        $this->motorista_pessoa_pf = null;

        if (!$motorista) {
            return null;
        }

        $pm = $motorista->getPessoaMotorista();
        if (!$pm) {
            throw new Escola_Exception("Falha ao emitir carteira de motorista, nenhuma pessoa motorista vinculada.");
        }

        $pf = $pm->getPessoaFisica();
        if (!$pf) {
            throw new Escola_Exception("Falha ao emitir carteira de motorista, nenhuma pessoa vinculada.");
        }

        $pessoa = $pf->getPessoa();

        $this->motorista = $motorista;
        $this->pessoa_motorista = $pm;
        $this->transporte_grupo = $motorista->getTransporteGrupo();
        $this->motorista_pessoa = $pessoa;
        $this->motorista_pessoa_pf = $pf;
    }

    public function getPessoaMotorista()
    {
        return $this->pessoa_motorista;
    }

    public function getPessoaFisica()
    {
        return $this->motorista_pessoa_pf;
    }

    public function getMatricula()
    {
        if (!($this->motorista && $this->motorista->matricula)) {
            return "--";
        }

        return $this->motorista->matricula;
    }
}
