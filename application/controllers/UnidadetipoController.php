<?php
class UnidadetipoController extends Escola_Controller_Logado {
	public function indexAction() {
		$tb = new TbUnidadeTipo();
		$page = $this->_getParam("page");
		$this->view->registros = $tb->listar(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("TIPOS DE UNIDADE");
		$button->addFromArray(array("titulo" => "Adicionar",
									"controller" => $this->_request->getControllerName(),
									"action" => "editar",
									"img" => "add.png",
									"params" => array("id" => 0)));
		$button->addFromArray(array("titulo" => "Voltar",
									"controller" => "intranet",
									"action" => "index",
									"img" => "delete.png",
									"params" => array("id" => 0)));
	}
	
	public function editarAction() {
		$id = $this->_request->getParam("id");
		$tb = new TbUnidadeTipo();
		if ($id) {
			$registro = $tb->fetchRow("id_unidade_tipo = " . $id);
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
			$button->setTitulo("CADASTRO DE TIPO DE UNIDADE - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE TIPO DE UNIDADE - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "disk.png");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "delete.png");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbUnidadeTipo();
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
		$this->_redirect($this->_request->getControllerName() . "/index");
	}
	
	public function viewAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbUnidadeTipo();
			$rg = $tb->find($id);
			if (count($rg)) {
				$this->view->registro = $rg->current();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR TIPO DE UNIDADE");
				$button->addAction("Voltar", $this->_request->getControllerName(), "index", "delete.png");
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