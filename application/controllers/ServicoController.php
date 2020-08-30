<?php
class ServicoController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("listar", "json");
        $ajaxContext->initContext();
    }

    public function listarAction()
    {
        $result = false;
        $tb = new TbServico();
        $objs = $tb->listar($this->getRequest()->getPost());

        if (!($objs && $objs->count())) {
            return;
        }

        $result = [];
        foreach ($objs as $servico) {
            $obj = new stdClass();
            $obj->id = $servico->id_servico;
            $obj->descricao = $servico->descricao;

            $result[] = $obj;
        }

        $this->view->result = $result;
    }

    public function indexAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_servico)) {
            unset($session->id_servico);
        }
        $tb = new TbServico();
        $dados = $session->atualizaFiltros(array("filtro_codigo", "filtro_descricao"));
        $dados["pagina_atual"] = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina($dados);
        $this->view->dados = $dados;

        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE SERVIÇOS");
        $button->addFromArray(array(
            "titulo" => "Adicionar",
            "controller" => $this->_request->getControllerName(),
            "action" => "editar",
            "img" => "icon-plus-sign",
            "params" => array("id" => 0)
        ));
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

    public function editarAction()
    {
        $id = $this->_request->getParam("id");
        $tb = new TbServico();
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
            $button->setTitulo("CADASTRO DE SERVIÇOS - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE SERVIÇOS - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbServico();
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
            $tb = new TbServico();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR SERVIÇO");
                $button->addFromArray(array(
                    "titulo" => "Alterar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editar",
                    "img" => "icon-cog",
                    "params" => array("id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Excluir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "excluir",
                    "img" => "icon-trash",
                    "class" => "link_excluir",
                    "params" => array("id" => $id)
                ));
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
    public function vinculoAction()
    {
        $session = Escola_Session::getInstance();
        if (!isset($session->id_servico) && $this->_request->getParam("id_servico")) {
            $session->id_servico = $this->_request->getParam("id_servico");
        }
        $id_servico = $session->id_servico;
        $tb = new TbServico();
        $servico = $tb->pegaPorId($id_servico);
        if ($servico) {
            $page = $this->_getParam("page");
            $tb = new TbServicoTransporteGrupo();
            if ($servico->transito()) {
                $registros = $tb->listar(array("pagina_atual" => $page, "id_servico" => $servico->getId()));
                if ($registros && count($registros)) {
                    $registro = $registros->current();
                } else {
                    $tb = new TbServicoTransporteGrupo();
                    $registro = $tb->createRow();
                }
                $this->_redirect($this->_request->getControllerName() . "/editarvinculo/id/{$registro->getId()}");
            }
            $this->view->registros = $tb->listar_por_pagina(array("pagina_atual" => $page, "id_servico" => $servico->getId()));
            $this->view->servico = $servico;
            $button = Escola_Button::getInstance();
            $button->setTitulo("CADASTRO DE VÍNCULO DE SERVIÇO");
            $button->addFromArray(array(
                "titulo" => "Adicionar",
                "controller" => $this->_request->getControllerName(),
                "action" => "editarvinculo",
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
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarvinculoAction()
    {
        $session = Escola_Session::getInstance();
        $id_servico = 0;
        if (isset($session->id_servico)) {
            $id_servico = $session->id_servico;
        }
        $tb = new TbServico();
        $servico = $tb->pegaPorId($id_servico);
        if (!$servico) {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
            return;
        }

        $this->view->servico = $servico;
        $id = $this->_request->getParam("id");
        $tb = new TbServicoTransporteGrupo();
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
                $registro->save();
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $this->_redirect($this->_request->getControllerName() . "/vinculo");
            }
        }
        $this->view->registro = $registro;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE VÍNCULO DE SERVIÇO - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE VÍNCULO DE SERVIÇO - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $action = "vinculo";
        if ($servico->transito()) {
            $action = "index";
        }
        $button->addFromArray(array(
            "titulo" => "Cancelar",
            "controller" => $this->_request->getControllerName(),
            "action" => $action,
            "img" => "icon-remove-circle",
            "params" => array("id" => 0)
        ));
    }

    public function excluirvinculoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbServicoTransporteGrupo();
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
        $this->_redirect($this->_request->getControllerName() . "/vinculo");
    }

    public function viewvinculoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbServicoTransporteGrupo();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->servico = $registro->findParentRow("TbServico");
                $this->view->transporte_grupo = $registro->findParentRow("TbTransporteGrupo");
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR VÍNCULO ENTRE SERVIÇO E GRUPO DE TRANSPORTE");
                $button->addFromArray(array(
                    "titulo" => "Alterar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarvinculo",
                    "img" => "icon-cog",
                    "params" => array("id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Excluir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "excluirvinculo",
                    "img" => "icon-trash",
                    "class" => "link_excluir",
                    "params" => array("id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "vinculo",
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
