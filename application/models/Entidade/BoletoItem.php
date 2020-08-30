<?php

class BoletoItem extends Escola_Entidade {

    public function setFromArray(array $dados) {
        if (isset($dados["valor"])) {
            $dados["valor"] = Escola_Util::montaNumero($dados["valor"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_boleto_item_tipo)) {
            $msgs[] = "CAMPO TIPO DE ÍTEM DE BOLETO OBRIGATÓRIO!";
        }
        if (!trim($this->id_boleto)) {
            $msgs[] = "CAMPO BOLETO OBRIGATÓRIO!";
        }
        if (!trim($this->chave)) {
            $msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
        }
        if (!trim($this->valor)) {
            $msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
        }
        if ($this->id_boleto_item_tipo && $this->chave) {
            $rg = $this->getTable()->fetchAll(" id_boleto = {$this->id_boleto} and id_boleto_item_tipo = '{$this->id_boleto_item_tipo}' and chave = {$this->chave} and id_boleto_item <> '" . $this->getId() . "' ");
            if ($rg && count($rg)) {
                $msgs[] = "ÍTEM DE BOLETO JÁ CADASTRADO!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function pegaReferencia() {
        $bit = $this->findParentRow("TbBoletoItemTipo");
        if ($bit) {
            $tb = false;
            if ($bit->servico_solicitacao()) {
                $tb = new TbServicoSolicitacao();
            }
            if ($tb) {
                $obj = $tb->pegaPorId($this->chave);
                if ($obj) {
                    return $obj;
                }
            }
        }
        return false;
    }

    public function toString() {
        $obj = $this->pegaReferencia();
        if ($obj) {
            return $obj->toString();
        }
        return "";
    }

    public function confirmar_pagamento($dados = array()) {
        $obj = $this->pegaReferencia();
        if (!$obj) {
            return null;
        }
        
        try {
            $obj->confirmar_pagamento($dados);
            
            return null;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function pago() {
        $referencia = $this->pegaReferencia();
        if ($referencia) {
            return $referencia->pago();
        }
        return false;
    }

}