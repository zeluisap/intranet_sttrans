<?php 
class JariController extends Escola_Controller_Logado {
    
    public function indexAction() {
        $tb = new TbRequerimentoJari();
        
        $sessao = Escola_Session::getInstance();
        $this->view->dados = $sessao->atualizaFiltros(array("filtro_placa", "filtro_chassi", "filtro_alfa", "filtro_codigo", "filtro_pf_nome", "filtro_data_infracao", "filtro_id_requerimento_jari_status"));
        $this->view->dados["pagina_atual"] = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina($this->view->dados);
        $button = Escola_Button::getInstance();
        $button->setTitulo("REQUERIMENTO JARI");
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
            $tb = new TbRequerimentoJari();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR REQUERIMENTO DE NOTIFICAÇÃO DE AUTO DE INFRAÇÃO");
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
            $tb = new TbRequerimentoJari();
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
    
    public function responderAction() {
        $id = $this->_request->getParam("id");
        $tb = new TbRequerimentoJari();
        if ($id) {
            $registro = $tb->getPorId($id);
            if ($registro->getId()) {
                $tb_rjs = new TbRequerimentoJariStatus();
                $rjss = $tb_rjs->listar();
                if (!$rjss) {
                    $this->addErro("NENHUM STATUS DE REQUERIMENTO CADASTRADO!");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
                $ain = $registro->findParentRow("TbAutoInfracaoNotificacao");
                if ($ain) {
                    $infracoes = $ain->listarInfracao();
                }
                if ($this->_request->isPost()) {
                    try {
                        $tb = new TbRequerimentoJariResposta();
                        $rjr = $tb->createRow();
                        $dados = $this->_request->getPost();
                        if (!isset($dados["id_requerimento_jari_status"]) || !$dados["id_requerimento_jari_status"]) {
                            throw new Exception("POR FAVOR ESCOLHA UMA OPÇÃO DE RESPOSTA!");
                        }
                        $tb_f = new TbFuncionario();
                        $funcionario = $tb_f->pegaLogado();
                        if ($funcionario) {
                            $dados["id_funcionario"] = $funcionario->getId();
                        }
                        $registro->responder($dados);
                        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                        $this->_redirect($this->_request->getControllerName() . "/index");
                    } catch (Exception $e) {
                        $this->view->actionErrors[] = $e->getMessage();
                    }
                }
                $this->view->registro = $registro;
                $this->view->ain = $ain;
                $this->view->infracoes = $infracoes;
                $this->view->rjss = $rjss;
                $button = Escola_Button::getInstance();
                if ($this->view->registro->getId()) {
                    $button->setTitulo("RESPOSTA DE REQUERIMENTO JARI");
                }
                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
            } else {
                $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }	
    
    public function cancelarrespostaAction() {
        $id = $this->_request->getParam("id");
        $tb = new TbRequerimentoJari();
        if ($id) {
            $registro = $tb->getPorId($id);
            if ($registro->getId()) {
                try {
                    $registro->cancelar_resposta();
                    $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
                } catch (Exception $ex) {
                    $this->addErro($ex->getMessage());
                }
            } else {
                $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS");
            }
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }
}