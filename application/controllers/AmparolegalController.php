<?php 
class AmparolegalController extends Escola_Controller_Logado {
    
	public function indexAction() {
        $session = Escola_Session::getInstance();
        if (isset($session->id_amparo_legal)) {
            unset($session->id_amparo_legal);
        }
		$tb = new TbAmparoLegal();
		$page = $this->_getParam("page");
		$this->view->registros = $tb->listar_por_pagina(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("AMPARO LEGAL");
		$button->addFromArray(array("titulo" => "Adicionar",
									"controller" => $this->_request->getControllerName(),
									"action" => "editar",
									"img" => "icon-plus-sign",
									"params" => array("id" => 0)));
		$button->addFromArray(array("titulo" => "Voltar",
									"controller" => "intranet",
									"action" => "index",
									"img" => "icon-reply",
									"params" => array("id" => 0)));
	}
	
	public function editarAction() {
		$id = $this->_request->getParam("id");
		$tb = new TbAmparoLegal();
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
			$button->setTitulo("CADASTRO DE AMPARO LEGAL - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE AMPARO LEGAL - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbAmparoLegal();
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
			$tb = new TbAmparoLegal();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR AMPARO LEGAL");
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
                $button->addFromArray(array("titulo" => "Infrações",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "item",
                                            "img" => "icon-list",
                                            "params" => array("page" => 1, "id" => 0, "id_amparo_legal" => $id)));
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

    public function itemAction() {
        $session = Escola_Session::getInstance();
        if ($this->_request->getParam("id_amparo_legal")) {
            $id_amparo_legal = $this->_request->getParam("id_amparo_legal");
            $session->id_amparo_legal = $id_amparo_legal;
        }
        $id_amparo_legal = $session->id_amparo_legal;
        if ($id_amparo_legal) {
            $amparo_legal = TbAmparoLegal::pegaPorId($id_amparo_legal);
            if ($amparo_legal) {
                $this->view->amparo_legal = $amparo_legal;
                $tb = new TbInfracao();
                $page = $this->_getParam("page");
                $this->view->registros = $tb->listar_por_pagina(array("pagina_atual" => $page, "id_amparo_legal" => $id_amparo_legal));
                $button = Escola_Button::getInstance();
                $button->setTitulo("AMPARO LEGAL > ÍTENS");
                $button->addFromArray(array("titulo" => "Adicionar",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "editaritem",
                                            "img" => "icon-plus-sign",
                                            "params" => array("id" => 0)));
                $button->addFromArray(array("titulo" => "Voltar",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "index",
                                            "img" => "icon-reply",
                                            "params" => array("id" => 0, "id_amparo_legal" => "0")));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }
	}
	
	public function editaritemAction() {
        $session = Escola_Session::getInstance();
        $id_amparo_legal = $session->id_amparo_legal;
        $amparo_legal = TbAmparoLegal::pegaPorId($id_amparo_legal);
        if ($amparo_legal) {
            $this->view->amparo_legal = $amparo_legal;
            $tb = new TbMoeda();
            $moedas = $tb->listar();
            if ($moedas) {
                $this->view->moedas = $moedas;
                $id = $this->_request->getParam("id");
                $tb = new TbInfracao();
                if ($id) {
                    $registro = $tb->getPorId($id);
                } else {
                    $registro = $tb->createRow();
                }
                if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    $registro->setFromArray($dados);
                    $registro->id_amparo_legal = $amparo_legal->getId();
                    $errors = $registro->getErrors();
                    if ($errors) {
                        $this->view->actionErrors = $errors;
                    } else {
                        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                        $registro->save();
                        $this->_redirect($this->_request->getControllerName() . "/item/id/0");
                    }  
                }
                $this->view->registro = $registro;
                $this->view->valor = $registro->pega_valor();
                $button = Escola_Button::getInstance();
                if ($this->view->registro->getId()) {
                    $button->setTitulo("CADASTRO DE ÍTEM DE AMPARO LEGAL - ALTERAR");
                } else {
                    $button->setTitulo("CADASTRO DE ÍTEM DE AMPARO LEGAL - INSERIR");
                }
                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array("titulo" => "Voltar",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "item",
                                            "img" => "icon-reply",
                                            "params" => array("id" => 0)));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA MOEDA CADASTRADA!");
                $this->_redirect($this->_request->getControllerName() . "/index/id/0");
            }
        } else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }
	}
	
	public function viewitemAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbInfracao();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR ÍTEM AMPARO LEGAL");
                $button->addFromArray(array("titulo" => "Alterar",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "editaritem",
                                            "img" => "icon-cog",
                                            "params" => array("id" => $id)));
                $button->addFromArray(array("titulo" => "Excluir",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "excluiritem",
                                            "img" => "icon-trash",
                                            "class" => "link_excluir",
                                            "params" => array("id" => $id)));
                $button->addFromArray(array("titulo" => "Voltar",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "item",
                                            "img" => "icon-reply",
                                            "params" => array("id" => 0)));
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
	
	public function excluiritemAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbInfracao();
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
	}
}