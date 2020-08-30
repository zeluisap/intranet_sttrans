<?php 
class NotificacaoController extends Escola_Controller_Logado {
    
    public function indexAction() {
        $tb = new TbAutoInfracaoNotificacao();
        
        $sessao = Escola_Session::getInstance();
        $this->view->dados = $sessao->atualizaFiltros(array("filtro_placa", "filtro_chassi", "filtro_alfa", "filtro_codigo", "filtro_pf_nome", "filtro_data_infracao", "filtro_id_servico_solicitacao_status"));
        $this->view->dados["pagina_atual"] = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina($this->view->dados);
        $button = Escola_Button::getInstance();
        $button->setTitulo("NOTIFICAÇOES DE AUTO DE INFRAÇAO");
        $button->addFromArray(array("titulo" => "Pesquisar",
                                                "onclick" => "pesquisar()",
                                                "img" => "icon-search",
                                                "params" => array("id" => 0)));
        $button->addFromArray(array("titulo" => "Voltar",
                                                "controller" => "intranet",
                                                "action" => "index",
                                                "img" => "icon-reply",
                                                "params" => array("id" => 0)));
    }
    
    public function viewAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbAutoInfracaoNotificacao();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->rjs = $registro->pegaRequerimentoJari();
                $this->view->registro = $registro;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR NOTIFICAÇÃO DE AUTO DE INFRAÇÃO");
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
	
    public function excluirAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbAutoInfracaoNotificacao();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $errors = $registro->getDeleteErrors();
                if (!$errors) {
                    try {
                        $registro->delete();
                        $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    } catch (Exception $e) {
                        $this->addErro($e->getMessage());
                    }
                } else {
                    foreach ($errors as $erro) {
                        $this->_flashMessage($erro);
                    }
                }
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }

    public function licencapgtoAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbServicoSolicitacao();
            $ss = $tb->getPorId($id);
            if ($ss) {
                if (!$ss->pago()) {
                    $this->view->registro = $ss;
                    if ($this->_request->isPost()) {
                        $dados = $this->_request->getPost();
                        $tb = new TbServicoSolicitacaoPagamento();
                        $pgto = $tb->createRow();
                        $pgto->setFromArray($dados);
                        $errors = $pgto->getErrors();
                        if (!$errors) {
                            $pgto->save();
                            if ($pgto->getId()) {
                                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                                $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                            } else {
                                $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
                                $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                            }
                        } else {
                            $this->view->actionErrors = $errors;
                        }
                    }
                    $button = Escola_Button::getInstance();
                    $button->setTitulo("CONFIRMAÇÃO DE PAGAMENTO DE NOTIFICAÇÃO DE AUTO DE INFRAÇÃO");
                    $button->addScript("Confirmar Pagamento", "salvarFormulario('formulario')", "icon-save");
                    $button->addFromArray(array("titulo" => "Voltar",
                                                "controller" => $this->_request->getControllerName(),
                                                "action" => "index",
                                                "img" => "icon-reply",
                                                "params" => array("id" => 0)));
                } else {
                    $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
                    $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                }
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function descontoAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbServicoSolicitacao();
            $ss = $tb->getPorId($id);
            if ($ss) {
                if (!$ss->pago()) {
                    $this->view->registro = $ss;
                    if ($this->_request->isPost()) {
                        $dados = $this->_request->getPost();
                        $tb = new TbServicoSolicitacaoDesconto();
                        $pgto = $tb->createRow();
                        $pgto->setFromArray($dados);
                        $errors = $pgto->getErrors();
                        if (!$errors) {
                            $pgto->save();
                            if ($pgto->getId()) {
                                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                                $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                            } else {
                                $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
                                $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                            }
                        } else {
                            $this->view->actionErrors = $errors;
                        }
                    }
                    $button = Escola_Button::getInstance();
                    $button->setTitulo("APLICAR DESCONTO À NOTIFICAÇÃO DE AUTO DE INFRAÇÃO");
                    $button->addScript("Aplicar Desconto", "salvarFormulario('formulario')", "icon-save");
                    $button->addFromArray(array("titulo" => "Voltar",
                                                "controller" => $this->_request->getControllerName(),
                                                "action" => "index",
                                                "img" => "icon-reply",
                                                "params" => array("id" => 0)));
                } else {
                    $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
                    $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                }
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
}