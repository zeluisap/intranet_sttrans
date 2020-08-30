<?php

class Retorno extends Escola_Entidade {

    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->data_importacao = date("Y-m-d");
            $this->hora_importacao = date("H:i:s");
        }
    }

    public function setFromArray(array $dados) {
        if (isset($dados["data_importacao"])) {
            $dados["data_importacao"] = Escola_Util::formatData($dados["data_importacao"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors() {
        $msgs = array();
        if (!trim($this->convenio)) {
            $msgs[] = "CAMPO CONVÊNIO OBRIGATÓRIO!";
        }
        if (!Escola_Util::validaData($this->data_importacao)) {
            $msgs[] = "CAMPO DATA DE IMPORTAÇÃO INVÁLIDO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function delete() {
        if ($this->getId()) {
            $tb = new TbRetornoItem();
            $stmt = $tb->listar(array("filtro_id_retorno" => $this->getId()));
            if ($stmt && count($stmt)) {
                foreach ($stmt as $ri) {
                    $ri->delete();
                }
            }
            $arquivo = $this->pegaArquivo();
            if ($arquivo) {
                $arquivo->delete();
            }
        }
        parent::delete();
    }

    public function pegaArquivo() {
        $tb = new TbArquivoRef();
        $stmt = $tb->fetchAll("tipo = 'R' and chave = {$this->getId()}");
        if ($stmt && count($stmt)) {
            $ar = $stmt->current();
            return $ar->findParentRow("TbArquivo");
        }
        return false;
    }

    public function pegaQtdItems() {
        $tb = new TbRetornoItem();
        $stmt = $tb->listar(array("filtro_id_retorno" => $this->getId()));
        if ($stmt && count($stmt)) {
            return count($stmt);
        }
        return 0;
    }

    public function pegaValorTotal() {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("ri" => "retorno_item"), array("total" => "sum(valor_pago)"));
        $sql->where("ri.id_retorno = {$this->getId()}");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $obj = $stmt->fetch(Zend_Db::FETCH_OBJ);
            return $obj->total;
        }
        return 0;
    }

    public function processar() {

        $relatorio = null;
        $items = array();
        $arquivo = $this->pegaArquivo();
        $filename = $arquivo->pegaNomeCompleto();
        $rt = $this->findParentRow("TbRetornoTipo");
        if ($arquivo && $rt) {
            $items = $rt->processar_arquivo($arquivo);
        }
        $tb = new TbRetornoItem();
        if (!($items && is_array($items) && count($items))) {
            return array("Nenhum Boleto Encontrado!");
        }

        foreach ($items as $item) {
            if ($item->convenio && ($item->convenio != $this->convenio)) {
                $this->convenio = $item->convenio;
                $this->save();
            }
            if (!$item->id_boleto) {
                continue;
            }
            $boleto = TbBoleto::pegaPorId($item->id_boleto);
            if (!$boleto) {
                continue;
            }
            $valor_pago = Escola_Util::number_format(Escola_Util::montaNumero($item->valor_pago));
            $valor_boleto = $boleto->pegaValor();
            if (!is_numeric($valor_boleto)) {
                continue;
            }
            $valor_boleto = Escola_Util::number_format($valor_boleto);
            if ($valor_boleto != $valor_pago) {
                continue;
            }
            try {
                $dados = array();
                $dados["valor_pago"] = $item->valor_pago;
                $dados["data_pagamento"] = $item->data_pagamento;
                $dados["nosso_numero"] = $item->nosso_numero;
                $ri = $tb->createRow();
                $ri->id_retorno = $this->getId();
                $ri->id_boleto = $item->id_boleto;
                $ri->setFromArray($dados);
                $erros = $this->getErrors();
                if (!$erros) {
                    $ri->save();
                    $dados["retorno_item"] = $ri;
                    $relatorio = $boleto->confirmar_pagamento($dados);
                }
            } catch (Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        return $relatorio;
    }

}