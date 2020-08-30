<?php
class VeiculoController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("listarporpagina", "json");
        $ajaxContext->addActionContext("dados", "json");
        $ajaxContext->addActionContext("salvar", "json");
        $ajaxContext->initContext();
    }

    public function salvarAction()
    {
        $this->view->id = false;
        $this->view->erro = "";
        $this->view->descricao = "";
        $dados = $this->getRequest()->getPost();
        $tb = new TbVeiculo();
        if (isset($dados["chassi"]) && $dados["chassi"]) {
            $registro = $tb->getPorChassi($dados["chassi"]);
            if (!$registro) {
                $registro = $tb->createRow();
            }
            $dados = array_map("utf8_decode", $dados);
            $registro->setFromArray($dados);
            $errors = $registro->getErrors();
            if ($errors) {
                $this->view->erro = implode("<br>", $errors);
            } else {
                try {
                    $id = $registro->save();
                } catch (Exception $e) {
                    die($e->getMessage());
                }
                if ($id) {
                    $this->view->id = $id;
                    $this->view->descricao = $registro->toString();
                } else {
                    $this->view->erro = "Falha ao Executar Operação, Chame o Administrador!";
                }
            }
        } else {
            $this->view->erro = "Campo Chassi Obrigatório!";
        }
    }

    public function dadosAction()
    {
        $obj = new stdClass();
        $this->view->erro = "";
        $this->view->obj = false;
        $dados = $this->getRequest()->getPost();
        $tb = new TbVeiculo();
        $flag_chassi = (isset($dados["filtro_chassi"]) && trim($dados["filtro_chassi"]));
        $flag_placa = (isset($dados["filtro_placa"]) && trim($dados["filtro_placa"]));
        if ($flag_chassi || $flag_placa) {
            $registro = false;
            $flag = true;
            if ($flag_chassi) {
                $rs = $tb->listar(array("filtro_chassi" => $dados["filtro_chassi"]));
                if ($rs && count($rs)) {
                    $registro = $rs->current();
                    if ($flag_placa && ($registro->placa != $dados["placa"])) {
                        $this->view->erro = "Placa Informada diferente da cadastrada para este Chassi!";
                        $flag = false;
                    }
                } elseif ($flag_placa) {
                    $rs = $tb->listar(array("filtro_placa" => $dados["filtro_placa"]));
                    if ($rs && count($rs)) {
                        $registro = $rs->current();
                        if ($flag_chassi && ($registro->chassi != $dados["chassi"])) {
                            $this->view->erro = "Chassi Informado é diferente do cadastrado para Esta Placa!";
                            $flag = false;
                        }
                    }
                }
            } elseif ($flag_placa) {
                $rs = $tb->listar(array("filtro_placa" => $dados["filtro_placa"]));
                if ($rs && count($rs)) {
                    $registro = $rs->current();
                }
            }
            if ($flag) {
                if (!$registro) {
                    $registro = $tb->createRow();
                    $registro->setFromArray(array("chassi" => $dados["filtro_chassi"], "placa" => $dados["filtro_placa"]));
                }
                foreach ($registro->toArray() as $field => $value) {
                    $obj->$field = $value;
                }
                $obj->data_aquisicao = Escola_Util::formatData($obj->data_aquisicao);
                $this->view->obj = $obj;
            }
        } else {
            $this->view->erro = "Informe o Número do Chassi e/ou Placa!";
        }
    }

    public function listarporpaginaAction()
    {
        $superior = false;
        $dados = $this->getRequest()->getPost();
        $tb = new TbVeiculo();
        $registros = $tb->listar_por_pagina($dados);
        $info = $registros->getPages();
        $this->view->items = false;
        $this->view->total_pagina = $info->pageCount;
        $this->view->pagina_atual = $info->current;
        $this->view->primeira = $info->first;
        $this->view->ultima = $info->last;
        if ($registros && count($registros)) {
            $items = array();
            foreach ($registros as $registro) {
                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->placa = $registro->placa;
                $obj->ano_modelo = $registro->ano_modelo;
                $obj->uf = $registro->findParentRow("TbUf")->toString();
                $obj->veiculo_tipo = $registro->findParentRow("TbVeiculoTipo")->toString();
                $obj->fabricante = $registro->findParentRow("TbFabricante")->toString();
                $obj->combustivel = $registro->findParentRow("TbCombustivel")->toString();
                $obj->veiculo = $registro->toString();
                $items[] = $obj;
            }
            $this->view->items = $items;
        }
    }

    public function indexAction()
    {
        $sessao = Escola_Session::getInstance();
        $this->view->dados = $sessao->atualizaFiltros(array("filtro_placa", "filtro_id_fabricante", "filtro_chassi", "filtro_proprietario"));
        $this->view->dados["pagina_atual"] = $this->_getParam("page");
        $tb = new TbVeiculo();
        $this->view->registros = $tb->listar_por_pagina($this->view->dados);
        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE VEÍCULO");
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
        $tb = new TbVeiculo();
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
            $button->setTitulo("CADASTRO DE VEÍCULOS - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE VEÍCULOS - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbVeiculo();
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
            $tb = new TbVeiculo();
            $registro = $tb->getPorId($id);
            if ($registro) {
                if ($registro->retido()) {
                    $this->view->actionErrors[] = "VEÍCULO RETIDO NO PÁTIO DA INSTITUIÇÃO!";
                }
                $tb = new TbTransporteVeiculo();
                $this->view->registro = $registro;
                $this->view->transporte_veiculo = $tb->listar(array("id_veiculo" => $registro->getId()));
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR VEÍCULO");
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
}
