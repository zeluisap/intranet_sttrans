<?php 
class ArquivotipoController extends Escola_Controller_Default {

	public function showAction() {
		$result = false;
		$arquivo = TbArquivoTipo::pegaPorId($this->_request->getParam("id"));
		$this->view->arquivo = false;
		if ($arquivo) {
			$this->view->arquivo = $arquivo->getWideImage();
			/*
			if ($this->_request->getParam("size")) {
				$this->view->arquivo = $this->view->arquivo->resize($this->_request->getParam("size"), $this->_request->getParam("size"));
			}
			*/
			if ($this->_request->getParam("width")) {
				if ($this->_request->getParam("height")) {
					$this->view->arquivo = $this->view->arquivo->resize($this->_request->getParam("width"), $this->_request->getParam("height"));
				} else {
					$this->view->arquivo = $this->view->arquivo->resize($this->_request->getParam("width"));
				}
			}
			header("Content-type: image/jpeg");
			if ($this->view->arquivo) {
				echo $this->view->arquivo->asString("jpg", 80);
			} else {
				echo "falha ao executar operaÃ§Ã£o!";
			}
		}
		die();		
	}
	
	public function indexAction() {
		$tb = new TbArquivoTipo();
		$page = $this->_getParam("page");
		$this->view->registros = $tb->listarPorPagina(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("TIPOS DE ARQUIVO");
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
		$tb = new TbArquivoTipo();
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
			$button->setTitulo("CADASTRO DE TIPO DE ARQUIVO - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE TIPO DE ARQUIVO - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbArquivoTipo();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$registro->delete();
				$this->_flashMessage("OPERAÃÃO EFETUADA COM SUCESSO!", "Messages");					
			} else {
				$this->_flashMessage("INFORMAÃÃO RECEBIDA INVÃLIDA!");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÃÃO RECEBIDA!");
		}
		$this->_redirect($this->_request->getControllerName() . "/index");
	}
	
	public function viewAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbArquivoTipo();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR TIPO DE ARQUIVO");
				$button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
			} else {
				$this->_flashMessage("Informação Recebida Inválida!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("Nenhuma Informação Recebida!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
}