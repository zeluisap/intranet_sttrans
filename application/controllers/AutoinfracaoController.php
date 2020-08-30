<?php 
class AutoinfracaoController extends Escola_Controller_Logado {

	public function indexAction() {
        $session = Escola_Session::getInstance();
        $dados = $session->atualizaFiltros(array("filtro_id_servico_tipo", "filtro_caracter", "filtro_codigo_inicio", "filtro_codigo_final", "filtro_id_auto_infracao_status", "filtro_id_agente"));
		$tb = new TbAutoInfracao();
		$dados["pagina_atual"] = $this->_getParam("page");
		$this->view->registros = $tb->listar_por_pagina($dados);
        $this->view->dados = $dados;
		$button = Escola_Button::getInstance();
		$button->setTitulo("AUTOS DE INFRAÇÃO");
		$button->addFromArray(array("titulo" => "Adicionar",
									"controller" => $this->_request->getControllerName(),
									"action" => "add",
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
    
    public function addAction() {
        if ($this->_request->isPost()) {
            $errors = array();
            $dados = $this->_request->getPost();
            if (!isset($dados["id_servico_tipo"]) || !$dados["id_servico_tipo"]) {
                $errors[] = "NENHUM TIPO INFORMADO!";
            }
            if (!isset($dados["caracter"]) || !$dados["caracter"]) {
                $errors[] = "NENHUM CARACTER INFORMADO!";
            }
            if (!isset($dados["codigo_inicio"]) || !$dados["codigo_inicio"]) {
                $errors[] = "CÓDIGO INICIAL NÃO INFORMADO!";
            }
            if (isset($dados["codigo_inicio"]) && !is_numeric($dados["codigo_inicio"])) {
                $errors[] = "CÓDIGO INICIAL INVÁLIDO!";
            }
            if (!isset($dados["codigo_final"]) || !$dados["codigo_final"]) {
                $errors[] = "CÓDIGO FINAL NÃO INFORMADO!";
            }
            if (isset($dados["codigo_final"]) && !is_numeric($dados["codigo_final"])) {
                $errors[] = "CÓDIGO FINAL INVÁLIDO!";
            }
            if (!count($errors)) {
                if ((isset($dados["codigo_inicio"]) && isset($dados["codigo_final"])) && ($dados["codigo_final"] < $dados["codigo_inicio"])) {
                    $errors[] = "CÓDIGO FINAL SUPERIOR AO INICIAL!";
                }
            }
            if (!count($errors)) {
                $contador = 0;
                $tb = new TbAutoInfracao();
                for ($codigo = $dados["codigo_inicio"]; $codigo <= $dados["codigo_final"]; $codigo++) {
                    $obj = $tb->createRow();
                    $obj->setFromArray(array("id_servico_tipo" => $dados["id_servico_tipo"], "alfa" => $dados["caracter"], "codigo" => $codigo));
                    $array_erros = $obj->getErrors();
                    if (!$array_erros) {
                        $id = $obj->save();
                        if ($id) {
                            $contador++;
                        }
                    } else {
                        foreach ($array_erros as $array_erro) {
                            $this->_flashMessage($array_erro);
                        }
                    }
                }
                if ($contador) {
                    $this->_flashMessage($contador . " Registro(s) Adicionado(s).", "Messages");
                } else {
                    $this->_flashMessage("Nenhum Registro Adicionado!");
                }
                $this->_redirect("autoinfracao/index");
            } else {
                $this->view->actionErrors = $errors;
            }
        }
		$button = Escola_Button::getInstance();
        $button->setTitulo("ADICIONAR AUTO DE INFRAÇÃO");
		$button->addScript("Criar", "salvarFormulario('formulario')", "icon-magic");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");        
    }
	
	public function editarAction() {
		$id = $this->_request->getParam("id");
		$tb = new TbAutoInfracao();
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
        $button->setTitulo("CADASTRO DE AUTO DE INFRAÇÃO");
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbAutoInfracao();
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
			$tb = new TbAutoInfracao();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
                $this->view->ocorrencias = $registro->listar_ocorrencias();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR AUTO-INFRAÇÃO");
                $button->addFromArray(array("titulo" => "Excluir",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "excluir",
                                            "img" => "icon-trash",
                                            "class" => "link_excluir",
                                            "params" => array("id" => $id)));
                if ($registro->disponivel()) {
                    $button->addFromArray(array("titulo" => "Atribuir ao Agente",
                                                "controller" => $this->_request->getControllerName(),
                                                "action" => "atribuir",
                                                "img" => "icon-group",
                                                "params" => array("id" => $id)));
                }
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
    
    public function atribuirAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $registro = TbAutoInfracao::pegaPorId($id);
            if ($registro) {
                if ($registro->disponivel()) {
                    if ($this->_request->isPost()) {
                        $dados = $this->_request->getPost();
                        if (isset($dados["id_agente"]) && $dados["id_agente"]) {
                            $agente = TbAgente::pegaPorId($dados["id_agente"]);
                            if ($agente) {
                                $flag = $registro->setAgente($agente);
                                if ($flag) {
                                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                                } else {
                                    $this->_flashMessage("NENHUM AGENTE DEFINIDO!");
                                }
                            } else {
                                $this->_flashMessage("NENHUM AGENTE DEFINIDO!");
                            }
                        } else {
                            $this->_flashMessage("NENHUM AGENTE DEFINIDO!");
                        }
                        $this->_redirect($this->_request->getControllerName() . "/index");
                    }
                    $this->view->registro = $registro;
                    $button = Escola_Button::getInstance();
                    $button->setTitulo("ATRIBUIR AUTO DE INFRAÇÃO AO AGENTE");
                    $button->addScript("Atribuir", "salvarFormulario('formulario')", "icon-save");
                    $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
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
    
    public function devolverAction() {
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
                            $this->_redirect($this->_request->getControllerName() . "/index");
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
                        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
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
	
	public function viewnotificacaoAction() {
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
                    $button = Escola_Button::getInstance();
                    $button->setTitulo("VISUALIZAR OCORRÊNCIA DE AUTO-INFRAÇÃO > NOTIFICAÇÃO");
                    $button->addFromArray(array("titulo" => "Voltar",
                                                "controller" => $this->_request->getControllerName(),
                                                "action" => "view",
                                                "img" => "icon-reply",
                                                "params" => array("id" => $ai->getId())));
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
                    $button->setTitulo("CONFIRMAÇÃO DE PAGAMENTO DE AUTO DE INFRAÇÃO");
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
}