<?php

class RetornoItem extends Escola_Entidade {

    public function setFromArray(array $dados) {
        if (isset($dados["data_pagamento"])) {
            $dados["data_pagamento"] = Escola_Util::montaData($dados["data_pagamento"]);
        }
        if (isset($dados["valor_pago"])) {
            $dados["valor_pago"] = Escola_Util::montaNumero($dados["valor_pago"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_retorno)) {
            $msgs[] = "CAMPO RETORNO OBRIGATÓRIO!";
        }
        if (!trim($this->nosso_numero)) {
            $msgs[] = "CAMPO NOSSO NÚMERO OBRIGATÓRIO!";
        }
        if (!Escola_Util::validaData($this->data_pagamento)) {
            $msgs[] = "CAMPO DATA DE PAGAMENTO INVÁLIDO!";
        }
        if (!trim($this->valor_pago)) {
            $msgs[] = "CAMPO VALOR PAGO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function pegaRelatorio(BoletoItem $bi) {
        if (!$this->getId()) {
            return null;
        }
        if (!$bi) {
            return null;
        }
        if (!$bi->getId()) {
            return null;
        }
        
        $tb = new TbRetornoItemRelatorio();
        $objs = $tb->listar(array(
            "filtro_id_retorno_item" => $this->getId(),
            "filtro_id_boleto_item" => $bi->getId()
        ));
        
        if (!($objs && $objs->count())) {
            return null;
        }

        if ($objs->count() > 1) {
            throw new Exception("Falha, Mais de um Relatório vinculado a um Retorno.");
        }
        
        return $objs->current();
    }
}