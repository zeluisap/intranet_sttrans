<?php
class ServicoSolicitacaoStatus extends Escola_Entidade
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
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_servico_solicitacao_status <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "STATUS DA SOLICITAÇÃO DE SERVIÇO JÁ CADASTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getDeleteErrors()
    {
        $msgs = array();
        $registros = $this->findDependentRowset("TbServicoSolicitacao");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function aguardando_pagamento()
    {
        return ($this->chave == "AG");
    }

    public function documento_entregue()
    {
        return ($this->chave == "DE");
    }

    public function pago()
    {
        return (($this->chave == "PG") || $this->documento_entregue());
    }

    public function cancelado()
    {
        return ($this->chave == "CA");
    }

    public function toHTML($ss)
    {
        $txt = "";

        $class = "label-warning";

        if ($this->pago()) {
            $class = "label-info";
            if (!$ss->cancelado() && $ss->vencida()) {
                $txt .= " (VENCIDA)";
                $class = "label-danger";
            }
        } elseif ($this->cancelado()) {
            $class = "label-inverse";
        }
        if ($txt) {
            $txt = $this->toString() . " " . $txt;
        } else {
            $txt = $this->toString();
        }
        return "<div style='font-size:14px' class='label {$class} label-pagamento'>{$txt}</div>";
    }
}
