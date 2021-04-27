<?php
class ServicoSolicitacaoPagamento extends Escola_Entidade
{

    protected $_valor_pago = false;
    protected $_valor_desconto = false;
    protected $_valor_juros = false;

    public function pega_valor_pago()
    {
        if ($this->_valor_pago) {
            return $this->_valor_pago;
        }
        $valor = TbValor::pegaPorId($this->id_valor_pago);
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }

    public function pega_valor_juros()
    {
        if ($this->_valor_juros) {
            return $this->_valor_juros;
        }
        $valor = TbValor::pegaPorId($this->id_valor_juros);
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }

    public function pega_valor_desconto()
    {
        if ($this->_valor_desconto) {
            return $this->_valor_desconto;
        }
        $valor = TbValor::pegaPorId($this->id_valor_desconto);
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }

    public function init()
    {
        parent::init();
        $this->_valor_pago = $this->pega_valor_pago();
        $this->_valor_desconto = $this->pega_valor_desconto();
        $this->_valor_juros = $this->pega_valor_juros();
        if (!$this->getId()) {
            $tb = new TbServicoSolicitacaoPagamentoStatus();
            $sss = $tb->getPorChave("A");
            if ($sss) {
                $this->id_servico_solicitacao_pagamento_status = $sss->getId();
            }
            $hoje = new Zend_Date();
            $this->data_pagamento = $hoje->toString("YYYY-MM-dd");
        }
    }

    public function setFromArray(array $dados)
    {
        if (isset($dados["valor_pago"]) && $dados["valor_pago"]) {
            $this->_valor_pago->setFromArray(array("valor" => $dados["valor_pago"]));
        }
        if (isset($dados["valor_desconto"]) && $dados["valor_desconto"]) {
            $this->_valor_desconto->setFromArray(array("valor" => $dados["valor_desconto"]));
        }
        if (isset($dados["valor_juros"]) && $dados["valor_juros"]) {
            $this->_valor_juros->setFromArray(array("valor" => $dados["valor_juros"]));
        }
        if (isset($dados["data_pagamento"])) {
            $dados["data_pagamento"] = Escola_Util::montaData($dados["data_pagamento"]);
        }
        parent::setFromArray($dados);
    }

    public function save()
    {
        $in_trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_trans = false;
        }
        try {

            $this->id_valor_pago = $this->_valor_pago->save();
            $this->id_valor_desconto = $this->_valor_desconto->save();
            $this->id_valor_juros = $this->_valor_juros->save();
            $id = parent::save();
            if (!$id) {
                throw new Exception("Falha ao Executar Operação, Dados Inválidos!");
            }

            $ss = $this->findParentRow("TbServicoSolicitacao");
            if (!$ss) {
                throw new Exception("Falha ao Executar Operação, Solicitação de Serviço Inválido!");
            }

            $tb = new TbServicoSolicitacaoStatus();
            $sss = $tb->getPorChave("PG");
            if (!$sss) {
                throw new Exception("Falha ao Executar Operação, Status de Solicitação de Serviço Inválido!");
            }

            $ss->id_servico_solicitacao_status = $sss->getId();
            if (!$ss->data_inicio) {
                $ss->data_inicio = date("Y-m-d");
            }
            $ss->save();

            $ss->gerarOcorrencia("P");

            if ($in_trans) {
                $db->commit();
            }

            return $id;
        } catch (Exception $ex) {

            if ($in_trans) {
                $db->rollBack();
            }

            throw $ex;
        }
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->id_servico_solicitacao_pagamento_status)) {
            $msgs[] = "CAMPO STATUS DA SOLICITAÇÃO DE SERVIÇO OBRIGATÓRIO!";
        }
        if (!trim($this->id_servico_solicitacao)) {
            $msgs[] = "CAMPO SOLICITAÇÃO OBRIGATÓRIO!";
        }
        if (!$this->_valor_pago->valor) {
            // $msgs[] = "CAMPO VALOR PAGO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
}
