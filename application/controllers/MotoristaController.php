<?php

class MotoristaController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("listarporpagina", "json");
        $ajaxContext->initContext();
    }

    public function listarporpaginaAction()
    {
        $dados = $this->getRequest()->getPost();
        $tb = new TbMotorista();
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
                $pm = $registro->findParentRow("TbPessoaMotorista");
                $pf = $pm->findParentRow("TbPessoaFisica");
                $cnh_categoria = $pm->findParentRow("TbCnhCategoria");
                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->matricula = $registro->matricula;
                $obj->cpf = Escola_Util::formatCpf($pf->cpf);
                $obj->nome = $pf->nome;
                $obj->cnh_numero = $pm->cnh_numero;
                $obj->cnh_categoria = $cnh_categoria->toString();
                $obj->cnh_validade = Escola_Util::formatData($pm->cnh_validade);
                $items[] = $obj;
            }
            $this->view->items = $items;
        }
    }

    public function indexAction()
    {
        $this->removeGrupoValor();

        $sessao = Escola_Session::getInstance();
        if (isset($sessao->id_motorista)) {
            unset($sessao->id_motorista);
        }
        $this->view->dados = $sessao->atualizaFiltros(array("filtro_matricula", "filtro_cpf", "filtro_nome", "filtro_id_transporte_grupo"));
        $this->view->dados["pagina_atual"] = $this->_getParam("page");
        $tb = new TbMotorista();
        $this->view->registros = $tb->listar_por_pagina($this->view->dados);
        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE MOTORISTAS");
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
        $tb = new TbTransporteGrupo();
        $tgs = $tb->listar();
        if (!$tgs) {
            $this->_flashMessage("NENHUM GRUPO DE TRANSPORTE CADASTRADO!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }

        $id = $this->_request->getParam("id");
        $tb = new TbMotorista();
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
            $button->setTitulo("CADASTRO DE MOTORISTA - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE MOTORISTA - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbMotorista();
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
            $tb = new TbMotorista();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->pm = $registro->findParentRow("TbPessoaMotorista");
                $this->view->pf = $this->view->pm->findParentRow("TbPessoaFisica");
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR MOTORISTA");
                $button->addFromArray(array(
                    "titulo" => "Excluir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "excluir",
                    "img" => "icon-trash",
                    "class" => "link_excluir",
                    "params" => array("id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Serviços",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "licenca",
                    "img" => "icon-copy",
                    "params" => array(
                        "id" => 0,
                        "id_motorista" => $id
                    )
                ));
                $button->addFromArray(array(
                    "titulo" => "Relatórios",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "relatorio",
                    "img" => "icon-print",
                    "params" => array(
                        "id" => 0,
                        "id_motorista" => $id
                    )
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

    /*
      public function carteiraAction() {
      $sessao = Escola_Session::getInstance();
      $id_motorista = $this->_getParam("id_motorista");
      if ($id_motorista) {
      $motorista = TbMotorista::pegaPorId($id_motorista);
      if ($motorista) {
      $motorista->atualizaCarteiras();
      $tb = new TbServicoSolicitacao();
      $this->view->registros = $tb->listar_por_pagina(array("tipo" => "MO", "chave" => $motorista->getId(), "pagina_atual" => $this->_getParam("page")));
      $this->view->motorista = $motorista;
      $button = Escola_Button::getInstance();
      $button->setTitulo("CARTEIRAS");
      $button->addFromArray(array("titulo" => "Voltar",
      "controller" => $this->_request->getControllerName(),
      "action" => "index",
      "img" => "icon-reply",
      "params" => array("id" => 0, "id_transporte" => 0)));
      } else {
      $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
      $this->_redirect($this->_request->getControllerName() . "/index");
      }
      } else {
      $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
      $this->_redirect($this->_request->getControllerName() . "/index");
      }
      }

      public function boletoAction() {
      $id = $this->_getParam("id");
      if ($id) {
      $ss = TbServicoSolicitacao::pegaPorId($id);
      if ($ss) {
      $this->_redirect("transporte/boleto/id/{$id_ss}");
      die();
      } else {
      $this->_flashMessage("NENHUMA SOLICITAÇÃO DISPONÍVEL PARA EMISSÃO, CHAME O ADMINISTRADOR!");
      }
      } else {
      $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
      }
      $this->_redirect($this->_request->getControllerName() . "/index");
      }

      public function pgtoAction() {
      $id = $this->_request->getParam("id");
      if ($id) {
      $tb = new TbServicoSolicitacao();
      $ss = $tb->getPorId($id);
      if ($ss) {
      if ($ss->motorista()) {
      if (!$ss->pago()) {
      $this->view->registro = $ss;
      $this->view->stg = $ss->findParentRow("TbServicoTransporteGrupo");
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
      $this->_redirect($this->_request->getControllerName() . "/carteira/id/0/id_motorista/{$ss->chave}");
      } else {
      $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
      $this->_redirect($this->_request->getControllerName() . "/carteira/id/0/id_motorista/{$ss->chave}");
      }
      } else {
      $this->view->actionErrors = $errors;
      }
      }
      $button = Escola_Button::getInstance();
      $button->setTitulo("MOTORISTA > PAGAMENTO DE TAXA DE CARTEIRA DE MOTORISTA");
      $button->addScript("Confirmar Pagamento", "salvarFormulario('formulario')", "icon-save");
      $button->addFromArray(array("titulo" => "Voltar",
      "controller" => $this->_request->getControllerName(),
      "action" => "carteira",
      "img" => "icon-reply",
      "params" => array("id" => 0, "id_motorista" => $ss->chave)));
      } else {
      $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
      $this->_redirect($this->_request->getControllerName() . "/carteira/id/0");
      }
      } else {
      $this->_flashMessage("SOLICITAÇÃO PRECISA SER DO TIPO MOTORISTA!");
      $this->_redirect($this->_request->getControllerName() . "/carteira/id/0");
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

      public function emitirAction() {
      $id = $this->_request->getParam("id");
      if ($id) {
      $ss = TbServicoSolicitacao::pegaPorId($id);
      if ($ss) {
      $motorista = $ss->pegaReferencia();
      if ($motorista) {
      $motorista->toPDF();
      }
      } else {
      $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
      $this->_redirect($this->_request->getControllerName() . "/carteira/id_motorista/{$ss->chave}");
      }
      } else {
      $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
      $this->_redirect($this->_request->getControllerName() . "/index");
      }
      }
     */

    public function licencaAction()
    {
        $id_motorista = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_motorista) && $session->id_motorista) {
            $id_motorista = $session->id_motorista;
        } else {
            $id_motorista = $this->_request->getParam("id_motorista");
        }

        if (!$id_motorista) {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
            return;
        }

        $motorista = TbMotorista::pegaPorId($id_motorista);
        if (!$motorista) {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!!");
            $this->_redirect($this->_request->getControllerName() . "/index");
            return;
        }

        $session->id_motorista = $motorista->getId();
        $dados = $session->atualizaFiltros(array("filtro_id_servico", "filtro_ano_referencia", "filtro_mes_referencia", "filtro_id_servico_solicitacao_status"));
        $dados["tipo"] = "MO";
        $dados["chave"] = $motorista->getId();
        $this->view->motorista = $motorista;
        $dados["pagina_atual"] = $this->_getParam("page");
        $tb = new TbServicoSolicitacao();
        $this->view->registros = $tb->listar_por_pagina($dados);
        $this->view->dados = $dados;
        $this->view->pessoa_motorista = false;
        $this->view->pessoa_fisica = false;
        $pm = $motorista->findParentRow("TbPessoaMotorista");
        if ($pm) {
            $this->view->pessoa_motorista = $pm;
            $pf = $pm->findParentRow("TbPessoaFisica");
            if ($pf) {
                $this->view->pessoa_fisica = $pf;
            }
        }
        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE MOTORISTA > SERVIÇOS");
        $button->addFromArray(array(
            "titulo" => "Boleto Único",
            "onclick" => "boleto()",
            "img" => "icon-credit-card",
            "params" => array("id" => 0)
        ));
        $button->addFromArray(array(
            "titulo" => "Adicionar",
            "controller" => $this->_request->getControllerName(),
            "action" => "addservico",
            "img" => "icon-plus-sign",
            "params" => array("id" => 0, "id_motorista" => $motorista->getId())
        ));
        $button->addFromArray(array(
            "titulo" => "Pesquisar",
            "onclick" => "pesquisar()",
            "img" => "icon-search",
            "params" => array("id" => 0)
        ));
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "index",
            "img" => "icon-reply",
            "params" => array("id" => 0)
        ));
    }

    public function boletovencimentoAction()
    {
        try {
            $id_motorista = 0;
            $session = Escola_Session::getInstance();
            if (isset($session->id_motorista) && $session->id_motorista) {
                $id_motorista = $session->id_motorista;
            } else {
                $id_motorista = $this->_request->getParam("id_motorista");
            }
            if (!$id_motorista) {
                throw new UnexpectedValueException("Nenhum Motorista Selecionado!");
            }
            $motorista = TbMotorista::pegaPorId($id_motorista);
            if (!$motorista) {
                throw new UnexpectedValueException("Nenhum Motorista Selecionado!");
            }
            if (!$this->_request->isPost()) {
                throw new Exception("Nenhuma Informação Recebida!");
            }

            $pessoa = false;
            $pm = $motorista->findParentRow("TbPessoaMotorista");
            if ($pm) {
                $pf = $pm->findParentRow("TbPessoaFisica");
                if ($pf) {
                    $pessoa = $pf->findParentRow("TbPessoa");
                }
            }
            if (!$pessoa) {
                throw new Exception("Nenhuma Pessoa Selecionada!");
            }

            $dados = $this->_request->getPost();
            $ids = array();
            $tb = new TbServicoSolicitacao();
            if (isset($dados["lista"]) && is_array($dados["lista"]) && count($dados["lista"])) {
                foreach ($dados["lista"] as $id) {
                    $ss = $tb->pegaPorId($id);
                    if ($ss) {
                        $ids[] = $ss;
                    }
                }
            } elseif (isset($dados["id_motorista"]) && $dados["id_motorista"]) {
                $sss = $tb->listar(array("tipo" => "MO", "chave" => $dados["id_motorista"]));
                if ($sss && count($sss)) {
                    foreach ($sss as $ss) {
                        if ($ss->aguardando_pagamento()) {
                            $ids[] = $ss;
                        }
                    }
                }
            }
            if (!$ids) {
                throw new Exception("Nenhum Boleto Disponível!");
            }
            $data_vencimento = new Zend_Date();
            $valor_total = 0;
            foreach ($ids as $ss) {
                $vencimento = new Zend_Date($ss->data_vencimento);
                if ($vencimento < $data_vencimento) {
                    $data_vencimento = $vencimento;
                }
                $valor = $ss->pega_valor();
                if ($valor) {
                    $valor_total += $valor->valor;
                }
            }
            $this->view->data_vencimento = Escola_Util::formatData($data_vencimento->toString("dd/MM/YYYY"));
            $this->view->valor_total = Escola_Util::number_format($valor_total);
            $this->view->ids = $ids;
            $this->view->motorista = $motorista;
            $this->view->pessoa = $pessoa;
            $button = Escola_Button::getInstance();
            $button->setTitulo("VENCIMENTO DO BOLETO");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "licenca",
                "img" => "icon-reply",
                "params" => array("id" => 0)
            ));
        } catch (UnexpectedValueException $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect("motorista");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect("motorista/licenca");
        }
    }

    public function addservicoAction()
    {
        $id_motorista = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_motorista) && $session->id_motorista) {
            $id_motorista = $session->id_motorista;
        } else {
            $id_motorista = $this->_request->getParam("id_motorista");
        }
        if ($id_motorista) {
            $motorista = TbMotorista::pegaPorId($id_motorista);
            if ($motorista) {
                $tipo = "MO";
                $chave = $motorista->getId();
                $this->view->motorista = $motorista;
                $tb = new TbServicoReferencia();
                $sr = $tb->getPorChave("TP");
                if ($sr) {
                    $this->view->referencia = $sr;
                    $tb = new TbServicoTransporteGrupo();
                    $stgs = $tb->listar(array("id_transporte_grupo" => $motorista->id_transporte_grupo, "id_servico_referencia" => $sr->getId()));
                    if (!$stgs || !count($stgs)) {
                        $this->_flashMessage("NENHUM SERVIÇO DISPONÍVEL PARA O TRANSPORTE!");
                        $this->_redirect($this->_request->getControllerName() . "/licenca/id/0");
                    }
                    $this->view->stgs = $stgs;
                    if ($this->_request->isPost()) {
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
                        if (!$errors) {
                            $ss->save();
                            $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Message");
                            $this->_redirect($this->_request->getControllerName() . "/licenca/id/0");
                        } else {
                            $this->view->actionErrors = $errors;
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
                        "params" => array("id" => 0)
                    ));
                } else {
                    $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
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
    }

    public function solicitacaoservicodeleteAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $ss = TbServicoSolicitacao::pegaPorId($id);
            if ($ss) {
                $errors = $ss->getDeleteErrors();
                if ($errors) {
                    foreach ($errors as $erro) {
                        $this->addErro($erro);
                    }
                } else {
                    $ss->delete();
                    $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
                }
            } else {
                $this->addErro("NENHUMA SOLICITAÇÃO DE SERVIÇO DISPONÍVEL!");
            }
        } else {
            $this->addErro("NENHUMA SOLICITAÇÃO DE SERVIÇO DISPONÍVEL!");
        }
        $this->_redirect($this->_request->getControllerName() . "/licenca/id/0");
    }

    public function viewlicencaAction()
    {
        $id_motorista = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_motorista) && $session->id_motorista) {
            $id_motorista = $session->id_motorista;
        } else {
            $id_motorista = $this->_request->getParam("id_motorista");
        }
        if ($id_motorista) {
            $motorista = TbMotorista::pegaPorId($id_motorista);
            if ($motorista) {
                $id = $this->_request->getParam("id");
                if ($id) {
                    $tb = new TbServicoSolicitacao();
                    $ss = $tb->getPorId($id);
                    if ($ss) {
                        $transporte = false;
                        $this->view->registro = $ss;
                        $this->view->motorista = $motorista;
                        $this->view->pagamento = $ss->pegaPagamento();
                        $button = Escola_Button::getInstance();
                        $button->setTitulo("VISUALIZAR MOTORISTA > SERVIÇO");
                        if ($ss->pago()) {
                            if ($ss->valido()) {
                                $button->addFromArray(array(
                                    "titulo" => "Emitir Documento",
                                    "controller" => $this->_request->getControllerName(),
                                    "action" => "emitir",
                                    "img" => "icon-print",
                                    "params" => array("id_transporte" => $ss->chave, "id" => $ss->getId())
                                ));
                            }
                        } else {
                            $button->addFromArray(array(
                                "titulo" => "Gerar Boleto",
                                "controller" => $this->_request->getControllerName(),
                                "action" => "boleto",
                                "img" => "icon-credit-card",
                                "params" => array("target" => "_blank", "id_transporte" => $ss->chave, "id" => $ss->getId())
                            ));
                            $button->addFromArray(array(
                                "titulo" => "Confirmar Pagamento",
                                "controller" => $this->_request->getControllerName(),
                                "action" => "licencapgto",
                                "img" => "icon-money",
                                "params" => array("id_transporte" => $ss->chave, "id" => $ss->getId())
                            ));
                        }
                        $button->addFromArray(array(
                            "titulo" => "Voltar",
                            "controller" => $this->_request->getControllerName(),
                            "action" => "licenca",
                            "img" => "icon-reply",
                            "params" => array("id_transporte" => $ss->chave, "id" => 0)
                        ));
                    } else {
                        $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
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
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function licencapgtoAction()
    {
        $id_motorista = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_motorista) && $session->id_motorista) {
            $id_motorista = $session->id_motorista;
        } else {
            $id_motorista = $this->_request->getParam("id_motorista");
        }
        if ($id_motorista) {
            $motorista = TbMotorista::pegaPorId($id_motorista);
            if ($motorista) {
                $id = $this->_request->getParam("id");
                if ($id) {
                    $tb = new TbServicoSolicitacao();
                    $ss = $tb->getPorId($id);
                    if ($ss) {
                        if (!$ss->pago()) {
                            $this->view->registro = $ss;
                            $this->view->motorista = $motorista;
                            $this->view->stg = $ss->findParentRow("TbServicoTransporteGrupo");
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
                                        $this->_redirect($this->_request->getControllerName() . "/licenca/id/0");
                                    } else {
                                        $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
                                        $this->_redirect($this->_request->getControllerName() . "/licenca/id/0");
                                    }
                                } else {
                                    $this->view->actionErrors = $errors;
                                }
                            }
                            $button = Escola_Button::getInstance();
                            $button->setTitulo("VISUALIZAR MOTORISTA > SERVIÇO > PAGAMENTO");
                            $button->addScript("Confirmar Pagamento", "salvarFormulario('formulario')", "icon-save");
                            $button->addFromArray(array(
                                "titulo" => "Voltar",
                                "controller" => $this->_request->getControllerName(),
                                "action" => "licenca",
                                "img" => "icon-reply",
                                "params" => array("id" => 0, "id_transporte" => $ss->chave)
                            ));
                        } else {
                            $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO JÁ PAGA!");
                            $this->_redirect($this->_request->getControllerName() . "/licenca/id/0");
                        }
                    } else {
                        $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
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
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function relatorioAction()
    {
        try {
            $session = Escola_Session::getInstance();
            if (isset($session->id_motorista) && $session->id_motorista) {
                $id = $session->id_motorista;
            } else {
                $id = $this->_request->getParam("id_motorista");
            }
            if (!$id) {
                throw new Exception("Falha ao Executar Operação, Dados Inválidos!");
            }
            $tb = new TbMotorista();
            $registro = $tb->getPorId($id);
            if (!$registro) {
                throw new Exception("Falha ao Executar Operação, Dados Inválidos!!");
            }
            $tb = new TbRelatorioTipo();
            $rt = $tb->getPorChave("Mo");
            if (!$rt) {
                throw new Exception("Falha ao Executar Operação, Dados Inválidos!!!");
            }
            $tb = new TbRelatorio();
            $relatorios = $tb->listar(array("filtro_id_relatorio_tipo" => $rt->getId()));
            if (!$relatorios) {
                throw new Exception("Falha ao Executar Operação, Dados Inválidos!!!!");
            }
            $dados = array("id_relatorio" => "", "tipo" => "");
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $dados["motorista"] = $registro;
                if (isset($dados["operacao"]) && ($dados["operacao"] == "imprimir")) {
                    $id_relatorio = 0;
                    if (isset($dados["id_relatorio"]) && $dados["id_relatorio"]) {
                        $id_relatorio = $dados["id_relatorio"];
                    }
                    $rel = TbRelatorio::getInstance($id_relatorio);
                    if ($rel) {
                        $rel->set_dados($dados);
                        $errors = $rel->validarEmitir();
                        if (!$errors) {
                            $this->view->rel = $rel;
                            $method = "to" . $dados["tipo"];
                            if (!method_exists($rel, $method)) {
                                $this->view->actionErrors[] = "FALHA AO GERAR RELATÓRIO, FORMATO INVÁLIDO!";
                            } else {
                                if (($dados["tipo"] == "XLS") || ($dados["tipo"] == "PDF")) {
                                    $rel->$method();
                                } else {
                                    $this->view->method = $method;
                                }
                            }
                        } else {
                            $this->view->actionErrors = $errors;
                        }
                        $this->view->errors = $errors;
                    } else {
                        $this->view->actionErrors[] = "FALHA AO GERAR RELATÓRIO!";
                    }
                }
            }

            $this->view->motorista = $registro;
            $this->view->relatorios = $relatorios;
            $this->view->dados = $dados;

            $button = Escola_Button::getInstance();
            $button->setTitulo("Relatórios Vinculados ao Motorista");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "index",
                "img" => "icon-reply",
                "params" => array("id" => 0)
            ));
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function licencaeditarAction()
    {
        try {
            $session = Escola_Session::getInstance();
            if (!(isset($session->id_motorista) && $session->id_motorista)) {
                throw new Exception("Falha, Nenhum Motorista Vinculado!");
            }
            $motorista = TbMotorista::pegaPorId($session->id_motorista);
            if (!$motorista) {
                throw new Exception("Falha, Nenhum Motorista Vinculado!!");
            }
            $tg = $motorista->getTransporteGrupo();
            if (!$tg) {
                throw new Exception("Falha, Nenhum Grupo de Transporte Vinculado!");
            }

            $id = $this->_request->getParam("id");
            if (!$id) {
                throw new Exception("Falha ao Executar Operação, Solicitação de Serviço Não Localizada!");
            }

            $ss = TbServicoSolicitacao::pegaPorId($id);
            if (!$ss) {
                throw new Exception("Falha ao Executar Operação, Solicitação de Serviço Não Localizada!");
            }
            if ($ss->pago()) {
                throw new Exception("Falha ao Executar Operação, Solicitação de Serviço Já Paga!");
            }

            $this->view->ss = $ss;

            $tb = new TbServicoTransporteGrupo();
            $dados = array(
                "id_transporte_grupo" => $tg->getId(),
                "servico_referencia_chave" => "TP"
            );
            $stgs = $tb->listar($dados);
            if (!$stgs) {
                throw new Exception("Falha, Nenhum Serviço Disponível!");
            }

            $this->view->stgs = $stgs;
            $this->view->stg = $ss->getServicoTransporteGrupo();

            $button = Escola_Button::getInstance();
            $button->setTitulo("ALTERAÇÃO DE SOLICITAÇÃO DE SERVIÇO!");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "licenca",
                "img" => "icon-reply",
                "params" => array("id" => 0)
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
}
