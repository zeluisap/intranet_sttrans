<?php 
class FuncionarioocorrenciatipoController extends Escola_Controller_Logado {

	public function indexAction() {
		$tb = new TbFuncionarioOcorrenciaTipo();
		$page = $this->_getParam("page");
		$this->view->registros = $tb->listarPorPagina(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("TIPOS DE OCORRÊNCIAS DE FUNCIONÁRIOS");
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
		$tb = new TbFuncionarioOcorrenciaTipo();
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
			$button->setTitulo("CADASTRO DE TIPO DE OCORRÊNCIA DE FUNCIONÁRIO - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE TIPO DE OCORRÊNCIA DE FUNCIONÁRIO - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbFuncionarioOcorrenciaTipo();
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
			$tb = new TbFuncionarioOcorrenciaTipo();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR TIPO DE OCORRÊNCIA DE FUNCIONÁRIO");
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
	
	public function setorAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$chamado_tipo = TbChamadoTipo::pegaPorId($id);
			if ($chamado_tipo) {
				$this->view->chamado_tipo= $chamado_tipo;
				$this->view->registros = $chamado_tipo->pegaSetor();
				$button = Escola_Button::getInstance();
				$button->setTitulo("SETORES VINCULADOS");
				$button->addFromArray(array("titulo" => "Adicionar",
											"onclick" => "setor()",
											"img" => "add.png",
											"params" => array("id_setor" => 0)));
				$button->addFromArray(array("titulo" => "Voltar",
											"controller" => $this->_request->getControllerName(),
											"action" => "index",
											"img" => "delete.png",
											"params" => array("id_setor" => 0)));
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
				$this->_redirect($this->_request->getControllerName() . "/index");			
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect($this->_request->getControllerName() . "/index");			
		}
	}
	
	public function setorsalvarAction() {
		$flag = false;
		$id = $this->_request->getParam("id");
		$ct = TbChamadoTipo::pegaPorId($id);
		if ($ct) {
			if ($this->_request->isPost()) {
				$dados = $this->_request->getPost();
				if (isset($dados["id_setor"]) && $dados["id_setor"]) {
					$setor = TbSetor::pegaPorId($dados["id_setor"]);
					if ($setor) {
						$ct->addSetor($setor);
						$flag = true;
					}
				}
			}
		}
		if ($flag) {
			$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
		}
		$this->_redirect($this->_request->getControllerName() . "/setor/id/" . $ct->getId());		
	}
	
	public function excluirsetorAction() {
		$flag = false;
		$id = $this->_request->getParam("id");
		$ct = TbChamadoTipo::pegaPorId($id);
		if ($ct) {
			$id = $this->_request->getParam("id_setor");
			$setor = TbSetor::pegaPorId($id);
			if ($setor) {
				$ct->excluirSetor($setor);
				$flag = true;
			}
		}
		if ($flag) {
			$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
		}
		$this->_redirect($this->_request->getControllerName() . "/setor/id/" . $ct->getId());		
	}
}