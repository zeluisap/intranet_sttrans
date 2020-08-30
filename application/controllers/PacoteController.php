<?php
class PacoteController extends Escola_Controller_Logado {
	
	public function indexAction() {
		$tb = new TbPacote();
		$page = $this->_getParam("page");
		$this->view->registros = $tb->listar_por_pagina(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("Pacotes");
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
        $tb = new TbPacote();
		$registro = TbPacote::pegaPorId($this->_request->getParam("id"));
		if (!$registro) {
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
		if ($registro) {
			$this->view->registro = $registro;
		}
        $this->view->ultima_ordem = 0;
		$button = Escola_Button::getInstance();
		if ($registro->getId()) {
			$button->setTitulo("Cadastro de Pacote - Alterar");
            $this->view->ultima_ordem = $tb->pegaUltimaOrdem();
		} else {
			$button->setTitulo("Cadastro de Pacote - Inserir");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$registro = TbPacote::pegaPorId($this->_request->getParam("id"));
		if ($registro) {
			$errors = $registro->getDeleteErrors();
			if ($errors) {
				foreach ($errors as $erro) {
					$this->_flashMessage($erro);
				}
			} else {
				$registro->delete();
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
		}
		$this->_redirect($this->_request->getControllerName() . "/index");
	}
	
	public function viewAction() {
		$registro = TbPacote::pegaPorId($this->_request->getParam("id"));
		if ($registro) {
			$this->view->registro = $registro;
			$button = Escola_Button::getInstance();
			$button->setTitulo("Visualizar Pacote");
			$button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
	
	public function moduloAction() {
		$pacote = TbPacote::pegaPorId($this->_request->getParam("id"));
		if ($pacote) {
			if ($this->_request->isPost()) {
				$pacote->limparModulos();
				$dados = $this->_request->getPost();
				if (isset($dados["lista_modulos"]) && is_array($dados["lista_modulos"]) && count($dados["lista_modulos"])) {
					foreach ($dados["lista_modulos"] as $id_modulo) {
						$modulo = TbModulo::pegaPorId($id_modulo);
						if ($modulo) {
							$pacote->addModulo($modulo);
						}
					}
				}
				$this->view->actionMessages[] = "Operação Efetuada com Sucesso!";
			}
			$tb = new TbModulo();
			$modulos = $tb->listarTodos();
			if ($modulos) {
				$this->view->pacote = $pacote;
				$this->view->modulos = $modulos;
				$button = Escola_Button::getInstance();
				$button->setTitulo("Pacotes");
				$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
				$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
			} else {
				$this->_flashMessage("Falha ao Executar a Operação, Nenhum Módulo Disponível!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else { 
			$this->_flashMessage("Falha ao Executar a Operação, Dados Inválidos!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
}