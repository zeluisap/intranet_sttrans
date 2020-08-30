<?php

class InterdicaoController extends Escola_Controller_Logado {

    public function indexAction() {
        $tb = new TbServico();
        $servico = $tb->getPorCodigo("AI");
        if ($servico) {
            $this->view->servico = $servico;
            $tb = new TbServicoTransporteGrupo();
            $stgs = $tb->listar(array("id_servico" => $servico->getId()));
            if ($stgs) {
                $stg = $stgs->current();
                $this->view->stg = $stg;
                $session = Escola_Session::getInstance();
                $dados = $session->atualizaFiltros(array("filtro_titulo", "filtro_id_pessoa_tipo", "filtro_cpf", "filtro_cnpj", "filtro_nome", "filtro_id_servico_solicitacao_status"));
                $dados["tipo"] = "IN";
                $dados["pagina_atual"] = $this->_getParam("page");
                $tb = new TbInterdicao();
                $this->view->registros = $tb->listar_por_pagina($dados);
                $this->view->dados = $dados;
                $button = Escola_Button::getInstance();
                $button->setTitulo("AUTORIZAÇÕES DE INTERDIÇÃO");
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
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect("intranet/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect("intranet/index");
        }
    }

    public function viewAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbInterdicao();
            $interdicao = $tb->getPorId($id);
            if ($interdicao) {
                $registro = $interdicao;
                $this->view->registro = $registro;
                $ss = $interdicao->pegaSolicitacao();
                $this->view->ss = $ss;
                $this->view->pagamento = $ss->pegaPagamento();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR AUTORIZAÇÃO DE INTERDIÇÃO");
                if ($ss->pago()) {
                    if ($ss->valido()) {
                        $button->addFromArray(array("titulo" => "Emitir Documento",
                            "controller" => $this->_request->getControllerName(),
                            "action" => "emitir",
                            "img" => "icon-print",
                            "params" => array("id" => $registro->getId())));
                    }
                } else {
                    $button->addFromArray(array("titulo" => "Alterar",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "editar",
                        "img" => "icon-cog",
                        "params" => array("id" => $registro->getId())));
                    $button->addFromArray(array("titulo" => "Excluir",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "excluir",
                        "img" => "icon-trash",
                        "class" => "link_excluir",
                        "params" => array("id" => $registro->getId())));
                    $button->addFromArray(array("titulo" => "Gerar Boleto",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "boleto",
                        "img" => "icon-credit-card",
                        "params" => array("target" => "_blank", "id" => $ss->getId())));
                    $button->addFromArray(array("titulo" => "Confirmar Pagamento",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "licencapgto",
                        "img" => "icon-money",
                        "params" => array("id" => $registro->getId())));
                }
                $button->addFromArray(array("titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => $registro->getId())));
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function licencapgtoAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $interdicao = TbInterdicao::pegaPorId($id);
            if ($interdicao) {
                $ss = $interdicao->pegaSolicitacao();
                if ($ss) {
                    if (!$ss->pago()) {
                        $this->view->interdicao = $interdicao;
                        $this->view->pessoa = $interdicao->findParentRow("TbPessoa");
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
                        $button->setTitulo("PAGAMENTO DE AUTORIZAÇÃO DE INTERDIÇÃO");
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

    public function boletoAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $interdicao = TbInterdicao::pegaPorId($id);
            if ($interdicao) {
                $registro = $interdicao->pegaSolicitacao();
                if ($registro) {
                    if (!$registro->pago()) {
                        $ref = $registro->pegaReferencia();
                        $pessoa = $interdicao->findParentRow("TbPessoa");
                        if ($pessoa) {
                            $stg = $registro->findParentRow("TbServicoTransporteGrupo");
                            $bc = TbBancoConvenio::pegaPadrao();
                            if ($bc) {
                                $ib = $bc->pega_info_bancaria();
                                $banco = $ib->findParentRow("TbBanco");
                                $servico = $stg->findParentRow("TbServico");
                                $referencia = $registro->mostrar_referencia();
                                $this->_helper->layout()->disableLayout();
                                $banco_sigla = Escola_Util::minuscula($banco->sigla);
                                $filename = ROOT_DIR . "/lib/boletophp/boleto_{$banco_sigla}.php";
                                if (file_exists($filename)) {
                                    include($filename);
                                    die();
                                } else {
                                    $this->_flashMessage("BOLETO NÃO DISPONÍVEL PARA O BANCO: {$banco->toString()}!");
                                    $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$registro->id_transporte}");
                                }
                            } else {
                                $this->_flashMessage("NENHUM CONVÊNIO DEFINIDO PARA O GRUPO DE TRANSPORTE!");
                                $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$registro->id_transporte}");
                            }
                        } else {
                            $this->_flashMessage("TRANSPORTE NÃO POSSUI PROPRIETÁRIO DEFINIDO!");
                            $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$registro->id_transporte}");
                        }
                    } else {
                        $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO ESTÁ PAGA!");
                        $this->_redirect($this->_request->getControllerName() . "/licenca/id/0/id_transporte/{$registro->id_transporte}");
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

    public function editarAction() {
        $tb = new TbServico();
        $servico = $tb->getPorCodigo("AI");
        if ($servico) {
            $this->view->servico = $servico;
            $tb = new TbServicoTransporteGrupo();
            $stgs = $tb->listar(array("id_servico" => $servico->getId()));
            if ($stgs) {
                $stg = $stgs->current();
                $this->view->stg = $stg;
                $tb = new TbInterdicao();
                $id = $this->_request->getParam("id");
                $interdicao = false;
                if ($id) {
                    $interdicao = $tb->pegaPorId($id);
                }
                if (!$interdicao) {
                    $interdicao = $tb->createRow();
                }
                if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    $db = Zend_Registry::get("db");
                    try {
                        $db->beginTransaction();
                        $dados["id_pessoa"] = 0;
                        if (isset($dados["id_pessoa_tipo"]) && $dados["id_pessoa_tipo"]) {
                            $pt = TbPessoaTipo::pegaPorId($dados["id_pessoa_tipo"]);
                            if ($pt) {
                                if ($pt->pf() && isset($dados["id_pessoa_fisica"]) && $dados["id_pessoa_fisica"]) {
                                    $pf = TbPessoaFisica::pegaPorId($dados["id_pessoa_fisica"]);
                                    if ($pf) {
                                        $dados["id_pessoa"] = $pf->id_pessoa;
                                    }
                                } elseif ($pt->pj() && isset($dados["id_pessoa_juridica"]) && $dados["id_pessoa_juridica"]) {
                                    $pj = TbPessoaJuridica::pegaPorId($dados["id_pessoa_juridica"]);
                                    if ($pj) {
                                        $dados["id_pessoa"] = $pj->id_pessoa;
                                    }
                                }
                            }
                        }
                        $interdicao->setFromArray($dados);
                        $errors = $interdicao->getErrors();
                        if (!$errors) {
                            $interdicao->save();
                            $ss = $interdicao->pegaSolicitacao();
                            if (!$ss) {
                                $tb = new TbServicoSolicitacao();
                                $ss = $tb->createRow();
                                $ss->tipo = "IN";
                                $ss->chave = $interdicao->getId();
                            }
                            $ss->setFromArray($dados);
                            $errors = $ss->getErrors();
                            if (!$errors) {
                                $ss->save();
                                if ($interdicao->isento() && !$ss->pago()) {
                                    $valor = $ss->pega_valor();
                                    $tb = new TbServicoSolicitacaoPagamento();
                                    $ssp = $tb->createRow();
                                    $ssp->setFromArray(array("id_servico_solicitacao" => $ss->getId(),
                                        "valor_pago" => "0,00",
                                        "valor_multa" => "0,00",
                                        "valor_desconto" => Escola_Util::number_format($valor->valor)));
                                    $ssp->save();
                                }
                                $db->commit();
                                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Message");
                                $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                            } else {
                                $this->view->actionErrors = $errors;
                            }
                        } else {
                            $this->view->actionErrors = $errors;
                        }
                    } catch (Exception $e) {
                        $db->rollBack();
                    }
                }
                $this->view->interdicao = $interdicao;
                $this->view->ss = $interdicao->pegaSolicitacao();
                $button = Escola_Button::getInstance();
                $button->setTitulo("ADICIONAR SOLICITAÇÃO DE AUTORIZAÇÃO DE INTERDIÇÃO");
                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array("titulo" => "Cancelar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-remove-circle",
                    "params" => array("id" => 0)));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
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
            $interdicao = TbInterdicao::pegaPorId($id);
            if ($interdicao) {
                $ss = $interdicao->pegaSolicitacao();
                if ($ss) {
                    if ($ss->pago()) {
                        $errors = $ss->validarEmitir();
                        if (!$errors) {
                            $flag = $ss->toPDF();
                            if (!$flag) {
                                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, RELATÓRIO NÃO DISPONÍVEL!");
                                $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                            }
                        } else {
                            foreach ($errors as $error) {
                                $this->_flashMessage($error);
                            }
                            $this->_redirect($this->_request->getControllerName() . "/index/id/0");
                        }
                    } else {
                        $this->_flashMessage("SOLICITAÇÃO DE SERVIÇO NÃO PAGA!");
                        $this->_redirect($this->_request->getControllerName() . "/index/id/0");
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
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function servicocancelarAction() {
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

    public function excluirAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $interdicao = TbInterdicao::pegaPorId($id);
            if ($interdicao) {
                $errors = $interdicao->getDeleteErrors();
                if (!$errors) {
                    $interdicao->delete();
                    $interdicao = TbInterdicao::pegaPorId($id);
                    if (!$interdicao) {
                        $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                    } else {
                        $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                    }
                } else {
                    foreach ($errors as $erro) {
                        $this->_flashMessage($erro);
                    }
                }
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }

}