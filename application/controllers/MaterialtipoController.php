<?php
class MaterialtipoController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("itemlistar", "json");
        $ajaxContext->addActionContext("itemsalvar", "json");
        $ajaxContext->initContext();
    }

    public function itemlistarAction()
    {
        $result = false;
        $tb = new TbMaterialTipoItem();
        $registros = $tb->listar($this->getRequest()->getPost());
        if ($registros && $registros->count()) {
            $result = array();
            foreach ($registros as $registro) {
                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->descricao = $registro->descricao;
                $result[] = $obj;
            }
        }
        $this->view->result = $result;
    }

    public function itemsalvarAction()
    {
        $result = new stdClass;
        $result->mensagem = false;
        $result->id = 0;
        $tb = new TbMaterialTipoItem();
        $row = $tb->createRow();
        $dados = $this->getRequest()->getPost();
        $dados = array_map("utf8_decode", $dados);
        $row->setFromArray($dados);
        $errors = $row->getErrors();
        if ($errors) {
            $result->mensagem = implode("<br>", $errors);
        } else {
            $row->save();
            if ($row->getId()) {
                $result->id = $row->getId();
            }
        }
        $this->view->result = $result;
    }

    public function indexAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_material_tipo)) {
            unset($session->id_material_tipo);
        }
        $tb = new TbMaterialTipo();
        $tb->recuperar();
        $page = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina(array("pagina_atual" => $page));
        $button = Escola_Button::getInstance();
        $button->setTitulo("TIPOS DE MATERIAL");
        $button->addFromArray(array(
            "titulo" => "Adicionar",
            "controller" => $this->_request->getControllerName(),
            "action" => "editar",
            "img" => "icon-plus-sign",
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

    public function editarAction()
    {
        $id = $this->_request->getParam("id");
        $tb = new TbMaterialTipo();
        if ($id) {
            $registro = $tb->getPorId($id);
        } else {
            $registro = $tb->createRow();
        }
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $registro->setFromArray($dados);
            $errors = $registro->getErrors();
            if ($errors) {
                $this->view->actionErrors = $errors;
            } else {
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $registro->save();
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        }
        $this->view->registro = $registro;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE TIPO DE MATERIAL - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE TIPO DE MATERIAL - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbMaterialTipo();
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

    public function viewAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbMaterialTipo();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR TIPO DE MATERIAL");
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

    public function itemAction()
    {
        $session = Escola_Session::getInstance();
        $id_material_tipo = 0;
        if (isset($session->id_material_tipo) && $session->id_material_tipo) {
            $id_material_tipo = $session->id_material_tipo;
        } elseif ($this->_request->getParam("id")) {
            $id_material_tipo = $this->_request->getParam("id");
        }
        if ($id_material_tipo) {
            $material_tipo = TbMaterialTipo::pegaPorId($id_material_tipo);
            if ($material_tipo) {
                $this->view->material_tipo = $material_tipo;
                $session->id_material_tipo = $material_tipo->getId();
                $tb = new TbMaterialTipoItem();
                $page = $this->_getParam("page");
                $this->view->registros = $tb->listar_por_pagina(array("id_material_tipo" => $material_tipo->getId(), "pagina_atual" => $page));
                $button = Escola_Button::getInstance();
                $button->setTitulo("TIPOS DE ÍTENS DE MATERIAL");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "itemeditar",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0)
                ));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function itemeditarAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_material_tipo) && $session->id_material_tipo) {
            $material_tipo = TbMaterialTipo::pegaPorId($session->id_material_tipo);
            if ($material_tipo) {
                $this->view->material_tipo = $material_tipo;
                $id = $this->_request->getParam("id");
                $tb = new TbMaterialTipoItem();
                if ($id) {
                    $registro = $tb->getPorId($id);
                } else {
                    $registro = $tb->createRow();
                }
                if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    $registro->setFromArray($dados);
                    $errors = $registro->getErrors();
                    if ($errors) {
                        $this->view->actionErrors = $errors;
                    } else {
                        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                        $registro->save();
                        $this->_redirect($this->_request->getControllerName() . "/item");
                    }
                }
                $this->view->registro = $registro;
                $button = Escola_Button::getInstance();
                if ($this->view->registro->getId()) {
                    $button->setTitulo("CADASTRO DE TIPO DE MATERIAL - ALTERAR");
                } else {
                    $button->setTitulo("CADASTRO DE TIPO DE MATERIAL - INSERIR");
                }
                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array(
                    "titulo" => "Cancelar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "item",
                    "img" => "icon-reply",
                    "params" => array("id" => 0)
                ));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function itemexcluirAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_material_tipo) && $session->id_material_tipo) {
            $material_tipo = TbMaterialTipo::pegaPorId($session->id_material_tipo);
            if ($material_tipo) {
                $id = $this->_request->getParam("id");
                if ($id) {
                    $tb = new TbMaterialTipoItem();
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
                $this->_redirect($this->_request->getControllerName() . "/item/id/0");
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function itemviewAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbMaterialTipoItem();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR TIPO DE MATERIAL");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "item",
                    "img" => "icon-reply",
                    "params" => array("id" => 0)
                ));
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
