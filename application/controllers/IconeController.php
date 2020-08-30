<?php
class IconeController extends Escola_Controller_Logado {

	public function indexAction() {
		$tb = new TbIcone();
		$page = $this->_getParam("page");
		$dados["pagina_atual"] = $page;
		$this->view->registros = $tb->listar_por_pagina($dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("Icones");
		$button->addFromArray(array("titulo" => "Adicionar",
									"controller" => $this->_request->getControllerName(),
									"action" => "editar",
									"img" => "icon-plus-sign",
									"params" => array("id" => 0)));
		$button->addFromArray(array("titulo" => "Importar",
									"onclick" => "importar()",
									"img" => "icon-download-alt",
									"params" => array("id" => 0)));        
		$button->addFromArray(array("titulo" => "Voltar",
									"controller" => "intranet",
									"action" => "index",
									"img" => "icon-reply",
									"params" => array("id" => 0)));
	}
	
	public function editarAction() {
		$id = $this->_request->getParam("id");
		$tb = new TbIcone();
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
				$registro->save();
				$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}  
		}
		$this->view->registro = $registro;
		$button = Escola_Button::getInstance();
		if ($this->view->registro->getId()) {
			$button->setTitulo("CADASTRO DE ÍCONE - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE ÍCONE - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbIcone();
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
		$tb = new TbIcone();
		$registro = $tb->getPorId($this->_request->getParam("id"));
		if ($registro->getId()) {
			$this->view->registro = $registro;
			$button = Escola_Button::getInstance();
			$button->setTitulo("VISUALIZAR ÍCONE");
			$button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
    
    public function importarAction() {
        $arquivo = Escola_Util::getUploadedFile("arquivo");
        if ($arquivo["size"]) {
            $tb = new TbIcone();
            $tb->importar_arquivo($arquivo);
            $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
        } else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }
}