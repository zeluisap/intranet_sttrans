<?php
class UsuarioController extends Escola_Controller_Logado
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("listarporpagina", "json");
		$ajaxContext->initContext();
	}

	public function listarporpaginaAction()
	{
		$result = false;
		$tb = new TbUsuario();
		$dados = $this->getRequest()->getPost();
		$dados["filtro_situacao"] = "A";
		$registros = $tb->listar($dados);
		$info = $registros->getPages();
		$this->view->items = false;
		$this->view->total_pagina = $info->pageCount;
		$this->view->pagina_atual = $info->current;
		$this->view->primeira = $info->first;
		$this->view->ultima = $info->last;
		if ($registros && count($registros)) {
			$items = array();
			foreach ($registros as $registro) {
				$registro = TbUsuario::pegaPorId($registro["id_usuario"]);
				$us = $registro->findParentRow("TbUsuarioSituacao");
				$pf = $registro->pega_pessoa_fisica();
				$obj = new stdClass();
				$obj->id = $registro->getId();
				$obj->nome = $pf->nome;
				$obj->situacao = $us->toString();
				$obj->cpf = Escola_Util::formatCpf($pf->cpf);
				$items[] = $obj;
			}
			$this->view->items = $items;
		}
	}

	public function indexAction()
	{
		$tb = new TbUsuario();
		$dados = array("pagina_atual" => $this->_getParam("page"));
		if ($this->_request->isPost()) {
			$dados = array_merge($dados, $this->_request->getPost());
		}
		$this->view->registros = $tb->listar($dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("USUÁRIOS");
		$button->addFromArray(array(
			"titulo" => "Adicionar",
			"controller" => $this->_request->getControllerName(),
			"action" => "editar",
			"img" => "icon-plus-sign",
			"params" => array("id" => 0)
		));
		$button->addFromArray(array(
			"titulo" => "Pesquisar",
			"onclick" => "pesquisar()",
			"img" => "icon-search",
			"params" => array("id" => 0)
		));
		$button->addFromArray(array(
			"titulo" => "Voltar",
			"controller" => "intranet",
			"action" => "index",
			"img" => "icon-reply",
			"params" => array("id" => 0)
		));
	}

	public function viewAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbUsuario();
			$usuario = $tb->getPorId($id);
			if ($usuario) {
				$this->view->registro = $usuario;
				$button = Escola_Button::getInstance();
				$button->setTitulo("USUÁRIO");
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

	public function editarAction()
	{
		$id = $this->_request->getParam("id");
		$tb = new TbUsuario();
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
		$municipio = $this->view->registro->getPessoaFisica()->findParentRow("TbMunicipio");
		$id_pais = $id_uf = null;
		if ($municipio) {
			$id_uf = $municipio->id_uf;
			$uf = $municipio->findParentRow("TbUf");
			if ($uf) {
				$id_pais = $uf->id_pais;
			}
		}
		$this->view->id_pais = $id_pais;
		$this->view->id_uf = $id_uf;
		$this->view->id_municipio = null;
		if ($municipio) {
			$this->view->id_municipio = $municipio->getId();
		}
		$button = Escola_Button::getInstance();
		if ($this->view->registro->getId()) {
			$button->setTitulo("CADASTRO DE USUÁRIOS - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE USUÁRIOS - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-reply");
	}

	public function excluirAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbUsuario();
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

	public function gruposAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbUsuario();
			$usuario = $tb->getPorId($id);
			if ($usuario) {
				$tb = new TbGrupo();
				if ($this->_request->isPost()) {
					$ids = $this->_request->getPost("lista_usuarios_grupo");
					$usuario->limparTbGrupo();
					if (is_array($ids) && count($ids)) {
						foreach ($ids as $id_grupo) {
							$grupo = $tb->getPorId($id_grupo);
							$usuario->addGrupo($grupo);
						}
					}
					$this->view->actionMessages[] = "OPERAÇÃO EFETUADA COM SUCESSO!";
				}
				$this->view->usuario = $usuario;
				$grupos = $tb->listar(array());
				$this->view->usuario_grupos = $this->view->usuario->getTbGrupo();
				$this->view->grupos = array();
				if ($grupos) {
					foreach ($grupos as $grupo) {
						if (!$this->view->usuario_grupos || !in_array($grupo, $this->view->usuario_grupos)) {
							$this->view->grupos[] = $grupo;
						}
					}
				}
				$button = Escola_Button::getInstance();
				$button->setTitulo("GRUPOS POR USUÁRIO");
				$button->addScript("Salvar", "salvar()", "icon-save");
				$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
			} else {
				$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
}
