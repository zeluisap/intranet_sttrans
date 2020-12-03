<?php

use Escola\Servico\BoletoRegistro;

class Boleto extends Escola_Entidade
{

    public function init()
    {
        if (!$this->getId()) {
            $this->data_criacao = date("Y-m-d");
        }
        parent::init();
    }

    public function setFromArray(array $dados)
    {
        if (isset($dados["data_criacao"])) {
            $dados["data_criacao"] = Escola_Util::montaData($dados["data_criacao"]);
        }
        if (isset($dados["data_vencimento"])) {
            $dados["data_vencimento"] = Escola_Util::montaData($dados["data_vencimento"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors()
    {
        $msgs = array();
        if (!Escola_Util::validaData($this->data_criacao)) {
            $msgs[] = "CAMPO DATA DE CRIAÇÃO É INVÁLIDO!";
        }

        if (!Escola_Util::validaData($this->data_vencimento)) {
            $msgs[] = "CAMPO DATA DE VENCIMENTO É INVÁLIDO!";
        }

        if (!trim($this->id_pessoa)) {
            $msgs[] = "CAMPO PESSOA OBRIGATÓRIO!";
        }
        if (!trim($this->id_banco_convenio)) {
            $msgs[] = "CAMPO CONVÊNIO BANCÁRIO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function save($flag = false)
    {
        $id = parent::save($flag);
        if (!$this->nosso_numero) {
            $this->nosso_numero = $this->pegaNossoNumero();
            $this->save(false);
        }
        return $id;
    }

    public function pegaItems()
    {
        $items = $this->findDependentRowset("TbBoletoItem");
        if ($items && count($items)) {
            return $items;
        }
        return false;
    }

    public function pegaValor()
    {
        $items = $this->pegaItems();
        if ($items && count($items)) {
            $valor = 0;
            foreach ($items as $item) {
                $valor += $item->valor;
            }
            return $valor;
        }
        return 0;
    }

    public function confirmar_pagamento($dados = array())
    {

        $errors = array();
        $relatorio = new stdClass();
        $relatorio->errors = null;
        $relatorio->sucesso = 0;

        $ri = null;
        if (isset($dados["retorno_item"]) && is_object($dados["retorno_item"])) {
            $ri = $dados["retorno_item"];
        }

        $items = $this->pegaItems();
        if ($items && count($items)) {
            foreach ($items as $item) {
                $erro = $item->confirmar_pagamento($dados);
                if ($erro) {
                    $errors[] = $item->toString() . " - " . $erro;
                } else {
                    $relatorio->sucesso++;
                }
                if ($ri) {
                    $tb_rir = new TbRetornoItemRelatorio();
                    $rir = $tb_rir->createRow();
                    $rir->id_retorno_item = $ri->getId();
                    $rir->id_boleto_item = $item->getId();
                    if ($erro) {
                        $rir->confirmado = "N";
                        $rir->mensagem = $erro;
                    } else {
                        $rir->confirmado = "S";
                    }
                    $rir_errors = $rir->getErrors();
                    if ($rir_errors) {
                        throw new Exception("Falha ao Gerar Relatório do Processamento do Boleto!");
                    }
                    try {
                        $rir->save();
                    } catch (Exception $ex) {
                        var_dump($ex);
                        die();
                    }
                }
            }
        }

        if (count($errors)) {
            $relatorio->errors = $errors;
        }

        return $relatorio;
    }

    public function pegaNossoNumero()
    {
        $bc = $this->findParentRow("TbBancoConvenio");
        if ($bc) {
            $nosso_numero = $bc->convenio . Escola_Util::zero($this->getId(), 10);
        } else {
            $nosso_numero = Escola_Util::zero($this->getId(), 17);
        }
        return $nosso_numero;
    }

    public function mostrarCedente()
    {
        $pessoa = $this->findParentRow("TbPessoa");
        if ($pessoa) {
            return $pessoa->toString();
        }
        return false;
    }

    public function pegaRetornoItem()
    {
        $tb = new TbRetornoItem();
        $objs = $tb->listar(array("filtro_id_boleto" => $this->getId()));
        if ($objs && count($objs)) {
            return $objs->current();
        }
        return false;
    }

    public function pago()
    {
        $items = $this->pegaItems();
        if ($items) {
            foreach ($items as $item) {
                if (!$item->pago()) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getDataPagamento()
    {
        $ri = $this->pegaRetornoItem();
        if (!$ri) {
            return "";
        }

        return $ri->data_pagamento;
    }

    public function registrar()
    {

        if ($this->registrado) {
            return true;
        }

        $response = BoletoRegistro::registrarBoleto($this);

        $this->registrado = true;
        $this->save();

        return $response;
    }

    public function getBancoConvenio()
    {
        return $this->findParentRow("TbBancoConvenio");
    }

    /**
     * @return Pessoa
     */
    public function pegaPessoa()
    {
        return $this->findParentRow("TbPessoa");
    }
}
