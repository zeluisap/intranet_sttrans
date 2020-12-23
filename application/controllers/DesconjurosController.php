<?php
class DesconjurosController extends Escola_Controller_Logado
{

    public function indexAction()
    {

        TbDesconjuros::carregaClassesDaPasta();

        $session = Escola_Session::getInstance();

        $dados = $session->atualizaFiltros(array("filtro"));

        $tb = new TbDesconjuros();
        $dados["pagina_atual"] = $this->_getParam("page");

        $this->view->registros = $tb->listar_por_pagina($dados);
        $this->view->dados = $dados;

        $button = Escola_Button::getInstance();
        $button->setTitulo("DESCONTOS / JUROS E MULTAS");

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
        $tb = new TbDesconjuros();
        if (!$id) {
            throw new Escola_Exception("Id não informado!");
        }

        $registro = $tb->getPorId($id);
        if (!$registro) {
            throw new Escola_Exception("Registro não localizado!");
        }

        $salvo = $this->salvar($registro);
        if ($salvo) {
            return;
        }

        $this->view->registro = $registro;

        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE DESCONTOS, JUROS E MULTAS - ALTERAR");
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function salvar($registro)
    {
        if (!$this->_request->isPost()) {
            return false;
        }

        try {
            $dados = $this->_request->getPost();
            $registro->setFromArray($dados);
            $errors = $registro->getErrors();

            if ($errors) {
                $this->view->actionErrors = $errors;
                return false;
            }

            Escola_DbUtil::inTransaction(function () use ($registro) {
                $registro->save();
            });

            $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
            $this->_redirect($this->_request->getControllerName() . "/index");

            return true;
        } catch (\Exception $ex) {
            $this->view->actionErrors[] = $ex->getMessage();
            return false;
        }
    }

    public function excluirAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbAgente();
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
            $tb = new TbAgente();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $funcionario = $registro->findParentRow("TbFuncionario");
                $pf = $funcionario->findParentRow("TbPessoaFisica");
                $this->view->registro = $registro;
                $this->view->funcionario = $funcionario;
                $this->view->pf = $pf;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR AGENTE");
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
                $button->addFromArray(array(
                    "titulo" => "Autos de Infração",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "autoinfracao",
                    "img" => "icon-warning-sign",
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

    public function autoinfracaoAction()
    {
        $id_agente = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_agente) && $session->id_agente) {
            $id_agente = $session->id_agente;
        } else {
            $id_agente = $this->_request->getParam("id_agente");
        }
        if ($id_agente) {
            $session->id_agente = $id_agente;
            $registro = TbAgente::pegaPorId($id_agente);
            if ($registro) {
                $tb = new TbAutoInfracao();
                $dados = $session->atualizaFiltros(array("filtro_id_servico_tipo", "filtro_caracter", "filtro_codigo_inicio", "filtro_codigo_final", "filtro_id_auto_infracao_status"));
                $dados["filtro_id_agente"] = $registro->getId();
                $dados["pagina_atual"] = $this->_getParam("page");
                $registros = $tb->listar_por_pagina($dados);
                $this->view->registro = $registro;
                $this->view->registros = $registros;
                $this->view->dados = $dados;
                $button = Escola_Button::getInstance();
                $button->setTitulo("AGENTE > AUTO DE INFRAÇÃO");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "add",
                    "img" => "icon-plus-sign",
                    "params" => array("id_agente" => $registro->getId(), "id" => $registro->getId())
                ));
                $button->addFromArray(array(
                    "titulo" => "Pesquisar",
                    "onclick" => "pesquisar()",
                    "img" => "icon-search",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Devolver",
                    "onclick" => "devolver()",
                    "img" => "icon-download-alt",
                    "class" => "btn_devolver hide",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => "0", "id_agente" => "0")
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

    public function addAction()
    {
        $id_agente = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_agente) && $session->id_agente) {
            $id_agente = $session->id_agente;
        } else {
            $id_agente = $this->_request->getParam("id_agente");
        }
        if ($id_agente) {
            $registro = TbAgente::pegaPorId($id_agente);
            if ($registro) {
                $this->view->registro = $registro;
                if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    if (isset($dados["id_agente"])) {
                        unset($dados["id_agente"]);
                    }
                    $tb = new TbAutoInfracao();
                    $rs = $tb->listar($dados);
                    if ($rs && count($rs)) {
                        $contador = 0;
                        foreach ($rs as $ai) {
                            $flag = $ai->setAgente($registro);
                            if ($flag) {
                                $contador++;
                            }
                        }
                    } else {
                        $this->_flashMessage("NENHUM AUTO DE INFRAÇÃO SELECIONADO!");
                    }
                    $this->_redirect($this->_request->getControllerName() . "/autoinfracao/id_agente/{$registro->getId()}/id/0");
                }
                $button = Escola_Button::getInstance();
                $button->setTitulo("AGENTE > AUTO DE INFRAÇÃO > ADICIONAR");
                $button->addScript("Vincular", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "autoinfracao",
                    "img" => "icon-reply",
                    "params" => array("id" => "0", "id_agente" => $id_agente)
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

    public function viewautoinfracaoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbAutoInfracao();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->ocorrencias = $registro->listar_ocorrencias();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR AGENTE > AUTO-INFRAÇÃO");
                if ($registro->entregue()) {
                    $button->addFromArray(array(
                        "titulo" => "Excluir",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "excluirautoinfracao",
                        "img" => "icon-trash",
                        "class" => "link_excluir",
                        "params" => array("id" => $id)
                    ));
                }
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "autoinfracao",
                    "img" => "icon-reply",
                    "params" => array("id_agente" => $registro->id_agente, "id" => "0")
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

    public function excluirautoinfracaoAction()
    {
        $id_agente = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_agente) && $session->id_agente) {
            $id_agente = $session->id_agente;
        } else {
            $id_agente = $this->_request->getParam("id_agente");
        }
        $id = $this->_request->getParam("id");
        if ($id_agente && $id) {
            $registro = TbAgente::pegaPorId($id_agente);
            $auto = TbAutoInfracao::pegaPorId($id);
            if ($registro && $auto) {
                if ($auto->entregue()) {
                    if ($registro->getId() == $auto->id_agente) {
                        $flag = $auto->cancelar_entrega();
                        if ($flag) {
                            $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                        } else {
                            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                        }
                    } else {
                        $this->_flashMessage("AUTO DE INFRAÇÃO NÃO VINCULADO AO AGENTE!");
                    }
                } else {
                    $this->_flashMessage("STATUS DO AUTO DE INFRAÇÃO INVÁLIDO PARA EXCLUSÃO!");
                }
            } else {
                $this->_flashMessage("DADOS INVÁLIDOS!");
            }
        } else {
            $this->_flashMessage("DADOS INVÁLIDOS!");
        }
        $this->_redirect($this->_request->getControllerName() . "/autoinfracao/id_agente/{$id_agente}/id/0");
    }

    public function devolveroldAction()
    {
        $errors = array();
        $id_agente = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_agente) && $session->id_agente) {
            $id_agente = $session->id_agente;
        } else {
            $id_agente = $this->_request->getParam("id_agente");
        }
        $id = $this->_request->getParam("id");
        if ($id_agente) {
            $agente = TbAgente::pegaPorId($id_agente);
            if ($agente) {
                $ids = array();
                if ($id) {
                    $ids[] = $id;
                }
                if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    if (isset($dados["flag"]) && ($dados["flag"] == "salvar")) {
                        if (isset($dados["result"]) && is_array($dados["result"]) && count($dados["result"])) {
                            $contador = 0;
                            foreach ($dados["result"] as $id_auto_infracao =>  $result) {
                                $ai = TbAutoInfracao::pegaPorId($id_auto_infracao);
                                if ($ai && $ai->entregue()) {
                                    $flag = $ai->devolver(array(
                                        "id_auto_infracao_devolucao_status" => $result["aids"],
                                        "observacoes" => $result["obs"]
                                    ));
                                    if ($flag) {
                                        $contador++;
                                    }
                                }
                            }
                        }
                        if ($contador) {
                            $this->_flashMessage("{$contador} AUTO(S) DE INFRAÇÃO(ÕES) DEVOLVIDO(S).", "Messages");
                        } else {
                            $this->_flashMessage("NENHUM AUTO DE INFRAÇÃO DEVOLVIDO!");
                        }
                        $this->_redirect($this->_request->getControllerName() . "/autoinfracao/id_agente/{$id_agente}/id/0");
                    } else {
                        if (isset($dados["lista_id_auto_infracao"]) && is_array($dados["lista_id_auto_infracao"]) && count($dados["lista_id_auto_infracao"])) {
                            $ids = array_merge($ids, $dados["lista_id_auto_infracao"]);
                        }
                    }
                }
                if (count($ids)) {
                    $this->view->agente = $agente;
                    $this->view->ids = $ids;
                    $tb = new TbAutoInfracaoDevolucaoStatus();
                    $this->view->lista_aids = $tb->listar();
                    $button = Escola_Button::getInstance();
                    $button->setTitulo("AGENTE > DEVOLVER AUTO DE INFRAÇÃO");
                    $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                    $button->addFromArray(array(
                        "titulo" => "Voltar",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "autoinfracao",
                        "img" => "icon-reply",
                        "params" => array("id_agente" => $agente->getId(), "id" => "0")
                    ));
                } else {
                    $errors[] = "NENHUM AUTO DE INFRAÇÃO SELECIONADO!";
                }
            } else {
                $errors[] = "DADOS INVÁLIDOS!";
            }
        } else {
            $errors[] = "DADOS INVÁLIDOS!";
        }
        if (count($errors)) {
            foreach ($errors as $erro) {
                $this->_flashMessage($erro);
            }
            $this->_redirect($this->_request->getControllerName() . "/autoinfracao/id_agente/{$id_agente}/id/0");
        }
    }

    public function viewnotificacaoAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_agente)) {
            $id_agente = $session->id_agente;
        }
        $agente = TbAgente::pegaPorId($id_agente);
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbAutoInfracaoNotificacao();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $ocorrencia = $registro->pegaOcorrencia();
                $ai = false;
                if ($ocorrencia) {
                    $ai = $ocorrencia->findParentRow("TbAutoInfracao");
                }
                if ($ai) {
                    $this->view->ocorrencia = $ocorrencia;
                    $this->view->ai = $ai;
                    $this->view->agente = $agente;
                    $button = Escola_Button::getInstance();
                    $button->setTitulo("VISUALIZAR OCORRÊNCIA DE AUTO-INFRAÇÃO > NOTIFICAÇÃO");
                    $button->addFromArray(array(
                        "titulo" => "Voltar",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "viewautoinfracao",
                        "img" => "icon-reply",
                        "params" => array("id" => $ai->getId())
                    ));
                } else {
                    $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                    $this->_redirect($this->_request->getControllerName() . "/index");
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

    public function devolverAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $registro = TbAutoInfracao::pegaPorId($id);
            if ($registro) {
                if ($registro->entregue()) {
                    $agente = $registro->findParentRow("TbAgente");
                    if ($agente) {
                        if ($this->_request->isPost()) {
                            $dados = $this->_request->getPost();
                            $dados["arquivo"] = Escola_Util::getUploadedFile("arquivo");
                            if (isset($dados["id_auto_infracao_devolucao_status"]) && $dados["id_auto_infracao_devolucao_status"]) {
                                $aids = TbAutoInfracaoDevolucaoStatus::pegaPorId($dados["id_auto_infracao_devolucao_status"]);
                                if ($aids) {
                                    if (!$aids->ok() && (!isset($dados["observacoes"]) || !$dados["observacoes"])) {
                                        $this->_flashMessage("CAMPO OBSERVAÇÕES OBRIGATÓRIO PARA A DEVOLUÇÃO [{$aids->toString()}]!");
                                    } else {
                                        if (isset($dados["id_infracao"]) && is_array($dados["id_infracao"]) && count($dados["id_infracao"])) {
                                            $flag = $registro->devolver($dados);
                                            if ($flag) {
                                                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                                            } else {
                                                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO!");
                                            }
                                        }
                                    }
                                } else {
                                    $this->_flashMessage("CAMPO AUTO DE INFRAÇÃO OBRIGATÓRIO!");
                                }
                            } else {
                                $this->_flashMessage("CAMPO AUTO DE INFRAÇÃO OBRIGATÓRIO!");
                            }
                            $this->_redirect($this->_request->getControllerName() . "/autoinfracao");
                        }
                        $this->view->registro = $registro;
                        $this->view->agente = $agente;
                        $notificacao = false;
                        //if ($registro->findParentRow("TbServicoTipo")->transporte()) {
                        $tb = new TbAutoInfracaoNotificacao();
                        $notificacao = $tb->createRow();
                        //}
                        $this->view->notificacao = $notificacao;
                        $button = Escola_Button::getInstance();
                        $button->setTitulo("DEVOLVER AUTO DE INFRAÇÃO");
                        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                        $button->addFromArray(array(
                            "titulo" => "Voltar",
                            "controller" => $this->_request->getControllerName(),
                            "action" => "autoinfracao",
                            "img" => "icon-reply",
                            "params" => array("id" => "0")
                        ));
                    } else {
                        $this->_flashMessage("NENHUM AGENTE ATRIBUÍDO AO AUTO DE INFRAÇÃO!");
                        $this->_redirect($this->_request->getControllerName() . "/index");
                    }
                } else {
                    $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
}
