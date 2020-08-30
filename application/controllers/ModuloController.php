<?php
class ModuloController extends Escola_Controller_Logado {
    
	public function indexAction() {
        $sessao = Escola_Session::getInstance();
		$tb = new TbModulo();
        $this->view->dados = $sessao->atualizaFiltros(array("filtro_descricao"));
        $this->view->dados["pagina_atual"] = $this->_getParam("page");
		$this->view->modulos = $tb->listar($this->view->dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("MÓDULOS");
		$button->addFromArray(array("titulo" => "Adicionar",
									"controller" => "modulo",
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
	}
	
	public function editarAction() {
		$id = $this->_request->getParam("id");
		$tb = new TbModulo();
		if ($id) {
			$modulo = $tb->fetchRow("id_modulo = " . $this->_request->getParam("id"));
		} else {
			$modulo = $tb->createRow();
		}
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$modulo->setFromArray($dados);
			$errors = $modulo->getErrors();
			if ($errors) {
				$this->view->actionErrors = $errors;
			} else {
				$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
				$modulo->save();
                if ($modulo->getId()) {
                    if (isset($dados["id_pacote"]) && is_array($dados["id_pacote"]) && count($dados["id_pacote"])) {
                        foreach ($dados["id_pacote"] as $id_pacote) {
							$pacote = TbPacote::pegaPorId($id_pacote);
							$pacote->addModulo($modulo);
                        }
                    }
                }
				$this->_redirect("modulo/index");
			}  
		}
		if ($modulo) {
			$this->view->mod = $modulo;
		}
		$button = Escola_Button::getInstance();
		if ($modulo->id_modulo) {
			$button->setTitulo("CADASTRO DE MÓDULO - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE MÓDULO - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", "modulo", "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbModulo();
			$rg = $tb->find($id);
			if (count($rg)) {
				$rg->current()->delete();
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
		}
		$this->_redirect("modulo/index");
	}
	
	public function viewAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbModulo();
			$rg = $tb->find($id);
			if (count($rg)) {
				$this->view->modulo = $rg->current();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR MÓDULO");
				$button->addAction("Voltar", "modulo", "index", "icon-reply");
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
				$this->_redirect("modulo/index");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect("modulo/index");
		}
	}
	
	public function acaosAction() {
		$id_modulo = $this->_request->getParam("id_modulo");
		if ($id_modulo) {
			$tb_modulo = new TbModulo();
			$modulos = $tb_modulo->find($id_modulo);
			if (count($modulos)) {
				$this->view->modulo_atual = $modulos->current();
				$page = $this->_getParam("page");
				$this->view->acaos = $this->view->modulo_atual->listarAcao(array("pagina_atual" => $page));
				$button = Escola_Button::getInstance();
				$button->setTitulo("MÓDULOS - AÇÕES");
				$button->addFromArray(array("titulo" => "Adicionar",
											"controller" => "modulo",
											"action" => "editaracao",
											"img" => "icon-plus-sign",
											"params" => array("id" => 0, "id_modulo" => $this->view->modulo_atual->getId())));
				$button->addFromArray(array("titulo" => "Voltar",
											"controller" => "modulo",
											"action" => "index",
											"img" => "icon-reply",
											"params" => array("id" => 0)));
			} else {
				$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
				$this->_redirect("modulo/index");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect("modulo/index");
		}
	}
	
	public function viewacaoAction() {
		$id_modulo = $this->_request->getParam("id_modulo");
		$id = $this->_request->getParam("id");
		if ($id_modulo && $id) {
			$tb = new TbModulo();
			$rg = $tb->find($id_modulo);
			if (count($rg)) {
				$this->view->modulo_atual = $rg->current();
			}
			$tb = new TbAcao();
			$rg = $tb->find($id);
			if (count($rg)) {
				$this->view->acao_atual = $rg->current();
				$button = Escola_Button::getInstance();
				$button->setTitulo("MÓDULOS - AÇÕES - VISUALIZAR");
				$button->addFromArray(array("titulo" => "Voltar",
											"controller" => "modulo",
											"action" => "acaos",
											"img" => "icon-remove-circle",
											"params" => array("id" => 0, "id_modulo" => $this->view->modulo_atual->getId())));
			} else {
				$this->_flashMessage("NÃO LOCALIZADO!");
				$this->_redirect("modulo/acaos");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect("modulo/acaos");
		}
	}
	
	public function editaracaoAction() {
		$id_modulo = $this->_request->getParam("id_modulo");
		$id = $this->_request->getParam("id");
		if ($id_modulo) {
			$tb = new TbModulo();
			$rg = $tb->find($id_modulo);
			if (count($rg)) {
				$this->view->modulo_atual = $rg->current();
			}
			$tb = new TbAcao();
			$rg = $tb->find($id);
			if (count($rg)) {
				$this->view->acao_atual = $rg->current();
			} else {
				$this->view->acao_atual = $tb->createRow();
			}
			if ($this->_request->isPost()) {
				$dados = $this->_request->getPost();
				$this->view->acao_atual->setFromArray($dados);
				$errors = $this->view->acao_atual->getErrors();
				if ($errors) {
					$this->view->actionErrors = $errors;
				} else {
					$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
					$this->view->acao_atual->save();
					$this->_redirect("modulo/acaos/id_modulo/" . $this->view->modulo_atual->getId());
				}  
			}
			$button = Escola_Button::getInstance();
			if ($this->view->modulo_atual->getId()) {
				$button->setTitulo("CADASTRO DE AÇÕES - ALTERAR");
			} else {
				$button->setTitulo("CADASTRO DE AÇÕES - INSERIR");
			}
			$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
			$button->addAction("Cancelar", "modulo", "acaos", "icon-remove-circle", array("id_modulo" => $this->view->modulo_atual->getId()));			
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect("modulo/acaos");
		}
	}
	
	public function excluiracaoAction() {
		$id_modulo = $this->_request->getParam("id_modulo");
		$id = $this->_request->getParam("id");
		if ($id_modulo && $id) {
			$tb = new TbModulo();
			$rg = $tb->find($id_modulo);
			if (count($rg)) {
				$this->view->modulo_atual = $rg->current();
			}
			$tb = new TbAcao();
			$rg = $tb->find($id);
			if (count($rg)) {
				$rg->current()->delete();
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
		}
		$this->_redirect("modulo/acaos/id_modulo/" . $this->view->modulo_atual->getId());
	}
	
	public function subirAction() {
		$id = $this->_request->getParam("id_modulo");
		$modulo = TbModulo::pegaPorId($id);
		if ($modulo) {
			$modulo->subir();
			$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
		} else {
			$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
		}
		$this->_redirect("modulo/index");
	}
	
	public function descerAction() {
		$id = $this->_request->getParam("id_modulo");
		$modulo = TbModulo::pegaPorId($id);
		if ($modulo) {
			$modulo->descer();
			$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
		} else {
			$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
		}
		$this->_redirect("modulo/index");
	}
	
	public function pacoteAction() {
		$modulo = TbModulo::pegaPorId($this->_request->getParam("id_modulo"));
		if ($modulo) {
			$this->view->modulo = $modulo;
			$tb = new TbPacote();
			$pacotes = $tb->listar();
			if ($pacotes) {
				$this->view->pacotes = $pacotes;
				if ($this->_request->isPost()) {
					$modulo->limparPacotes();
					$dados = $this->_request->getPost();
					if (isset($dados["lista_pacote"]) && is_array($dados["lista_pacote"]) && count($dados["lista_pacote"])) {
						foreach ($dados["lista_pacote"] as $id_pacote) {
							$pacote = TbPacote::pegaPorId($id_pacote);
							$pacote->addModulo($modulo);
						}
					}
					$this->view->actionMessages[] = "Operação Efetuada com Sucesso!";
				}
				$button = Escola_Button::getInstance();
				$button->setTitulo("Módulos");
				$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
				$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
			} else {
				$this->_flashMessage("Falha ao Executar Operação, Nenhum pacote disponível!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
}