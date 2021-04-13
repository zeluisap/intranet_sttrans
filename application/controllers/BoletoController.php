<?php

use Escola\Servico\BoletoRegistro;
use GuzzleHttp\Exception\ServerException;

class BoletoController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("registra", "json");
        $ajaxContext->initContext();
    }


    public function registraAction()
    {
        try {
            $this->view->result = BoletoRegistro::registrarTodos();
        } catch (ServerException $ex) {
            $this->view->result = [
                "erro" => $ex->getResponse()->getBody()->getContents()
            ];
        } catch (Exception $ex) {
            $this->view->result = [
                "erro" => $ex->getMessage()
            ];
        }
    }

    public function indexAction()
    {
        $session = Escola_Session::getInstance();
        $dados = $session->atualizaFiltros(array("filtro_id_boleto", "filtro_convenio", "filtro_nome", "filtro_nosso_numero"));
        $tb = new TbBoleto();
        $dados["pagina_atual"] = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina($dados);
        $this->view->dados = $dados;
        $button = Escola_Button::getInstance();
        $button->setTitulo("BOLETOS");
        $button->addFromArray(array(
            "titulo" => "Pesquisar",
            "onclick" => "pesquisar()",
            "img" => "icon-search",
            "params" => array("id" => 0)
        ));
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => "intranet",
            "action" => "index",
            "img" => "icon-reply",
            "params" => array("id" => 0)
        ));
    }

    public function viewAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbBoleto();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->items = false;
                $registros = $registro->pegaItems();
                if ($registros) {
                    $this->view->registros = $registros;
                }
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR BOLETO");
                $button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function pagamentoAction()
    {
        try {

            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("Falha Nenhum Boleto Informado!");
            }
            $boleto = TbBoleto::pegaPorId($id);
            if (!$boleto) {
                throw new Exception("Falha Nenhum Boleto Informado!!");
            }
            if ($boleto->Pago()) {
                throw new Exception("Falha ao Executar Operação, Boleto já Pago!");
            }

            if ($this->_request->isPost()) {
                $db = Zend_Registry::get("db");
                $db->beginTransaction();
                try {
                    $dados = $this->_request->getPost();

                    $tb_rt = new TbRetornoTipo();
                    $rt = $tb_rt->getPorChave("MANUAL");
                    if (!$rt) {
                        throw new Exception("Tipo de Retorno Manual Não Cadastrado!");
                    }
                    $tb_r = new TbRetorno();
                    $r = $tb_r->createRow();
                    $con = $boleto->findParentRow("TbBancoConvenio");
                    if ($con) {
                        $r->convenio = $con->convenio;
                    }
                    $r->id_retorno_tipo = $rt->getId();
                    $r->save();
                    if (!$r->getId()) {
                        throw new Exception("Falha, Retorno Não Salvo!");
                    }

                    $tb_ri = new TbRetornoItem();
                    $ri = $tb_ri->createRow();
                    $ri->id_retorno = $r->getId();
                    $ri->nosso_numero = $boleto->nosso_numero;
                    $ri->valor_pago = $boleto->pegaValor();
                    $ri->id_boleto = $boleto->getId();
                    $ri->data_pagamento = date("Y-m-d");
                    if (isset($dados["data_pagamento"]) && Escola_Util::limpaNumero($dados["data_pagamento"])) {
                        $data_pagamento = Escola_Util::montaData($dados["data_pagamento"]);
                        if (!Escola_Util::validaData($data_pagamento)) {
                            throw new Exception("Falha, Data [{$dados["data_pagamento"]}] de Pagamento Inválida!");
                        }
                        $ri->data_pagamento = $data_pagamento;
                    }
                    $ri->save();
                    if (!$ri->getId()) {
                        throw new Exception("Falha, Ítem de Retorno Não Salvo!");
                    }

                    $dados["retorno_item"] = $ri;
                    $boleto->confirmar_pagamento($dados);
                    if (!$boleto->pago()) {
                        throw new Exception("Falha ao Confirmar Pagamento de Boleto, Chame o Administrador!");
                    }

                    $db->commit();

                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                    die();
                } catch (Exception $ex) {
                    $db->rollBack();

                    $this->view->actionErrors[] = $ex->getMessage();
                }
            }

            $this->view->registro = $boleto;
            $button = Escola_Button::getInstance();
            $button->setTitulo("CONFIRMAR PAGAMENTO DE BOLETO");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
}
