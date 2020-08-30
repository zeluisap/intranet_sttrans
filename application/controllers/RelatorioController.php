<?php
class RelatorioController extends Escola_Controller_Logado {
	
	public function admAction() {
		$this->view->relatorio = false;
		$this->view->method = false;
		$session = Escola_Session::getInstance();
		$dados = $session->atualizaFiltros(array("id_relatorio", "tipo"));
		$this->view->dados = $dados;
		if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    try {
                        if (!isset($dados["operacao"]) || ($dados["operacao"] != "imprimir")) {
                            throw new Exception("Falha ao Gerar Relatï¿½rio, Dados Invï¿½lidos!");
                        }
                        if (!isset($dados["id_relatorio"]) || !$dados["id_relatorio"]) {
                            throw new Exception("Falha ao Gerar Relatï¿½rio, Nenhum Relatï¿½rio Selecionado!");
                        } 

                        $relatorio = TbRelatorio::getInstance($dados["id_relatorio"]);
                        var_dump($relatorio); die();
                        if ($relatorio) {
                            throw new Exception("Falha ao Gerar Relatï¿½rio, Nenhum Relatï¿½rio Selecionado!");
                        }

                        $this->view->relatorio = $relatorio;
                        $method = "to" . $dados["tipo"];
                        if (!method_exists($relatorio, $method)) {
                            throw new Exception("Falha ao Gerar Relatï¿½rio, Formato Invï¿½lido!");
                        } 

                        $this->view->method = $method;

                        if (($dados["tipo"] == "XLS") || ($dados["tipo"] == "PDF")) {
                            $relatorio->$method();
                        }
                    } catch (Exception $ex) {
                        $this->view->actionErrors[] = $ex->getMessage();
                    }
		}
		$tb = new TbRelatorioTipo();
		$rt = $tb->getPorChave("Adm");
		if ($rt) {
			$tb = new TbRelatorio();
			$relatorios = $tb->listar(array("filtro_id_relatorio_tipo" => $rt->getId()));
			if ($relatorios && count($relatorios)) {
				$this->view->relatorios = $relatorios;
				$button = Escola_Button::getInstance();
				$button->setTitulo("RELATï¿½RIOS");
				$button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
				$button->addFromArray(array("titulo" => "Voltar",
																"controller" => "intranet",
																"action" => "index",
																"img" => "icon-reply",
																"params" => array("id" => 0)));
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, NENHUM RELATï¿½RIO DISPONï¿½VEL!");
				$this->_redirect("index");
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INCONSISTENTES!");
			$this->_redirect("index");
		}
	}
    
