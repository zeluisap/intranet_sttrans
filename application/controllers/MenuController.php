<?php
class MenuController extends Escola_Controller_Logado {
	
	public function indexAction() {
		$tb = new TbMenu();
		$page = $this->_getParam("page");
		$this->view->registros = $tb->listarPorPagina(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("MENUS");
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
		$tb = new TbMenu();
		if ($id) {
			$registro = $tb->getPorId($id);
		} else {
			$registro = $tb->createRow();
		}
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$arquivo = Escola_Util::getUploadedFile("arquivo");
			if ($arquivo && isset($arquivo["size"]) && $arquivo["size"]) {
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
		$items = $tb->listar();
		$this->view->menus = false;
		if ($items) {
			$menus = array();
			foreach ($items as $item) {
				if ($item->getId() != $registro->getId()) {
					$menus[] = $item;
				}
			}
			if (count($menus)) {
				$this->view->menus = $menus;
			}
		}
		$button = Escola_Button::getInstance();
		if ($this->view->registro->getId()) {
			$button->setTitulo("CADASTRO DE MENU DO SITE - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE MENU DO SITE - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbMenu();
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
			$tb = new TbMenu();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR MENU");
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
	
	public function subirAction() {
		$id = $this->_request->getParam("id");
		$registro = TbMenu::pegaPorId($id);
		if ($registro) {
			$registro->subir();
			$this->_redirect("menu/index/id/" . $registro->getId());
		} else {
			$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
			$this->_redirect("menu/index");
		}
	}
	
	public function descerAction() {
		$id = $this->_request->getParam("id");
		$registro = TbMenu::pegaPorId($id);
		if ($registro) {
			$registro->descer();
			$this->_redirect("menu/index/id/" . $registro->getId());
		} else {
			$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
			$this->_redirect("menu/index");
		}
	}
}