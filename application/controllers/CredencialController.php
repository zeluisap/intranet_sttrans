<?php 
class CredencialController extends Escola_Controller_Logado {
	
    public function indexAction() {
        $session = Escola_Session::getInstance();
        if (isset($session->id_credencial)) {
            unset($session->id_credencial);
        }
        $tb = new TbCredencial();
        $dados = $session->atualizaFiltros(array("filtro_id_credencial_tipo", "filtro_id_credencial_status", "filtro_cpf", "filtro_nome"));
        $dados["pagina_atual"] = $this->_getParam("page");
        
        $this->view->dados = $dados;
        $this->view->registros = $tb->listar_por_pagina($dados);
        
        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE CREDENCIAL");
        $button->addFromArray(array("titulo" => "Adicionar",
                                    "controller" => $this->_request->getControllerName(),
                                    "action" => "editar",
                                    "img" => "icon-plus-sign",
                                    "params" => array("id" => 0)));
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

    public function editarAction() {
        $id = $this->_request->getParam("id");
        $tb = new TbCredencial();
        if ($id) {
            $registro = $tb->getPorId($id);
            if (!$registro) {
                $this->_flashMessage("Credencial Inválida!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
            $cs = $registro->findParentRow("TbCredencialStatus");
            if (!$cs->pendente()) {
                $this->_flashMessage("Status da Credencial Inválido para Edição!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $registro = $tb->createRow();
        }
        if ($this->_request->isPost()) {
            try {
                $dados = $this->_request->getPost();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors) {
                    $this->view->actionErrors[] = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
                } else {
                    $this->_flashMessage("Credencial Salva com Sucesso!", "Messages");
                    $registro->save();
                    
                    if (isset($dados["resposta"]) && $dados["resposta"]) {
                        try {
                            if (!in_array($dados["resposta"], array("D", "I"))) {
                                throw new Exception("Credencial Não Respondida, Resposta Inválida!");
                            }
                            if ($dados["resposta"] == "D") {
                                $data_validade = false;
                                if (isset($dados["resposta_data_validade"])) {
                                    $data_validade = $dados["resposta_data_validade"];
                                }
                                $registro->deferir($data_validade);
                            } elseif ($dados["resposta"] == "I") {
                                $justificativa = false;
                                if (isset($dados["resposta_justificativa"])) {
                                    $justificativa = $dados["resposta_justificativa"];
                                }
                                if (empty($justificativa)) {
                                    throw new Exception("Credencial Não Respondida, Justificativa Obrigatória para Indeferimento!");
                                }
                                $registro->indeferir($justificativa);
                            }
                        } catch (Exception $ex) {
                            $this->_flashMessage($ex->getMessage());
                        }
                    }
                    
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }  
            } catch (Exception $ex) {
                $this->view->actionErrors[] = "<ul><li>" . $ex->getMessage() . "</li></ul>";
            }
        }
        $this->view->registro = $registro;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE CREDENCIAL - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE CREDENCIAL - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbCredencial();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $errors = $registro->getDeleteErrors();
                if (!$errors) {
                    $registro->delete();
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
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

    public function viewAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbCredencial();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->ocorrencias = $registro->pegaOcorrencias();
                
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR CREDENCIAL");
                $button->addFromArray(array("titulo" => "Alterar",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "editar",
                                            "img" => "icon-cog",
                                            "params" => array("id" => $id)));
                $button->addFromArray(array("titulo" => "Excluir",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "excluir",
                                            "img" => "icon-trash",
                                            "class" => "link_excluir",
                                            "params" => array("id" => $id)));
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
    
    public function deferircancelarAction() {
        try {
            $id = 0;
            $session = Escola_Session::getInstance();
            if (isset($session->id_credencial)) {
                $id = $session->id_credencial;
            } else {
                $id = $this->getParam("id");
            }
            if (!$id) {
                throw new Exception("Falha ao Executar Operação, Credencial não Encontrada!");
            }
            
            $tb = new TbCredencial();
            $credencial = $tb->getPorId($id);
            if (!$credencial) {
                throw new Exception("Falha ao Executar Operação, Credencial não Encontrada!!");
            }
            
            $cs = $credencial->findParentRow("TbCredencialStatus");
            if (!$cs) {
                throw new Exception("Falha ao Executar Operação, Status da Credencial Não Encontrado!");
            }
            
            if ($cs->pendente()) {
                throw new Exception("Falha ao Executar Operação, Credencial Pendente!!");
            }
            
            $session->id_credencial = $id;
            
            $justificativa = "";
            if ($this->_request->isPost()) {
               try {
                    $dados = $this->_request->getPost();
                    if (!isset($dados["justificativa"])) {
                        throw new Exception("Falha ao Executar Operação, Campo Justificativa Obrigatório!");
                    }

                    $justificativa = $dados["justificativa"];
                    $credencial->cancelar_resposta($dados["justificativa"]);
                    $this->_flashMessage("Credencial Salva com Sucesso!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                    
               } catch (Exception $ex) {
                    $this->view->actionErrors[] = $ex->getMessage();
               }
            }
            
            $this->view->credencial = $credencial;
            $this->view->justificativa = $justificativa;

            $button = Escola_Button::getInstance();
            $button->setTitulo("CREDENCIAL - CANCELAMENTO DE RESPOSTA");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
            
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function deferirAction() {
        try {
            $id = 0;
            $session = Escola_Session::getInstance();
            if (isset($session->id_credencial)) {
                $id = $session->id_credencial;
            } else {
                $id = $this->getParam("id");
            }
            if (!$id) {
                throw new Exception("Falha ao Executar Operação, Credencial não Encontrada!");
            }
            
            $tb = new TbCredencial();
            $credencial = $tb->getPorId($id);
            if (!$credencial) {
                throw new Exception("Falha ao Executar Operação, Credencial não Encontrada!!");
            }
            
            $cs = $credencial->findParentRow("TbCredencialStatus");
            if (!$cs) {
                throw new Exception("Falha ao Executar Operação, Status da Credencial Não Encontrado!");
            }
            
            if (!$cs->pendente()) {
                throw new Exception("Falha ao Executar Operação, Credencial Não Está Pendente!!");
            }
            
            $session->id_credencial = $id;
            
            $resposta = $justificativa = $data_validade = "";
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                try {
                    if (!isset($dados["resposta"])) {
                        throw new Exception("Falha ao Executar Operação, Resposta Inválida!");
                    }
                    
                    $resposta = $dados["resposta"];
                    
                    if (!in_array($dados["resposta"], array("D", "I"))) {
                        throw new Exception("Falha ao Executar Operação, Resposta Inválida!");
                    }
                    
                    if ($dados["resposta"] == "D") {
                        $data_validade = false;
                        if (isset($dados["data_validade"])) {
                            $data_validade = $dados["data_validade"];
                        }
                        $credencial->deferir($data_validade);
                    } elseif ($dados["resposta"] == "I") {
                        $justificativa = false;
                        if (isset($dados["justificativa"])) {
                            $justificativa = $dados["justificativa"];
                        }
                        if (empty($justificativa)) {
                            throw new Exception("Falha ao Executar Operação, Justificativa Obrigatória para Indeferimento!");
                        }
                        $credencial->indeferir($justificativa);
                    }
                    
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                    
                } catch (Exception $ex) {
                    $this->view->actionErrors[] = $ex->getMessage();
                }
            }
            
            $this->view->credencial = $credencial;
            $this->view->justificativa = $justificativa;
            $this->view->data_validade = $data_validade;
            $this->view->resposta = $resposta;

            $button = Escola_Button::getInstance();
            $button->setTitulo("CREDENCIAL - CANCELAMENTO DE RESPOSTA");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
            
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function imprimirAction() {
        try {
            $id = $this->getParam("id");
            if (!$id) {
                throw new Exception("Falha ao Executar Operação, Credencial Inválida!");
            }
            
            $tb = new TbCredencial();
            $credencial = $tb->getPorId($id);
            if (!$credencial) {
                throw new Exception("Falha ao Executar Operação, Credencial não Encontrada!!");
            }

            $credencial->imprimir();
            
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function renovarAction() {
        try {
            $id = 0;
            $session = Escola_Session::getInstance();
            if (isset($session->id_credencial)) {
                $id = $session->id_credencial;
            } else {
                $id = $this->getParam("id");
            }
            if (!$id) {
                throw new Exception("Falha ao Executar Operação, Credencial não Encontrada!");
            }
            
            $tb = new TbCredencial();
            $credencial = $tb->getPorId($id);
            if (!$credencial) {
                throw new Exception("Falha ao Executar Operação, Credencial não Encontrada!!");
            }
            
            if (!$credencial->vencida()) {
                throw new Exception("Falha ao Executar Operação, Credencial Não Vencida!!");
            }
            
            $session->id_credencial = $id;
            
            $txt_validade = "";
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                try {
                    if (!isset($dados["validade"])) {
                        throw new Exception("Falha ao Renovar Credencial!");
                    }
                    if (!trim($dados["validade"])) {
                        throw new Exception("Falha ao Renovar Credencial!");
                    }
                    if (!is_numeric($dados["validade"])) {
                        throw new Exception("Falha ao Renovar Credencial! Informe o valor em Anos!");
                    }
                    
                    $credencial->renovar($dados["validade"]);
                    
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                    
                } catch (Exception $ex) {
                    $this->view->actionErrors[] = $ex->getMessage();
                }
            }
            
            $this->view->credencial = $credencial;
            if (!$txt_validade) {
                $txt_validade = 2;
            }
            $this->view->validade = $txt_validade;
            
            $button = Escola_Button::getInstance();
            $button->setTitulo("CREDENCIAL - RENOVAR");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
            
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
}