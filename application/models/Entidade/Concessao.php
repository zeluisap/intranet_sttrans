<?php
class Concessao extends Escola_Entidade
{

    public function setFromArray(array $dados)
    {
        if (isset($dados["concessao_data"])) {
            $dados["concessao_data"] = Escola_Util::montaData($dados["concessao_data"]);
        }
        if (isset($dados["numero"])) {
            $dados["numero"] = Escola_Util::maiuscula($dados["numero"]);
        }
        if (isset($dados["decreto"])) {
            $dados["decreto"] = Escola_Util::maiuscula($dados["decreto"]);
        }
        if (isset($dados["processo_numero"])) {
            $dados["processo_numero"] = Escola_Util::maiuscula($dados["processo_numero"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->id_concessao_tipo)) {
            $msgs[] = "CAMPO TIPO DA CONCESSÃO OBRIGATÓRIO!";
        }
        if (!trim($this->numero)) {
            $msgs[] = "CAMPO NÚMERO DA CONCESSÃO OBRIGATÓRIO!";
        }
        if (!Escola_Util::validaData($this->concessao_data)) {
            $msgs[] = "CAMPO DATA DA CONCESSÃO INVÁLIDO!";
        }
        if (!trim($this->id_concessao_validade)) {
            $msgs[] = "CAMPO VALIDADE DA CONCESSÃO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getDeleteErrors()
    {
        $msgs = array();
        $registros = $this->findDependentRowset("TbTransporte");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function toString()
    {
        return $this->numero . " - " . $this->decreto . " - " . $this->processo_numero . "/" . $this->processo_ano;
    }

    public function getValidade()
    {
        return $this->findParentRow("TbConcessaoValidade");
    }
}