	public function caraterAction() {
            $this->view->relatorio = false;
            $this->view->method = false;
            
            $session = Escola_Session::getInstance();
            $dados = $session->atualizaFiltros(array("id_transporte_grupo", "tipo", "agrupado"));
            if (!isset($dados["agrupado"]) || !$dados["agrupado"]) {
                $dados["agrupado"] = "S";
            }
            $this->view->dados = $dados;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();

                try {
                    if (!(isset($dados["operacao"]) && ($dados["operacao"] == "imprimir"))) {
                        throw new Exception("Falha! Dados do RelatÃ³rio InvÃ¡lido!");
                    }

                    $agrupado = ($dados["agrupado"] == "S");

                    if ($agrupado) {
                        $relatorio_nome = "CaraterGrupo";
                    } else {
                        $relatorio_nome = "Carater";
                    }

                    $tb = new TbRelatorio();
                    $relatorio = $tb->getPorChave($relatorio_nome);

                    if (!$relatorio) {
                        throw new Exception("Falha ao Gerar Relatório!");
                    }

                    $this->view->relatorio = $relatorio;
                    $rel = TbRelatorio::getInstance($relatorio->getId());
                    if (!$rel) {
                        throw new Exception("Falha ao Gerar Relatório!");
                    }

                    $rel->set_dados($dados);
                    $errors = $rel->validarEmitir();
                    if ($errors) {
                        $this->view->actionErrors = $errors;
                        throw new Exception("");
                    }

                    $this->view->errors = $errors;

                    $this->view->rel = $rel;
                    $method = "to" . $dados["tipo"];
                    if (!method_exists($rel, $method)) {
                        throw new Exception("Falha ao Gerar Relatório! Formato Inválido!");
                    }

                    if (($dados["tipo"] == "XLS") || ($dados["tipo"] == "PDF")) {
                        $rel->$method();
                    } else {
                        $this->view->method = $method;
                    }

                } catch (Exception $ex) {
                    $message = $ex->getMessage();
                    if ($message) {
                        $this->view->actionErrors[] = $ex->getMessage();
                    }
                }
            }
            $button = Escola_Button::getInstance();
            $button->setTitulo("RELATï¿½RIOS");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array("titulo" => "Voltar",
                                                    "controller" => "intranet",
                                                    "action" => "index",
                                                    "img" => "icon-reply",
                                                    "params" => array("id" => 0)));
	}
    
	public function placasretidasAction() {
		$this->view->relatorio = false;
		$this->view->method = false;
        $tb = new TbRelatorio();
        $relatorio = $tb->getPorChave("Placas");
        if ($relatorio) {
            $this->view->relatorio = $relatorio;
            $session = Escola_Session::getInstance();
            $dados = $session->atualizaFiltros(array("id_transporte_grupo", "tipo"));
            $this->view->dados = $dados;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                if (isset($dados["operacao"]) && ($dados["operacao"] == "imprimir")) {
                    $rel = TbRelatorio::getInstance($relatorio->getId());
                    if ($rel) {
                        $rel->set_dados($dados);
                        $errors = $rel->validarEmitir();
                        if (!$errors) {
                            $this->view->rel = $rel;
                            $method = "to" . $dados["tipo"];
                            if (!method_exists($rel, $method)) {
                                $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO, FORMATO INVï¿½LIDO!";
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
                        $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO!";
                    }
                }
            }
            $button = Escola_Button::getInstance();
            $button->setTitulo("RELATï¿½RIOS");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array("titulo" => "Voltar",
                                                    "controller" => "intranet",
                                                    "action" => "index",
                                                    "img" => "icon-reply",
                                                    "params" => array("id" => 0)));
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INCONSISTENTES!");
            $this->_redirect("intranet/index");
        }
	}
    
	public function agenteAction() {
		$this->view->relatorio = false;
		$this->view->method = false;
        $tb = new TbRelatorio();
        $relatorio = $tb->getPorChave("Agente");
        if ($relatorio) {
            $this->view->relatorio = $relatorio;
            $session = Escola_Session::getInstance();
            $dados = $session->atualizaFiltros(array("tipo"));
            $this->view->dados = $dados;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                if (isset($dados["operacao"]) && ($dados["operacao"] == "imprimir")) {
                    $rel = TbRelatorio::getInstance($relatorio->getId());
                    if ($rel) {
                        $rel->set_dados($dados);
                        $errors = $rel->validarEmitir();
                        if (!$errors) {
                            $this->view->rel = $rel;
                            $method = "to" . $dados["tipo"];
                            if (!method_exists($rel, $method)) {
                                $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO, FORMATO INVï¿½LIDO!";
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
                        $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO!";
                    }
                }
            }
            $button = Escola_Button::getInstance();
            $button->setTitulo("RELATï¿½RIOS");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array("titulo" => "Voltar",
                                                    "controller" => "intranet",
                                                    "action" => "index",
                                                    "img" => "icon-reply",
                                                    "params" => array("id" => 0)));
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INCONSISTENTES!");
            $this->_redirect("intranet/index");
        }
	}
    
	public function motoristaAction() {
            try {
                $this->view->relatorio = false;
                $this->view->method = false;
                $tb = new TbRelatorio();
                $relatorio = $tb->getPorChave("Motorista");
                if (!$relatorio) {
                    throw new Exception("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INCONSISTENTES!");
                }
                
                $this->view->relatorio = $relatorio;
                $session = Escola_Session::getInstance();
                $dados = $session->atualizaFiltros(array("id_transporte_grupo", "tipo", "ordem"));
                if (!$dados["ordem"]) {
                    $dados["ordem"] = "nome";
                }
                $this->view->dados = $dados;
                if ($this->_request->isPost()) {
                    try {
                        $dados = $this->_request->getPost();
                        if (!isset($dados["operacao"]) || ($dados["operacao"] != "imprimir")) {
                            throw new Exception("Falha ao Gerar Relatï¿½rio, Dados Invï¿½lidos!");
                        }
                        
                        $rel = TbRelatorio::getInstance($relatorio->getId());
                        if (!$rel) {
                            throw new Exception("Falha ao Gerar Relatï¿½rio, Nenhum Relatï¿½rio Disponï¿½vel!");
                        }
                        
                        $rel->set_dados($dados);
                        $errors = $rel->validarEmitir();
                        $this->view->errors = $errors;
                        if ($errors) {
                            throw new Exception("<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
                        }
                        
                        $this->view->rel = $rel;
                        $method = "to" . $dados["tipo"];
                        if (!method_exists($rel, $method)) {
                            throw new Exception("FALHA AO GERAR RELATï¿½RIO, FORMATO INVï¿½LIDO!");
                        }

                        $this->view->method = $method;
                        if (($dados["tipo"] == "XLS") || ($dados["tipo"] == "PDF")) {
                            $rel->$method();
                        }
                        
                    } catch (Exception $ex) {
                        $this->view->actionErrors[] = $ex->getMessage();
                    }
                }
                $button = Escola_Button::getInstance();
                $button->setTitulo("RELATï¿½RIOS");
                $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
                $button->addFromArray(array("titulo" => "Voltar",
                                                        "controller" => "intranet",
                                                        "action" => "index",
                                                        "img" => "icon-reply",
                                                        "params" => array("id" => 0)));
            } catch (Exception $ex) {
                $this->_flashMessage($ex->getMessage());
                $this->_redirect("intranet/index");
            }
	}
        
    public function taxasAction(){
        $this->view->relatorio = false;
        $this->view->method = false;
        $tb = new TbRelatorio();
        $relatorio = $tb->getPorChave("Taxas");
        if ($relatorio) {
            $this->view->relatorio = $relatorio;
            $session = Escola_Session::getInstance();
            $dados = $session->atualizaFiltros(array("id_transporte_grupo", "data_inicio", "data_fim", "tipo", "nome_proprietario"));
            $this->view->dados = $dados;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                if (isset($dados["operacao"]) && ($dados["operacao"] == "imprimir")) {
                    $rel = TbRelatorio::getInstance($relatorio->getId());
                    if ($rel) {
                        $rel->set_dados($dados);
                        $errors = $rel->validarEmitir();
                        if (!$errors) {
                            $this->view->rel = $rel;
                            $method = "to" . $dados["tipo"];
                            if (!method_exists($rel, $method)) {
                                $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO, FORMATO INVï¿½LIDO!";
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
                        $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO!";
                    }
                }
            }
            $button = Escola_Button::getInstance();
            $button->setTitulo("RELATï¿½RIOS");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array("titulo" => "Voltar",
                                                    "controller" => "intranet",
                                                    "action" => "index",
                                                    "img" => "icon-reply",
                                                    "params" => array("id" => 0)));
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INCONSISTENTES!");
            $this->_redirect("intranet/index");
        }
    }
        
    public function taxassacadoAction(){
        $this->view->relatorio = false;
        $this->view->method = false;
        $tb = new TbRelatorio();
        $relatorio = $tb->getPorChave("Taxassacado");
        if ($relatorio) {
            $this->view->relatorio = $relatorio;
            $session = Escola_Session::getInstance();
            $dados = $session->atualizaFiltros(array("id_transporte_grupo", "data_inicio", "data_fim", "tipo", "nome_proprietario"));
            $this->view->dados = $dados;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                if (isset($dados["operacao"]) && ($dados["operacao"] == "imprimir")) {
                    $rel = TbRelatorio::getInstance($relatorio->getId());
                    if ($rel) {
                        $rel->set_dados($dados);
                        $errors = $rel->validarEmitir();
                        if (!$errors) {
                            $this->view->rel = $rel;
                            $method = "to" . $dados["tipo"];
                            if (!method_exists($rel, $method)) {
                                $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO, FORMATO INVï¿½LIDO!";
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
                        $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO!";
                    }
                }
            }
            $button = Escola_Button::getInstance();
            $button->setTitulo("RELATï¿½RIOS");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array("titulo" => "Voltar",
                                                    "controller" => "intranet",
                                                    "action" => "index",
                                                    "img" => "icon-reply",
                                                    "params" => array("id" => 0)));
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INCONSISTENTES!");
            $this->_redirect("intranet/index");
        }    
    }
    
    public function taxasapagarAction(){
        $this->view->relatorio = false;
        $this->view->method = false;
        $tb = new TbRelatorio();
        $relatorio = $tb->getPorChave("Taxasapagar");
        if ($relatorio) {
            $this->view->relatorio = $relatorio;
            $session = Escola_Session::getInstance();
            $dados = $session->atualizaFiltros(array("id_transporte_grupo", "data_inicio", "data_fim", "tipo"));
            $this->view->dados = $dados;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                if (isset($dados["operacao"]) && ($dados["operacao"] == "imprimir")) {
                    $rel = TbRelatorio::getInstance($relatorio->getId());
                    if ($rel) {
                        $rel->set_dados($dados);
                        $errors = $rel->validarEmitir();
                        if (!$errors) {
                            $this->view->rel = $rel;
                            $method = "to" . $dados["tipo"];
                            if (!method_exists($rel, $method)) {
                                $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO, FORMATO INVï¿½LIDO!";
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
                        $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO!";
                    }
                }
            }
            $button = Escola_Button::getInstance();
            $button->setTitulo("RELATï¿½RIOS");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array("titulo" => "Voltar",
                                                    "controller" => "intranet",
                                                    "action" => "index",
                                                    "img" => "icon-reply",
                                                    "params" => array("id" => 0)));
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INCONSISTENTES!");
            $this->_redirect("intranet/index");
        }    
    }
    
    public function veiculosAction(){
        $this->view->relatorio = false;
        $this->view->method = false;
        $tb = new TbRelatorio();
        $relatorio = $tb->getPorChave("veiculos");
        if ($relatorio) {
            $this->view->relatorio = $relatorio;
            $session = Escola_Session::getInstance();
            $dados = $session->atualizaFiltros(array("id_transporte_grupo", "id_transporte_veiculo_status", "id_servico_solicitacao_status", "tipo"));
            $this->view->dados = $dados;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                if (isset($dados["operacao"]) && ($dados["operacao"] == "imprimir")) {
                    $rel = TbRelatorio::getInstance($relatorio->getId());
                    if ($rel) {
                        $rel->set_dados($dados);
                        $errors = $rel->validarEmitir();
                        if (!$errors) {
                            $this->view->rel = $rel;
                            $method = "to" . $dados["tipo"];
                            if (!method_exists($rel, $method)) {
                                $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO, FORMATO INVï¿½LIDO!";
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
                        $this->view->actionErrors[] = "FALHA AO GERAR RELATï¿½RIO!";
                    }
                }
            }
            $button = Escola_Button::getInstance();
            $button->setTitulo("RELATï¿½RIOS");
            $button->addScript("Imprimir", "salvarFormulario('formulario')", "icon-print");
            $button->addFromArray(array("titulo" => "Voltar",
                                                    "controller" => "intranet",
                                                    "action" => "index",
                                                    "img" => "icon-reply",
                                                    "params" => array("id" => 0)));
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INCONSISTENTES!");
            $this->_redirect("intranet/index");
        }    
    }
}