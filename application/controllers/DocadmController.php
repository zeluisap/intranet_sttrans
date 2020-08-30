<?php
class DocadmController extends Escola_Controller_Logado {
	
	public function indexAction() {
		$page = $this->_getParam("page");
        $session = Escola_Session::getInstance();
		$filtros = array("filtro_id_documento_tipo", "filtro_numero", "filtro_ano", "page", "filtro_resumo");
		$dados = $session->atualizaFiltros($filtros);
		$this->view->dados = $dados;
		$dados["pagina_atual"] = $page;
		$tb = new TbDocumentoTipoTarget();
		$dtt = $tb->getPorChave("A");
		if ($dtt) {
			$dados["filtro_id_documento_tipo_target"] = $dtt->getId();
		}
		$tb = new TbDocumento();
		$this->view->registros = $tb->listarPorPagina($dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("DOCUMENTOS ADMINISTRATIVOS");
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
	}
	
	public function editarAction() {
		$id = $this->_request->getParam("id");
		$tb = new TbDocumento();
		if ($id) {
			$registro = $tb->getPorId($id);
		} else {
			$registro = $tb->createRow();
			$tb = new TbDocumentoModo();
			$dm = $tb->getPorChave("N");
			if ($dm) {
				$registro->id_documento_modo = $dm->getId();
			}
			$tb = new TbDocumentoStatus();
			$status = $tb->getPorChave("E");
			if ($dm) {
				$registro->id_documento_status = $status->getId();
			}
			$usuario = Escola_Acl::getUsuarioLogado();
			if ($usuario) {
				$tb = new TbFuncionario();
				$funcionario = $tb->getPorUsuario($usuario);
				if ($funcionario) {
					$registro->id_funcionario = $funcionario->getId();
					$lotacao = $funcionario->pegaLotacaoAtual();
					if ($lotacao) {
						$registro->id_setor = $lotacao->id_setor;
					}
				}
			}
			$tb = new TbSetor();
			$procedencia = $tb->pegaInstituicao();
			if ($procedencia) {
				$registro->id_setor_procedencia = $procedencia->getId();
			}
		}
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$arquivo = Escola_Util::getUploadedFile("arquivo");
			if ($arquivo && $arquivo["size"]) {
				$dados["arquivo"] = $arquivo;
			}
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
			$button->setTitulo("CADASTRO DE DOCUMENTO ADMINISTRATIVO - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE DOCUMENTO ADMINISTRATIVO - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbDocumento();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$registro->delete();
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");					
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
			$tb = new TbDocumento();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR DOCUMENTO ADMINISTRATIVO");
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
	
	public function funcionarioAction() {
		$id = $this->_getParam("id");
		if ($id) {
			$documento = TbDocumento::pegaPorId($id);
			if ($documento) {
				$this->view->documento = $documento;
				$this->view->registros = $documento->pegaFuncionario();
				$button = Escola_Button::getInstance();
				$button->setTitulo("DOCUMENTOS ADMINISTRATIVOS - FUNCIONÁRIOS");
				$button->addFromArray(array("titulo" => "Adicionar",
											"onclick" => "add_usuario()",
											"action" => "editar",
											"img" => "icon-plus-sign",
											"params" => array("id" => 0)));
				$button->addFromArray(array("titulo" => "Voltar",
											"controller" => $this->_request->getControllerName(),
											"action" => "index",
											"img" => "icon-reply",
											"params" => array("id" => $id)));
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
				$this->_redirect($this->_request->getControllerName() . "/index");							
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");			
		}
	}
	
	public function addfuncionarioAction() {
		$flag = false;
		$id = $this->_getParam("id");
		$id_funcionario = $this->_getParam("id_funcionario");
		if ($id && $id_funcionario) {
			$documento = TbDocumento::pegaPorId($id);
			if ($documento) {
				$funcionario = TbFuncionario::pegaPorId($id_funcionario);
				if ($funcionario) {
					$flag = $documento->addFuncionario($funcionario);
				}
			}
		}
		if ($flag) {
			$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO!");
		}
		$this->_redirect("docadm/funcionario/id/{$id}");
	}
	
	public function excluirfuncionarioAction() {
		$id = $this->_request->getParam("id");
		$id_funcionario = $this->_request->getParam("id_funcionario");
		if ($id && $id_funcionario) {
			$documento = TbDocumento::pegaPorId($id);
			$funcionario = TbFuncionario::pegaPorId($id_funcionario);
			if ($documento && $funcionario) {
				$documento->excluirFuncionario($funcionario);
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");					
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
		}
		$this->_redirect("docadm/funcionario/id/{$id}");
	}
	
	public function viewfuncionarioAction() {
		$id = $this->_request->getParam("id");
		$id_funcionario = $this->_request->getParam("id_funcionario");
		if ($id && $id_funcionario) {
			$documento = TbDocumento::pegaPorId($id);
			$funcionario = TbFuncionario::pegaPorId($id_funcionario);
			if ($documento && $funcionario) {
				$this->view->documento = $documento;
				$this->view->funcionario = $funcionario;
				$this->view->pf = $funcionario->findParentRow("TbPessoaFisica");
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR REFERÊNCIA DE FUNCIONÁRIO");
				$button->addFromArray(array("titulo" => "Voltar",
									"controller" => "docadm",
									"action" => "funcionario",
									"img" => "icon-reply",
									"params" => array("id" => $documento->getId())));
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