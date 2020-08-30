<?php
class TransportePessoaTipo extends Escola_Entidade
{

    public function toString()
    {
        return $this->descricao;
    }

    public function setFromArray(array $dados)
    {
        if (isset($dados["chave"])) {
            $dados["chave"] = Escola_Util::maiuscula($dados["chave"]);
        }
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->chave)) {
            $msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
        }
        if (!trim($this->descricao)) {
            $msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
        }
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_transporte_pessoa_tipo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TIPO DE VÍNCULO DE PESSOA  JÁ CADASTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getDeleteErrors()
    {
        $msgs = array();
        $registros = $this->findDependentRowset("TbTransportePessoa");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function proprietario()
    {
        return ($this->chave == "PR");
    }

    public function motorista()
    {
        return ($this->chave == "MO");
    }

    public function auxiliar()
    {
        return ((strtoupper($this->chave) == "AU") || $this->motorista());
    }
}
