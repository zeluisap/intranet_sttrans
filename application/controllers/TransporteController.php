<?php

class TransporteController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("transporteform", "json");
        $ajaxContext->initContext();
    }

    public function transporteformAction()
    {
        $dados = $this->getRequest()->getPost();
        $tb = new TbTransporte();
        $transporte = false;
        if (isset($dados["id_transporte"]) && $dados["id_transporte"]) {
            $transporte = $tb->pegaPorId($dados["id_transporte"]);
        } else {
            $transporte = $tb->createRow();
        }
        unset($dados["id_transporte"]);
        $transporte->setFromArray($dados);
        $this->view->possui_concessao = $transporte->possui_concessao();
        $obj = $transporte->getTransporteInstancia();
        $this->view->result = false;
        if ($obj) {
            $this->view->result = $obj->render($this->view);
        }
    }

    public function indexAction()
    {
        $this->removeGrupoValor();

        $tb = new TbTransporte();
        $session = Escola_Session::getInstance();
        unset($session->id_transporte);
        unset($session->tipo);
        unset($session->chave);
        $dados = $session->atualizaFiltros(array("filtro_id_transporte_grupo", "filtro_codigo", "filtro_placa", "filtro_proprietario_nome"));
        $dados["pagina_atual"] = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina($dados);
        $this->view->dados = $dados;
        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE TRÂNSITO");
        $button->addFromArray(array(
            "titulo" => "Adicionar",
            "controller" => $this->_request->getControllerName(),
            "action" => "editar",
            "img" => "icon-plus-sign",
            "params" => array("id" => 0),
        ));
        $button->addFromArray(array(
            "titulo" => "Pesquisar",
            "onclick" => "pesquisar()",
            "img" => "icon-search",
            "params" => array("id" => 0),
        ));
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => "intranet",
            "action" => "index",
            "img" => "icon-reply",
            "params" => array("id" => 0),
        ));
    }

    public function editarAction()
    {
        $id = $this->_request->getParam("id");
        $tb = new TbTransporte();
        if ($id) {
            $transporte = $tb->getPorId($id);
            $registro = $transporte->getTransporteInstancia();
        } else {
            $transporte = $tb->createRow();
            $registro = $transporte;
        }
        if ($this->_request->isPost()) {
            try {
                $dados = $this->_request->getPost();
                if (isset($dados["id_transporte_grupo"]) && $dados["id_transporte_grupo"]) {
                    $transporte->id_transporte_grupo = $dados["id_transporte_grupo"];
                    $registro = $transporte->getTransporteInstancia();
                }
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors) {
                    throw new Exception("<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
                }
                $registro->save();
                if (isset($dados["id_pessoa"]) && $dados["id_pessoa"]) {
                    $tb = new TbTransportePessoaTipo();
                    $tpt = $tb->getPorChave("PR");
                    if ($tpt) {
                        $tb = new TbTransportePessoa();
                        $tp = $tb->createRow();
                        $tp->setFromArray(array(
                            "id_transporte_pessoa_tipo" => $tpt->getId(),
                            "id_pessoa" => $dados["id_pessoa"],
                            "id_transporte" => $registro->id_transporte,
                        ));
                        $erros = $tp->getErrors();
                        if (!$erros) {
                            $tp->save();
                        }
                    }
                }

                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $this->_redirect($this->_request->getControllerName() . "/index");
            } catch (Exception $ex) {
                $this->view->actionErrors[] = $ex->getMessage();
            }
        }
        $this->view->registro = $registro;
        $this->view->transporte = $transporte;
        $this->view->concessao = $transporte->get_concessao();
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE TRANSPORTES - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE TRANSPORTES - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbTransporte();
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
            $tb = new TbTransporte();
            $transporte = $tb->getPorId($id);
            if ($transporte) {
                $this->view->transporte = $transporte;
                $this->view->registro = $transporte->getTransporteInstancia();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR TRANSPORTE");
                $button->addFromArray(array(
                    "titulo" => "Alterar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "alterar",
                    "img" => "icon-cog",
                    "params" => array("id" => $id),
                ));
                $button->addFromArray(array(
                    "titulo" => "Excluir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "excluir",
                    "img" => "icon-trash",
                    "class" => "link_excluir",
                    "params" => array("id" => $id),
                ));
                $button->addFromArray(array(
                    "titulo" => "Pessoas",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "pessoa",
                    "img" => "icon-group",
                    "params" => array("id" => 0, "id_transporte" => $id),
                ));
                $button->addFromArray(array(
                    "titulo" => "VEÍCULOs",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "veiculo",
                    "img" => "icon-truck",
                    "params" => array("id" => 0, "id_transporte" => $id),
                ));
                $button->addFromArray(array(
                    "titulo" => "SERVIÇOs",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "licenca",
                    "img" => "icon-copy",
                    "params" => array("id" => 0, "id_transporte" => $id),
                ));
                $button->addFromArray(array(
                    "titulo" => "Relat?rios",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "relatorio",
                    "img" => "icon-print",
                    "params" => array("id" => $id),
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

    public function pessoaAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_transporte)) {
            $id = $session->id_transporte;
        } else {
            $id = $this->_request->getParam("id_transporte");
        }
        if ($id) {
            $session->id_transporte = $id;
            $tb = new TbTransporte();
            $transporte = $tb->getPorId($session->id_transporte);
            if ($transporte) {
                $this->view->transporte = $transporte;
                $page = $this->_getParam("page");
                $tb = new TbTransportePessoa();
                $this->view->registros = $tb->listar_por_pagina(array("id_transporte" => $transporte->getId(), "pagina_atual" => $page));
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE TRÂNSITO > PESSOAS");
                /*
                $button->addFromArray(array("titulo" => "Emitir Carteiras",
                "onclick" => "carteira()",
                "img" => "icon-print",
                "params" => array("id" => 0)));
                 */
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarpessoa",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewpessoaAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbTransportePessoa();
            $tp = $tb->getPorId($id);
            if ($tp) {
                $this->view->registro = $tp;
                $this->view->pessoa = $tp->findParentRow("TbPessoa");
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR TRANSPORTE > PESSOA");
                $button->addFromArray(array(
                    "titulo" => "Excluir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "excluirpessoa",
                    "img" => "icon-trash",
                    "class" => "link_excluir",
                    "params" => array("id_transporte" => $tp->id_transporte, "id" => $id),
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "pessoa",
                    "img" => "icon-reply",
                    "params" => array("id_transporte" => $tp->id_transporte, "id" => 0),
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

    public function editarpessoaAction()
    {
        $id = $this->_request->getParam("id");
        $tb = new TbTransportePessoa();
        if ($id) {
            $registro = $tb->getPorId($id);
            $transporte = $registro->findParentRow("TbTransporte");
        } else {
            $session = Escola_Session::getInstance();
            $registro = $tb->createRow();
            $tb = new TbTransporte();
            $transporte = TbTransporte::pegaPorId($session->id_transporte);
        }
        $this->view->transporte = $transporte;
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $registro->setFromArray($dados);
            $errors = $registro->getErrors();
            if ($errors) {
                $this->view->actionErrors = $errors;
            } else {
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $registro->save();
                $this->_redirect($this->_request->getControllerName() . "/pessoa/id/0");
            }
        }
        $this->view->registro = $registro;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE TRANSPORTES > PESSOA - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE TRANSPORTES > PESSOA - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "pessoa",
            "img" => "icon-remove-circle",
            "params" => array("id_transporte" => $registro->id_transporte, "id" => 0),
        ));
    }

    public function excluirpessoaAction()
    {
        $session = Escola_Session::getInstance();
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbTransportePessoa();
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
        $this->_redirect($this->_request->getControllerName() . "/pessoa/id/0");
    }

    public function veiculoAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->tipo)) {
            unset($session->tipo);
        }
        if (isset($session->chave)) {
            unset($session->chave);
        }

        if (isset($session->id_transporte)) {
            $id = $session->id_transporte;
        } else {
            $id = $this->_request->getParam("id_transporte");
        }
        if ($id) {
            $session->id_transporte = $id;
            $tb = new TbTransporte();
            $transporte = $tb->getPorId($session->id_transporte);
            if ($transporte) {
                $dados = $session->atualizaFiltros(array("filtro_placa", "filtro_chassi"));
                $this->view->transporte = $transporte;
                $page = $this->_getParam("page");
                $tb = new TbTransporteVeiculo();
                $dados["id_transporte"] = $transporte->getId();
                $dados["pagina_atual"] = $page;
                $this->view->registros = $tb->listar_por_pagina($dados);
                $this->view->dados = $dados;
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE TRÂNSITO > VEÍCULOS");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarveiculo",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Pesquisar",
                    "onclick" => "pesquisar()",
                    "img" => "icon-search",
                    "params" => array("id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarveiculoAction()
    {
        $id = $this->_request->getParam("id");
        $tb = new TbTransporteVeiculo();
        if ($id) {
            $registro = $tb->getPorId($id);
            $transporte = $registro->findParentRow("TbTransporte");
        } else {
            $session = Escola_Session::getInstance();
            $registro = $tb->createRow();
            $tb = new TbTransporte();
            $transporte = TbTransporte::pegaPorId($session->id_transporte);
        }
        if (!$registro->baixa()) {
            $this->view->transporte = $transporte;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors) {
                    $this->view->actionErrors = $errors;
                } else {
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $registro->save();
                    $this->_redirect($this->_request->getControllerName() . "/veiculo/id/0");
                }
            }
            $this->view->registro = $registro;
            $button = Escola_Button::getInstance();
            if ($this->view->registro->getId()) {
                $button->setTitulo("CADASTRO DE TRANSPORTES > VEÍCULO - ALTERAR");
            } else {
                $button->setTitulo("CADASTRO DE TRANSPORTES > VEÍCULO - INSERIR");
            }
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "veiculo",
                "img" => "icon-remove-circle",
                "params" => array("id_transporte" => $registro->id_transporte, "id" => 0),
            ));
        } else {
            $this->_flashMessage("VEÍCULO EM BAIXA!");
            $this->_redirect($this->_request->getControllerName() . "/veiculo/id/0");
        }
    }

    public function excluirveiculoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbTransporteVeiculo();
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
        $this->_redirect($this->_request->getControllerName() . "/veiculo/id/0");
    }

    public function viewveiculoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbTransporteVeiculo();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->veiculo = $registro->findParentRow("TbVeiculo");
                $this->view->baixa = $registro->pegaBaixa();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR TRANSPORTE > VEÍCULO");
                if ($registro->ativo()) {
                    $button->addFromArray(array(
                        "titulo" => "Excluir",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "excluirveiculo",
                        "img" => "icon-trash",
                        "class" => "link_excluir",
                        "params" => array("id" => $id),
                    ));
                    $button->addFromArray(array(
                        "titulo" => "Baixa",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "baixa",
                        "img" => "icon-thumbs-down",
                        "params" => array("id" => $id, "id_transporte" => $registro->id_transporte),
                    ));
                }
                $button->addFromArray(array(
                    "titulo" => "SERVIÇOs",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "licenca",
                    "img" => "icon-copy",
                    "params" => array("tipo" => "TV", "chave" => $id, "id_transporte" => $registro->id_transporte, "id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "veiculo",
                    "img" => "icon-reply",
                    "params" => array("id_transporte" => $registro->id_transporte, "id" => 0),
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

    public function licencaAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_transporte)) {
            $id = $session->id_transporte;
        } else {
            $id = $this->_request->getParam("id_transporte");
        }
        if ($id) {
            $referencia = false;
            $hab_adicionar = true;
            $session->id_transporte = $id;
            $tb = new TbTransporte();
            $transporte = $tb->getPorId($session->id_transporte);
            if ($transporte) {
                $chave = null;
                $dados = $session->atualizaFiltros(array("filtro_id_servico", "filtro_id_veiculo_tipo", "filtro_ano_referencia", "filtro_mes_referencia", "filtro_id_servico_solicitacao_status"));
                //$dados["tipo"] = "TR";
                //$dados["chave"] = $transporte->getId();
                $return_action = "index";
                $tipo = $this->_request->getParam("tipo");
                if (!$tipo) {
                    $tipo = "TR";
                    if (isset($session->tipo)) {
                        $tipo = $session->tipo;
                    }
                    if (!$chave && isset($session->chave)) {
                        $chave = $session->chave;
                    } else {
                        $chave = $transporte->getId();
                    }
                }
                if (!$chave) {
                    $chave = $this->_request->getParam("chave");
                    if (!$chave && isset($session->chave)) {
                        $chave = $session->chave;
                    }
                }
                if ($tipo && $chave) {
                    $session->tipo = $tipo;
                    $session->chave = $chave;
                    $dados["tipo"] = $tipo;
                    $dados["chave"] = $chave;
                    $tb = new TbServicoReferencia();
                    $sr = $tb->getPorChave($tipo);
                    if ($sr->veiculo() || $sr->pessoa()) {
                        $referencia = $sr->pegaReferencia($chave);
                        if ($sr->veiculo()) {
                            if ($referencia && $referencia->baixa()) {
                                $hab_adicionar = false;
                            }
                            $return_action = "veiculo";
                        } elseif ($sr->pessoa()) {
                            $return_action = "pessoa";
                        }
                    }
                }
                if ($tipo == "TR") {
                    $dados["id_transporte"] = $transporte->getId();
                    unset($dados["tipo"]);
                    unset($dados["chave"]);
                }
                $this->view->transporte = $transporte;
                $dados["pagina_atual"] = $this->_getParam("page");
                $tb = new TbServicoSolicitacao();
                $this->view->registros = $tb->listar_por_pagina($dados);
                $this->view->dados = $dados;
                $this->view->referencia = $referencia;
                $pessoa = false;
                $proprietario = $transporte->pegaProprietario();
                if ($proprietario) {
                    $pessoa = $proprietario->findParentRow("TbPessoa");
                }
                $this->view->pessoa = $pessoa;
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE TRÃNSITO > SERVIÇOS");
                $button->addFromArray(array(
                    "titulo" => "Boleto ?nico",
                    "onclick" => "boleto()",
                    "img" => "icon-credit-card",
                    "params" => array("id" => 0),
                ));
                if ($hab_adicionar) {
                    $button->addFromArray(array(
                        "titulo" => "Adicionar",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "addservico",
                        "img" => "icon-plus-sign",
                        "params" => array("id" => 0, "id_transporte" => $transporte->getId()),
                    ));
                }
                $button->addFromArray(array(
                    "titulo" => "Pesquisar",
                    "onclick" => "pesquisar()",
                    "img" => "icon-search",
                    "params" => array("id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => $return_action,
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function baixaAction()
    {
        $registro = TbTransporteVeiculo::pegaPorId($this->getRequest()->getParam("id"));
        if ($registro) {
            $transporte = $registro->findParentRow("TbTransporte");
            if (!$registro->baixa()) {
                if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    $tb = new TbTransporteVeiculoBaixa();
                    $baixa = $tb->createRow();
                    $dados["id_transporte_veiculo"] = $registro->getId();
                    $usuario = TbUsuario::pegaLogado();
                    if ($usuario) {
                        $dados["id_usuario"] = $usuario->getId();
                    }
                    $baixa->setFromArray($dados);
                    $errors = $baixa->getErrors();
                    if ($errors) {
                        $this->view->actionErrors = $errors;
                    } else {
                        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                        $baixa->save();
                        $this->_redirect($this->_request->getControllerName() . "/veiculo/id/0/id_tranporte/{$transporte->getId()}");
                    }
                }
                $this->view->registro = $registro;
                $this->view->transporte = $transporte;
                $button = Escola_Button::getInstance();
                $button->setTitulo("BAIXA DE VEÍCULO");
                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "veiculo",
                    "img" => "icon-reply",
                    "params" => array("id" => $registro->getId(), "id_transporte" => $transporte->getId()),
                ));
            } else {
                $this->_flashMessage("VEÍCULO JÁ ESTÁ EM BAIXA!");
                $this->_redirect($this->_request->getControllerName() . "/veiculo/id/0");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewlicencaAction()
    {

        try {
            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Escola_Exception("NENHUMA INFORMAÇÃO RECEBIDA!");
            }

            $tb = new TbServicoSolicitacao();
            $ss = $tb->getPorId($id);
            if (!$ss) {
                throw new Escola_Exception("INFORMAÇÃO RECEBIDA INVÁLIDA!");
            }

            $transporte = false;
            $this->view->registro = $ss;
            $this->view->desconjuros = TbDesconjuros::calcular($ss);

            $referencia = $ss->pegaReferencia();
            if ($referencia) {
                if ($ss->transporte()) {
                    $transporte = $referencia;
                    $referencia = false;
                } elseif ($ss->veiculo() || $ss->pessoa()) {
                    $transporte = $referencia->findParentRow("TbTransporte");
                }
            }
            $this->view->transporte = $transporte;
            $this->view->referencia = $referencia;
            $this->view->pagamento = $ss->pegaPagamento();
            $this->view->ocorrencias = $ss->pegaOcorrencias();

            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR TRANSPORTE > SERVIÇO");
            if ($ss->pago()) {
                if ($ss->valido()) {
                    $button->addFromArray(array(
                        "titulo" => "Emitir Documento",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "emitir",
                        "img" => "icon-print",
                        "params" => array("id_transporte" => $ss->chave, "id" => $ss->getId()),
                    ));
                }
            } else {
                $button->addFromArray(array(
                    "titulo" => "Gerar Boleto",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "boleto",
                    "img" => "icon-credit-card",
                    "params" => array("target" => "_blank", "id_transporte" => $ss->chave, "id" => $ss->getId()),
                ));
                $button->addFromArray(array(
                    "titulo" => "Confirmar Pagamento",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "licencapgto",
                    "img" => "icon-money",
                    "params" => array("id_transporte" => $ss->chave, "id" => $ss->getId()),
                ));
            }
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "licenca",
                "img" => "icon-reply",
                "params" => array("id_transporte" => $ss->chave, "id" => 0),
            ));
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function licencapgtoAction()
    {

        try {

            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("NENHUMA INFORMAÇÃO RECEBIDA!");
            }

            $tb = new TbServicoSolicitacao();
            $ss = $tb->getPorId($id);
            if (!$ss) {
                throw new Exception("INFORMAÇÃO RECEBIDA INVÁLIDA!");
            }

            if ($ss->pago()) {
                throw new Escola_Exception("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
            }

            $transporte = $ss->pegaTransporte();

            $this->view->registro = $ss;
            $this->view->desconjuros = TbDesconjuros::calcularGrupos($ss);
            $this->view->transporte = $transporte;
            $this->view->referencia = $ss->pegaReferencia();
            $this->view->stg = $ss->findParentRow("TbServicoTransporteGrupo");
            if ($this->_request->isPost()) {
                try {
                    $dados = $this->_request->getPost();
                    $tb = new TbServicoSolicitacaoPagamento();
                    $pgto = $tb->createRow();
                    $pgto->setFromArray($dados);
                    $errors = $pgto->getErrors();
                    if ($errors) {
                        throw new Exception("Falha ao Executar OPERAÇÃO: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
                    }

                    $pgto->save();

                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$transporte->getId()}");
                } catch (Exception $ex) {
                    $this->view->actionErrors[] = $ex->getMessage();
                }
            }

            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR TRANSPORTE > SERVIÇO > PAGAMENTO");
            $button->addScript("Confirmar Pagamento", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "licenca",
                "img" => "icon-reply",
                "params" => array("id" => 0, "id_transporte" => $ss->chave),
            ));
        } catch (Escola_Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/licenca/id/0");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function boletoAction()
    {
        $boleto = false;
        $ids = array();
        $tb = new TbServicoSolicitacao();
        $dados = array();
        try {
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                if (isset($dados["lista"]) && is_array($dados["lista"]) && count($dados["lista"])) {
                    foreach ($dados["lista"] as $id) {
                        $ss = $tb->pegaPorId($id);
                        if ($ss) {
                            $ids[] = $ss;
                        }
                    }
                } elseif (isset($dados["id_transporte"]) && $dados["id_transporte"]) {
                    $sss = $tb->listar(array("id_transporte" => $dados["id_transporte"]));
                    if ($sss && count($sss)) {
                        foreach ($sss as $ss) {
                            if ($ss->aguardando_pagamento() && !$ss->vencida()) {
                                $ids[] = $ss;
                            }
                        }
                    }
                }
            } else {
                $id_boleto = $this->getParam("id_boleto");
                if ($id_boleto) {
                    $boleto = TbBoleto::pegaPorId($id_boleto);
                } else {
                    $id = $this->getParam("id");
                    if (!$id) {
                        throw new Exception("Falha ao Executar Operacao, Nenhuma Informacao Recebida!");
                    }

                    $ss = $tb->pegaPorId($id);
                    if ($ss) {
                        $ids[] = $ss;
                        $pessoa = $ss->pegaPessoa();
                        if ($pessoa) {
                            $dados["id_pessoa"] = $pessoa->getId();
                        }
                    }
                }
            }

            if (!$ids && !$boleto) {
                throw new Exception("Falha ao Executar Operacao, Nenhuma Informacao Recebida!");
            }

            if (!$boleto) {
                $id_pessoa = false;
                if (isset($dados["id_pessoa"]) && $dados["id_pessoa"]) {
                    $id_pessoa = $dados["id_pessoa"];
                }
                $tb = new TbBoleto();
                $data_vencimento = false;
                if (isset($dados["data_vencimento"]) && $dados["data_vencimento"]) {
                    $data_vencimento = $dados["data_vencimento"];
                }

                if (isset($dados["correcao"]) && $dados["correcao"]) {
                    $correcao = $dados["correcao"];
                }

                $boleto = $tb->criaBoleto($ids, $id_pessoa, $data_vencimento, $correcao);
            }

            if (!$boleto) {
                throw new UnderflowException("Falha ao Executar Operacao, Nenhum Boleto Bancario Disponivel!");
            }

            $pessoa = $boleto->findParentRow("TbPessoa");
            $bc = $boleto->findParentRow("TbBancoConvenio");
            if (!$bc) {
                throw new UnderflowException("Falha ao Executar Operacao, Nenhum Convenio Bancario Disponivel!");
            }

            $registroBoleto = $boleto->registrar();

            $ib = $bc->pega_info_bancaria();
            $banco = $ib->findParentRow("TbBanco");
            $this->_helper->layout()->disableLayout();
            $banco_sigla = Escola_Util::minuscula($banco->sigla);
            $filename = ROOT_DIR . "/public/boletophp/boleto_{$banco_sigla}.php";
            if (!file_exists($filename)) {
                throw new UnderflowException("Falha ao Executar Operacao, Boleto Nao Disponivel para o Banco: {$banco->toString()}!");
            }

            include $filename;
            die();
        } catch (UnderflowException $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$registro->id_transporte}");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function addservicoAction()
    {
        $session = Escola_Session::getInstance();
        $tipo = $session->tipo;
        $chave = $session->chave;
        if ($tipo && $chave) {
            $tb = new TbServicoReferencia();
            $sr = $tb->getPorChave($tipo);
            if ($sr) {
                $referencia = $sr->pegaReferencia($chave);
                if ($referencia) {
                    if ($sr->transporte()) {
                        $transporte = $referencia;
                        $referencia = false;
                    } elseif ($sr->veiculo() || $sr->pessoa()) {
                        $transporte = $referencia->findParentRow("TbTransporte");
                    }
                }
                $this->view->transporte = $transporte;
                $this->view->referencia = $referencia;
                $tb = new TbServicoTransporteGrupo();
                $stgs = $tb->listar(array("id_transporte_grupo" => $transporte->id_transporte_grupo, "id_servico_referencia" => $sr->getId()));
                if (!$stgs || !count($stgs)) {
                    $this->_flashMessage("NENHUM SERVIÇO DISPONÍVEL PARA O TRANSPORTE!");
                    $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$transporte->getId()}");
                }
                $this->view->stgs = $stgs;
                if ($this->_request->isPost()) {
                    try {

                        $dados = $this->_request->getPost();
                        $dados["tipo"] = $tipo;
                        $dados["chave"] = $chave;
                        $tb = new TbServicoSolicitacao();
                        $ss = $tb->createRow();
                        $ss->setFromArray($dados);
                        $stg = $ss->findParentRow("TbServicoTransporteGrupo");
                        if ($stg) {
                            $ss->pega_valor()->valor = $stg->pega_valor()->valor;
                        }
                        $errors = $ss->getErrors();
                        if ($errors) {
                            throw new Exception("Falha ao Salvar SOLICITAÇÃO de SERVIÇO: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
                        }
                        $ss->save();

                        $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Message");
                        $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$transporte->getId()}");
                    } catch (Exception $ex) {
                        $this->view->actionErrors[] = $ex->getMessage();
                    }
                }
                $button = Escola_Button::getInstance();
                $button->setTitulo("ADICIONAR SOLICITAÇÃO DE SERVIÇO");
                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array(
                    "titulo" => "Cancelar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "licenca",
                    "img" => "icon-remove-circle",
                    "params" => array("id" => 0, "id_transporte" => $transporte->getId()),
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function emitirAction()
    {
        // ini_set("display_errors", false);

        try {
            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Escola_Exception("Falha ao Executar OPERAÇÃO, SERVIÇO não Localizado!");
            }

            $tb = new TbServicoSolicitacao();
            $ss = $tb->getPorId($id);
            if (!$ss) {
                throw new Escola_Exception("Falha ao Executar OPERAÇÃO, SERVIÇO Não Localizado!");
            }

            if (!$ss->pago()) {
                throw new Escola_Exception("Falha ao Executar OPERAÇÃO, SOLICITAÇÃO de SERVIÇO Não Paga!");
            }

            $errors = $ss->validarEmitir();
            if ($errors) {
                throw new Escola_Exception("Falha ao Executar OPERAÇÃO: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }

            $flag = $ss->toPDF();

            if (!$flag) {
                throw new Escola_Exception("Falha ao Executar OPERAÇÃO: Relatório Não DISPONÍVEL!");
            }
        } catch (Escola_Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$ss->id_transporte}");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function carteiraAction()
    {
        $id_transporte = 0;
        $session = Escola_Session::getInstance();
        $id_transporte = $session->id_transporte;
        if (!$id_transporte) {
            $id_transporte = $this->_request->getParam("id_transporte");
        }
        if (!$id_transporte) {
            $id_transporte = "0";
        }
        $ids = array();
        $id = $this->_request->getParam("id");
        if ($id) {
            $ids[] = $id;
        }
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            if (isset($dados["id_transporte_pessoa"]) && is_array($dados["id_transporte_pessoa"]) && count($dados["id_transporte_pessoa"])) {
                $ids = array_merge($ids, $dados["id_transporte_pessoa"]);
            }
        }
        if (count($ids)) {
            $relatorio = new Escola_Relatorio_Carteira();
            $relatorio->toPDF($ids);
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA PESSOA SELECIONADA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index/id/0/id_transporte/{$id_transporte}");
    }

    public function fichacadastroAction()
    {
        $id = $this->_request->getParam("id_transporte");
        if ($id) {
            $transporte = TbTransporte::pegaPorId($id);
            $relatorio = new Escola_Relatorio_FichaCadastro();
            $relatorio->set_transporte($transporte);
            $relatorio->imprimir();
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function placaAction()
    {
        $id = $this->_request->getParam("id");
        $id_transporte = $this->_request->getParam("id_transporte");
        $tb = new TbTransporteVeiculo();
        $registro = $tb->pegaPorId($id);
        if ($registro) {
            $transporte = $registro->findParentRow("TbTransporte");
            $veiculo = $registro->findParentRow("TbVeiculo");
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $veiculo->setFromArray($dados);
                $errors = $veiculo->getErrors();
                if ($errors) {
                    $this->view->actionErrors = $errors;
                } else {
                    $veiculo->save();
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/veiculo/id_transporte/{$transporte->getId()}/id/0");
                }
            }
            $this->view->registro = $registro;
            $this->view->transporte = $transporte;
            $this->view->veiculo = $veiculo;
            $button = Escola_Button::getInstance();
            $button->setTitulo("TRANSPORTE > VEÍCULO > ATRIBUIR PLACA");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Cancelar",
                "controller" => $this->_request->getControllerName(),
                "action" => "veiculo",
                "img" => "icon-remove-circle",
                "params" => array("id" => 0, "id_transporte" => $transporte->getId()),
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/veiculo/id_transporte/{$id_transporte}/id/0");
        }
    }

    public function relatorioAction()
    {
        try {
            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("Nenhuma INFORMAÇÃO Recebida!");
            }

            $transporte = TbTransporte::pegaPorId($id);
            if (!$transporte) {
                throw new Exception("Nenhuma INFORMAÇÃO Recebida!");
            }

            $tb = new TbRelatorio();
            $relatorios = $tb->listar(array("tipos" => array("Tra", "Taxi")));
            if (!$relatorios) {
                throw new Exception("Nenhum Relat?rio DISPONÍVEL!");
            }

            $this->view->relatorios = $relatorios;
            $button = Escola_Button::getInstance();
            $button->setTitulo("TRÃNSITO > RELATÓRIO");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "index",
                "img" => "icon-reply",
                "params" => array("id" => 0),
            ));

            $this->view->transporte = $transporte;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                if (isset($dados["operacao"]) && ($dados["operacao"] == "imprimir")) {
                    if (!isset($dados["id_relatorio"]) || !$dados["id_relatorio"]) {
                        $this->view->actionErrors[] = "FALHA AO GERAR RELATÓRIO, NENHUM RELATÓRIO SELECIONADO!";
                    } else {
                        if (isset($dados["id"]) && $dados["id"]) {
                            $tr = TbTransporte::pegaPorId($dados["id"]);
                            if ($tr) {
                                $transporte = $tr;
                            }
                        }
                        $relatorio = TbRelatorio::getInstance($dados["id_relatorio"]);
                        if ($relatorio) {
                            $relatorio->set_transporte($transporte);
                            $relatorio->toPDF();
                        } else {
                            $this->view->actionErrors[] = "FALHA AO GERAR RELATÓRIO!";
                        }
                    }
                }
                $this->view->dados = $dados;
            }
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function servicocancelarAction()
    {
        $id = $this->_request->getParam("id");
        $id_transporte = $this->_request->getParam("id_transporte");
        if ($id) {
            $ss = TbServicoSolicitacao::pegaPorId($id);
            if ($ss) {
                if (!$ss->pago()) {
                    $ss->cancelar();
                } else {
                    $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
        $this->_redirect("transporte/licenca/id_transporte/{$id_transporte}");
    }

    public function boletovencimentoAction()
    {
        $session = Escola_Session::getInstance();
        try {

            if (!$this->_request->isPost()) {
                // throw new Exception("Falha ao Executar Operacao, Dados Invalidos!");
            }

            $dados = $this->_request->getPost();

            $querys = $this->_request->getParams();

            $ids = array();
            $tb = new TbServicoSolicitacao();

            $query_ss_id = Escola_Util::valorOuNulo($querys, "id");
            if ($query_ss_id) {
                $ss = $tb->getPorId($query_ss_id);
                if ($ss) {
                    $ids[] = $ss;
                }
            }

            if (isset($dados["lista"]) && is_array($dados["lista"]) && count($dados["lista"])) {
                foreach ($dados["lista"] as $id) {
                    $ss = $tb->pegaPorId($id);
                    if ($ss) {
                        $ids[] = $ss;
                    }
                }
            }

            if (!count($ids)) {
                $dados_filtro = $session->atualizaFiltros(array("filtro_id_servico", "filtro_id_veiculo_tipo", "filtro_ano_referencia", "filtro_mes_referencia", "filtro_id_servico_solicitacao_status"));
                if (isset($session->id_transporte)) {
                    $dados_filtro["id_transporte"] = $session->id_transporte;
                }
                $tb = new TbServicoSolicitacao();
                $objs = $tb->listar($dados_filtro);
                if ($objs && count($objs)) {
                    foreach ($objs as $obj) {
                        $ids[] = $obj;
                    }
                }
            }

            if (!count($ids)) {
                if (isset($dados["id_transporte"]) && $dados["id_transporte"]) {
                    $sss = $tb->listar(array("id_transporte" => $dados["id_transporte"]));
                    if ($sss && count($sss)) {
                        foreach ($sss as $ss) {
                            //if ($ss->aguardando_pagamento() && !$ss->vencida()) {
                            if ($ss->aguardando_pagamento()) {
                                $ids[] = $ss;
                            }
                        }
                    }
                }
            }

            if (!count($ids)) {
                throw new Exception("Falha ao Gerar Boleto, Nenhuma SOLICITAÇÃO de Servico Selecionada!");
            }

            $data_vencimento = new Zend_Date();
            $valor_total = $total_juros = $total_multas = 0;
            foreach ($ids as $ss) {
                $vencimento = new Zend_Date($ss->data_vencimento);
                if ($vencimento > $data_vencimento) {
                    $data_vencimento = $vencimento;
                }
                $valor = $ss->pega_valor();
                if ($valor) {
                    $valor_total += $valor->valor;
                }

                $juros = TbDesconjuros::pegaJuros($ss);
                if ($juros) {
                    $total_juros += $juros;
                }
                $multas = TbDesconjuros::pegaMultas($ss);
                if ($multas) {
                    $total_multas += $multas;
                }
            }

            $this->view->data_vencimento = Escola_Util::formatData($data_vencimento->toString("dd/MM/YYYY"));
            $this->view->valor_total = $valor_total;
            $this->view->juros = $total_juros;
            $this->view->multas = $total_multas;
            $this->view->valor_a_pagar = $valor_total + $total_juros + $total_multas;

            $pessoa = false;
            if (isset($dados["id_pessoa"]) && $dados["id_pessoa"]) {
                $pessoa = TbPessoa::pegaPorId($dados["id_pessoa"]);
            }

            if (!$pessoa && (isset($querys["id_pessoa"]) && $querys["id_pessoa"])) {
                $pessoa = TbPessoa::pegaPorId($querys["id_pessoa"]);
            }

            if (!$pessoa) {
                throw new Exception("Sacado do Boleto Nao Identificado!");
            }

            $this->view->ids = $ids;
            $this->view->pessoa = $pessoa;

            $button = Escola_Button::getInstance();
            $button->setTitulo("VENCIMENTO DO BOLETO");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "licenca",
                "img" => "icon-reply",
                "params" => array("id" => 0),
            ));
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect("transporte");
        }
    }

    public function solicitacaoservicodeleteAction()
    {
        try {
            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("Falha ao Executar OPERAÇÃO, SOLICITAÇÃO de SERVIÇO Não DISPONÍVEL!");
            }

            $ss = TbServicoSolicitacao::pegaPorId($id);
            if (!$ss) {
                throw new Exception("Falha ao Executar OPERAÇÃO, SOLICITAÇÃO de SERVIÇO Não DISPONÍVEL!");
            }

            $errors = $ss->getDeleteErrors();
            if ($errors) {
                throw new UnexpectedValueException("Falha ao Executar OPERAÇÃO: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }

            $ss->delete();
            $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
        } catch (Exception $ex) {
            $this->addErro($ex->getMessage());
        }

        $this->_redirect($this->_request->getControllerName() . "/licenca/id_transporte/" . $this->_request->getParam("id_transporte"));
    }

    public function cancelarbaixaAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tv = TbTransporteVeiculo::pegaPorId($id);
            if ($tv) {
                if ($tv->baixa()) {
                    $tv->cancelarBaixa();
                    if (!$tv->baixa()) {
                        $this->addMensagem("OPERA?AO EFETUADA COM SUCESSO!");
                    } else {
                        $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                    }
                } else {
                    $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, VEÍCULO NÃO EM BAIXA!");
                }
                $this->_redirect($this->_request->getControllerName() . "/veiculo/id_transporte/" . $tv->id_transporte);
            } else {
                $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }

    public function rotaAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_rota)) {
            unset($session->id_rota);
        }

        if (isset($session->id_transporte)) {
            $id = $session->id_transporte;
        } else {
            $id = $this->_request->getParam("id_transporte");
        }
        if ($id) {
            $session->id_transporte = $id;
            $tb = new TbTransporte();
            $transporte = $tb->getPorId($session->id_transporte);
            if ($transporte) {
                $this->view->transporte = $transporte;
                $page = $this->_getParam("page");
                $tb = new TbRota();
                $this->view->registros = $tb->listar_por_pagina(array("id_transporte" => $transporte->getId(), "pagina_atual" => $page));
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE TRÃNSITO > ROTAS");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarrota",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarrotaAction()
    {
        $id = $this->_request->getParam("id");
        $tb = new TbRota();
        if ($id) {
            $registro = $tb->getPorId($id);
            $transporte = $registro->findParentRow("TbTransporte");
        } else {
            $session = Escola_Session::getInstance();
            $registro = $tb->createRow();
            $tb = new TbTransporte();
            $transporte = TbTransporte::pegaPorId($session->id_transporte);
        }
        $this->view->transporte = $transporte;
        $tb = new TbDiaTipo();
        $dts = $tb->listar();
        if (!$dts) {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/rota/id/0");
        }
        if ($this->_request->isPost()) {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            try {
                $dados = $this->_request->getPost();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors) {
                    throw new Exception(implode("<br />", $errors));
                } else {
                    $registro->save();
                    $tb = new TbRotaDia();
                    if (isset($dados["dia_tipo"]) && is_array($dados["dia_tipo"]) && count($dados["dia_tipo"])) {
                        foreach ($dts as $dt) {
                            if (isset($dados["dia_tipo"][$dt->getId()])) {
                                $id_dia_tipo = $dt->getId();
                                $dia_tipo = $dados["dia_tipo"][$dt->getId()];
                                $dt_dados = array("id_rota" => $registro->getid(), "id_dia_tipo" => $id_dia_tipo);
                                $rds = $tb->listar($dt_dados);
                                if ($rds && count($rds)) {
                                    $rd = $rds->current();
                                } else {
                                    $rd = $tb->createRow($dt_dados);
                                }
                                if (isset($dia_tipo["veiculos"])) {
                                    $rd->veiculos = $dia_tipo["veiculos"];
                                }
                                if (isset($dia_tipo["viagens"])) {
                                    $rd->viagens = $dia_tipo["viagens"];
                                }
                                $erros = $rd->getErrors();
                                if (!$erros) {
                                    $rd->save();
                                } else {
                                    foreach ($erros as $id_erro => $erro) {
                                        $erros[$id_erro] = "TIPO DE DIA [" . $dt->toString() . "]: " . $erro;
                                    }
                                    throw new Exception(implode("<br>", $erros));
                                }
                            } else {
                                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA INFORMAÇÃO PARA O DIA: " . $dt->toString());
                            }
                        }
                    } else {
                        throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                    }
                }
                $db->commit();
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $this->_redirect($this->_request->getControllerName() . "/rota/id/0");
            } catch (Exception $ex) {
                $db->rollBack();
                $this->view->actionErrors[] = $ex->getMessage();
            }
        }
        $this->view->dts = $dts;
        $this->view->registro = $registro;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE TRANSPORTES > ROTA - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE TRANSPORTES > ROTA - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "rota",
            "img" => "icon-remove-circle",
            "params" => array("id_transporte" => $registro->id_transporte, "id" => 0),
        ));
    }

    public function excluirrotaAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbRota();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $errors = $registro->getDeleteErrors();
                if (!$errors) {
                    $flag = $registro->delete();
                    if ($flag) {
                        $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    } else {
                        $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
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
        $this->_redirect($this->_request->getControllerName() . "/rota/id/0");
    }

    public function viewrotaAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbRota();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $tb = new TbDiaTipo();
                $dts = $tb->listar();
                if (!$dts) {
                    //erro
                }
                $this->view->dts = $dts;
                $this->view->registro = $registro;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR TRANSPORTE > VEÍCULO");

                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "rota",
                    "img" => "icon-reply",
                    "params" => array("id_transporte" => $registro->id_transporte, "id" => 0),
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

    public function rotaviagemAction()
    {
        $session = Escola_Session::getInstance();

        if (isset($session->id_rota)) {
            $id = $session->id_rota;
        } else {
            $id = $this->_request->getParam("id");
        }
        if ($id) {
            $session->id_rota = $id;
            $tb = new TbRota();
            $rota = $tb->getPorId($session->id_rota);
            if ($rota) {
                $transporte = $rota->findParentRow("TbTransporte");
                $this->view->rota = $rota;
                $this->view->transporte = $transporte;
                $page = $this->_getParam("page");
                $tb = new TbRotaViagem();
                $this->view->registros = $tb->listar_por_pagina(array("id_rota" => $rota->getId(), "pagina_atual" => $page));
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE TRÃNSITO > ROTAS > SA?DAS");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "rota",
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function bdoAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_rota)) {
            $id = $session->id_rota;
        } else {
            $id = $this->_request->getParam("id");
        }
        if ($id) {
            $session->id_rota = $id;
            $tb = new TbRota();
            $rota = $tb->getPorId($session->id_rota);
            if ($rota) {
                $dados = $session->atualizaFiltros(array("filtro_id_transporte_veiculo", "filtro_id_tarifa", "filtro_data_inicial", "filtro_data_final"));
                $this->view->rota = $rota;
                $transporte = $rota->findParentRow("TbTransporte");
                $this->view->transporte = $transporte;
                $page = $this->_getParam("page");
                $tb = new TbOnibusBdo();
                $dados["id_rota"] = $rota->getId();
                $dados["pagina_atual"] = $page;
                $this->view->registros = $tb->listar_por_pagina($dados);
                $this->view->dados = $dados;
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE TRÃNSITO > ROTAS > BOLETIM DI?RIO DE ÔNIBUS");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarbdo",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Pesquisar",
                    "onclick" => "pesquisar()",
                    "img" => "icon-search",
                    "params" => array("id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "rota",
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarbdoAction()
    {
        $session = Escola_Session::getInstance();
        $id = $this->_request->getParam("id");
        $tb = new TbOnibusBdo();
        if ($id) {
            $registro = $tb->getPorId($id);
            $rota = $registro->findParentRow("TbRota");
        } else {
            $registro = $tb->createRow();
            $rota = TbRota::pegaPorId($session->id_rota);
        }
        $transporte = $rota->findParentRow("TbTransporte");
        $this->view->rota = $rota;
        $this->view->transporte = $transporte;
        $tvs = $transporte->pegaTransporteVeiculoAtivos();
        if (!$tvs || !count($tvs)) {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, NENHUM VEÍCULO ATIVO DISPONÍVEL!");
            $this->_redirect($this->_request->getControllerName() . "/bdo/id/0");
            die();
        }
        $tb = new TbTarifa();
        $tarifas = $tb->listar();
        if (!$tarifas || !count($tarifas)) {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA TARIFA DISPONÍVEL!");
            $this->_redirect($this->_request->getControllerName() . "/bdo/id/0");
            die();
        }
        $tb_tt = new TbTarifaTipo();
        $tts = $tb_tt->listar();
        $this->view->tts = $tts;
        if ($this->_request->isPost()) {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            try {
                $dados = $this->_request->getPost();
                if (isset($dados["id_tarifa"]) && $dados["id_tarifa"]) {
                    $tarifa = TbTarifa::pegaPorId($dados["id_tarifa"]);
                    if ($tarifa) {
                        $to = $tarifa->pega_ocorrencia_atual();
                        $dados["id_tarifa_ocorrencia"] = $to->getId();
                    }
                }
                $dados["id_rota"] = $rota->getId();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors) {
                    throw new Exception(implode("<br />", $errors));
                } else {
                    $registro->save();
                    if ($registro->getId()) {
                        if ($tts && count($tts)) {
                            $tb_obt = new TbOnibusBdoTarifa();
                            foreach ($tts as $tt) {
                                if (isset($dados["passageiros"][$tt->getId()]) && $dados["passageiros"][$tt->getId()]) {
                                    $array_obt = array("id_onibus_bdo" => $registro->getId(), "id_tarifa_tipo" => $tt->getId());
                                    $rs = $tb_obt->listar($array_obt);
                                    if ($rs && count($rs)) {
                                        $obt = $rs->current();
                                    } else {
                                        $obt = $tb_obt->createRow($array_obt);
                                    }
                                    $obt->passageiros = $dados["passageiros"][$tt->getId()];
                                    $errors = $obt->getErrors();
                                    if ($errors) {
                                        foreach ($errors as $errors_key => $error) {
                                            $errors[$errors_key] = $tt->toString() . ": " . $error;
                                        }
                                        throw new Exception(implode("<br />", $errors));
                                    } else {
                                        $obt->save();
                                    }
                                } else {
                                    throw new Exception("CAMPO PASSAGEIROS INVÁLIDO PARA O TIPO: " . $tt->toString());
                                }
                            }
                        }
                    } else {
                        throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                    }
                }
                $db->commit();
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $this->_redirect($this->_request->getControllerName() . "/bdo/id/0");
            } catch (Exception $ex) {
                $db->rollBack();
                $this->view->actionErrors[] = $ex->getMessage();
            }
        }
        $this->view->registro = $registro;
        $this->view->tvs = $tvs;
        //        $this->view->tarifas = $tarifas;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE TRANSPORTES > ROTA > BOLETIM DIÁRIO DE ÔNIBUS - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE TRANSPORTES > ROTA > BOLETIM DIÁRIO DE ÔNIBUS - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "bdo",
            "img" => "icon-remove-circle",
            "params" => array("id" => 0),
        ));
    }

    public function viewbdoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbOnibusBdo();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $tb = new TbTarifaTipo();
                $tts = $tb->listar();
                if (!$tts) {
                    $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA TARIFA DISPONÍVEL!");
                    $this->_redirect($this->_request->getControllerName() . "/bdo/id/0");
                    die();
                }
                $this->view->tts = $tts;
                $this->view->registro = $registro;

                $rota = $transporte = false;

                $rota = $registro->findParentRow("TbRota");
                if ($rota) {
                    $transporte = $rota->findParentRow("TbTransporte");
                }

                $this->view->rota = $rota;
                $this->view->transporte = $transporte;

                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR TRANSPORTE > ROTA > BDO");

                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "bdo",
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
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

    public function excluirbdoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbOnibusBdo();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $errors = $registro->getDeleteErrors();
                if (!$errors) {
                    $flag = $registro->delete();
                    if ($flag) {
                        $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    } else {
                        $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
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
        $this->_redirect($this->_request->getControllerName() . "/bdo/id/0");
    }

    public function rotaparadaAction()
    {
        $session = Escola_Session::getInstance();

        if (isset($session->id_rota)) {
            $id = $session->id_rota;
        } else {
            $id = $this->_request->getParam("id");
        }
        if ($id) {
            $session->id_rota = $id;
            $tb = new TbRota();
            $rota = $tb->getPorId($session->id_rota);
            if ($rota) {
                $transporte = $rota->findParentRow("TbTransporte");
                $this->view->rota = $rota;
                $this->view->transporte = $transporte;
                $page = $this->_getParam("page");
                $tb = new TbRotaParada();
                $this->view->registros = $tb->listar_por_pagina(array("id_rota" => $rota->getId(), "pagina_atual" => $page));
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE TRÃNSITO > ROTAS > PONTOS DE ÔNIBUS");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarparada",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0),
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "rota",
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarparadaAction()
    {
        $session = Escola_Session::getInstance();
        $id = $this->_request->getParam("id");
        $tb = new TbRotaParada();
        if ($id) {
            $registro = $tb->getPorId($id);
            $rota = $registro->findParentRow("TbRota");
        } else {
            $registro = $tb->createRow();
            $rota = TbRota::pegaPorId($session->id_rota);
        }
        $tb = new TbOnibusParada();
        $ops = $tb->listar();
        if (!$ops || !count($ops)) {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, NENHUM PONTO DE ÔNIBUS CADASTRADO!");
            $this->_redirect($this->_request->getControllerName() . "/rotaparada/id/0");
            die();
        }
        $transporte = $rota->findParentRow("TbTransporte");
        $this->view->rota = $rota;
        $this->view->transporte = $transporte;
        if ($this->_request->isPost()) {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            try {
                $dados = $this->_request->getPost();
                $dados["id_rota"] = $rota->getId();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors) {
                    throw new Exception(implode("<br />", $errors));
                } else {
                    $registro->save();
                }
                $db->commit();
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $this->_redirect($this->_request->getControllerName() . "/rotaparada/id/0");
            } catch (Exception $ex) {
                $db->rollBack();
                $this->view->actionErrors[] = $ex->getMessage();
            }
        }
        $this->view->registro = $registro;
        $this->view->ultima_ordem = TbRotaParada::pega_ultima_ordem($rota);
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE TRANSPORTES > ROTA > PONTO DE ÔNIBUS- ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE TRANSPORTES > ROTA > PONTO DE ÔNIBUS - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "rotaparada",
            "img" => "icon-remove-circle",
            "params" => array("id" => 0),
        ));
    }

    public function excluirparadaAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbRotaParada();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $errors = $registro->getDeleteErrors();
                if (!$errors) {
                    $flag = $registro->delete();
                    if ($flag) {
                        $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    } else {
                        $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
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
        $this->_redirect($this->_request->getControllerName() . "/rotaparada/id/0");
    }

    public function viewparadaAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbRotaParada();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;

                $rota = $transporte = false;

                $rota = $registro->findParentRow("TbRota");
                if ($rota) {
                    $transporte = $rota->findParentRow("TbTransporte");
                }

                $this->view->rota = $rota;
                $this->view->transporte = $transporte;

                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR TRANSPORTE > ROTA > PONTO DE ÔNIBUS");

                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "rotaparada",
                    "img" => "icon-reply",
                    "params" => array("id" => 0),
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

    public function cancelarpagamentoAction()
    {
        try {
            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Dados INVÁLIDOS!");
            }
            $tb = new TbServicoSolicitacao();
            $registro = $tb->getPorId($id);
            if (!$registro) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Dados INVÁLIDOS!!");
            }
            if (!$registro->getId()) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Dados INVÁLIDOS!!!");
            }
            $registro->cancelar_pagamento();

            $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
        }
        $this->_redirect($this->_request->getControllerName() . "/licenca");
    }

    public function ativarAction()
    {
        try {
            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Pessoa Não Localizada!");
            }

            $tp = TbTransportePessoa::pegaPorId($id);
            if (!$tp) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Pessoa Não Localizada!");
            }

            $tpt = $tp->findParentRow("TbTransportePessoaTipo");
            if (!$tpt) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Tipo de Pessoa Não Encontrado!");
            }

            // if (!$tpt->proprietario()) {
            //     throw new Exception("Falha ao Executar OPERAÇÃO, Pessoa Não é Permissionário!");
            // }

            if ($tp->ativo()) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Pessoa Já Está Ativa!");
            }

            $tp->ativar();

            $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
        }
        $this->_redirect($this->_request->getControllerName() . "/pessoa");
    }

    public function desativarAction()
    {
        try {
            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Pessoa Não Localizada!");
            }

            $tp = TbTransportePessoa::pegaPorId($id);
            if (!$tp) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Pessoa Não Localizada!");
            }

            $tpt = $tp->findParentRow("TbTransportePessoaTipo");
            if (!$tpt) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Tipo de Pessoa Não Encontrado!");
            }

            // if (!$tpt->proprietario()) {
            //     throw new Exception("Falha ao Executar OPERAÇÃO, Pessoa Não é Permissionário!");
            // }

            if (!$tp->ativo()) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Pessoa Já Está Inativa!");
            }

            $tp->desativar();

            $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
        }
        $this->_redirect($this->_request->getControllerName() . "/pessoa");
    }

    public function licencaeditarAction()
    {
        try {
            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("Falha ao Executar OPERAÇÃO, SOLICITAÇÃO de SERVIÇO Não Localizada!");
            }

            $ss = TbServicoSolicitacao::pegaPorId($id);
            if (!$ss) {
                throw new Exception("Falha ao Executar OPERAÇÃO, SOLICITAÇÃO de SERVIÇO Não Localizada!");
            }
            if ($ss->pago()) {
                throw new Exception("Falha ao Executar OPERAÇÃO, SOLICITAÇÃO de SERVIÇO Já Paga!");
            }

            $this->view->ss = $ss;

            $tb = new TbServicoTransporteGrupo();
            $stg = $ss->findParentRow("TbServicoTransporteGrupo");
            if (!$stg) {
                throw new Exception("Falha, Nenhum SERVIÇO DISPONÍVEL!");
            }
            $tg = $stg->findParentRow("TbTransporteGrupo");
            if (!$tg) {
                throw new Exception("Falha, Nenhum Grupo de Transporte DISPONÍVEL!");
            }
            $stgs = $tb->listar(
                array(
                    "servico_referencia_chave" => $ss->tipo,
                    "id_transporte_grupo" => $tg->getId(),
                )
            );
            if (!$stgs) {
                throw new Exception("Falha, Nenhum SERVIÇO DISPONÍVEL!");
            }

            $this->view->stgs = $stgs;
            $this->view->stg = $stg;

            $button = Escola_Button::getInstance();
            $button->setTitulo("ALTERAÇÃO DE SOLICITAÇÃO DE SERVIÇO!");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "licenca",
                "img" => "icon-reply",
                "params" => array("id" => 0),
            ));

            if ($this->_request->isPost()) {
                $db = Zend_Registry::get("db");
                $db->beginTransaction();
                try {
                    $dados = $this->_request->getPost();
                    $ss->setFromArray($dados);
                    $errors = $ss->getErrors();
                    if ($errors) {
                        throw new Exception(implode("<br />", $errors));
                    } else {
                        $ss->save();
                    }
                    $db->commit();
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/licenca");
                } catch (Exception $ex) {
                    $db->rollBack();
                    $this->view->actionErrors[] = $ex->getMessage();
                }
            }
        } catch (Exception $ex) {
            $this->addErro($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/licenca");
        }
    }

    public function licencacancelarAction()
    {
        $id = $this->getParam("id");
        try {
            if (!$id) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Nenhum Registro Localizado!");
            }
            $ss = TbServicoSolicitacao::pegaPorId($id);
            if (!$ss) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Nenhum Registro Localizado!");
            }
            if (!$ss->aguardando_pagamento()) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Status Inv?lido para Cancelamento!");
            }
            $ss->cancelar();

            $this->addMensagem("OPERAÇÃO Efetuada com Sucesso!");
        } catch (Exception $ex) {
            $this->addErro($ex->getMessage());
        }
        $this->_redirect($this->_request->getControllerName() . "/licenca");
    }
}
