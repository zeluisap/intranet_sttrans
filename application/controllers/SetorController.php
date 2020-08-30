<?php
class SetorController extends Escola_Controller_Logado
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("instituicaoporpagina", "json");
		$ajaxContext->addActionContext("listarporpagina", "json");
		$ajaxContext->addActionContext("salvar", "json");
		$ajaxContext->initContext();
	}

	public function salvarAction()
	{
		$this->view->id = false;
		$this->view->erro = "";
		$this->view->procedencia = "";

		$dados = $this->getRequest()->getPost();
		$tb = new TbSetorTipo();
		$st = $tb->getPorChave("E");
		$dados["id_setor_tipo"] = $st->getId();
		if (!isset($dados["setor_nivel"]) || !$dados["setor_nivel"]) {
			$dados["setor_nivel"] = "T";
		}
		$tb = new TbSetorNivel();
		$sn = $tb->getPorChave($dados["setor_nivel"]);
		if ($sn) {
			$dados["id_setor_nivel"] = $sn->getId();
		}
		$tb = new TbSetor();
		$setor = $tb->createRow();
		$dados = array_map("utf8_decode", $dados);
		$setor->setFromArray($dados);
		$errors = $setor->getErrors();
		if ($errors) {
			$this->view->erro = implode("<br>", $errors);
		} else {
			$id = $setor->save();
			if ($id) {
				$this->view->id = $id;
				$this->view->procedencia = $setor->toString();
				$this->view->descricao = $setor->toString();
			} else {
				$this->view->erro = "FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!";
			}
		}
	}

	public function instituicaoporpaginaAction()
	{
		$result = false;
		$tb = new TbSetorNivel();
		$sn = $tb->getPorChave("T");
		$tb = new TbSetor();
		$dados = $this->getRequest()->getPost();
		$dados["filtro_id_setor_nivel"] = $sn->getId();
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
				$obj = new stdClass();
				$obj->id = $registro->getId();
				$obj->sigla = $registro->sigla;
				$obj->descricao = $registro->descricao;
				$items[] = $obj;
			}
			$this->view->items = $items;
		}
	}

	public function listarporpaginaAction()
	{
		$superior = false;
		$dados = $this->getRequest()->getPost();
		if (isset($dados["filtro_id_setor_procedencia"]) && $dados["filtro_id_setor_procedencia"]) {
			$tb = new TbSetor();
			$procedencia = $tb->pegaPorId($dados["filtro_id_setor_procedencia"]);
			if ($procedencia->findParentRow("TbSetorTipo")->interno() && $procedencia->findParentRow("TbSetorNivel")->eInstituicao()) {
				$tb = new TbSetorTipo();
				$st = $tb->getPorChave("I");
				if ($st) {
					$dados["filtro_id_setor_tipo"] = $st->getId();
				}
			} else {
				$dados["filtro_id_setor_superior"] = $procedencia->getId();
			}
		}
		if (isset($dados["setor_tipo"]) && $dados["setor_tipo"]) {
			$tb = new TbSetorTipo();
			$st = $tb->getPorChave($dados["setor_tipo"]);
			if ($st) {
				$dados["filtro_id_setor_tipo"] = $st->getId();
			}
		}
		$tb = new TbSetor();
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
				$obj = new stdClass();
				$obj->id = $registro->getId();
				$obj->sigla = $registro->sigla;
				$obj->descricao = $registro->descricao;
				$items[] = $obj;
			}
			$this->view->items = $items;
		}
	}

	public function indexAction()
	{
		$tb = new TbSetor();
		$dados = $this->_request->getParams();
		$dados["pagina_atual"] = $this->_getParam("page");
		$this->view->registros = $tb->listar($dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("SETORES");
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

	public function editarAction()
	{
		$id = $this->_request->getParam("id");
		$tb = new TbSetor();
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
			$button->setTitulo("CADASTRO DE SETOR - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE SETOR - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}

	public function excluirAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbSetor();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$rows = $registro->findDependentRowSet("TbSetor");
				if (!count($rows)) {
					$lotacaos = $registro->pegaLotacao();
					if ($lotacaos) {
						$this->_flashMessage("EXISTEM FUNCIONÁRIOS LOTADOS NESTE SETOR, EXCLUA AS LOTAÇÕES E TENTE NOVAMENTE!");
					} else {
						$registro->delete();
						$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
					}
				} else {
					$this->_flashMessage("EXISTEM OUTROS SETORES VINCULADOS A ESTE, EXCLUA OS VÍNCULOS E TENTE NOVAMENTE!");
				}
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
		}
		$this->_redirect($this->_request->getControllerName() . "/index");
	}

	public function viewAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbSetor();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR SETOR");
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

	public function funcionarioAction()
	{
		$tb = new TbSetor();
		$this->view->setor = $tb->getPorId($this->_request->getParam("id"));
		$dados = $this->_request->getParams();
		$dados["filtro_id_setor"] = $this->view->setor->getId();
		$tb = new TbLotacao();
		$this->view->registros = $tb->listar($dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("FUNCIONÁRIOS");
		$button->addFromArray(array(
			"titulo" => "Adicionar",
			"onclick" => "add_usuario()",
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
			"controller" => $this->_request->getControllerName(),
			"action" => "index",
			"img" => "icon-reply",
			"params" => array("id" => 0)
		));
	}

	public function addfuncionarioAction()
	{
		$tb = new TbSetor();
		$this->view->setor = $tb->getPorId($this->_request->getParam("id"));
		$dados = $this->_request->getParams();
		if ($this->view->setor->getId()) {
			if (isset($dados["cpf"]) && $dados["cpf"]) {
				$dados["jan_cpf"] = $dados["cpf"];
				$tb = new TbLotacao();
				$lotacao = $tb->getPorId($dados["id_lotacao"]);
				if (!$lotacao) {
					$lotacao = $tb->createRow();
				}
				$lotacao->setFromArray($dados);
				$errors = $lotacao->getErrors();
				if (!$errors) {
					$id_lotacao = $lotacao->save();
					if ($id_lotacao) {
						$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
						$this->_redirect("setor/funcionario/id/" . $this->view->setor->getId());
					} else {
						$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
					}
				} else {
					$this->view->actionErrors = $errors;
				}
			}
			if (isset($dados["jan_cpf"])) {
				$tb = new TbPessoaFisica();
				$pf = $tb->getPorCPF($dados["jan_cpf"]);
				if (!$pf) {
					$pf = $tb->createRow();
					$pf->cpf = $dados["jan_cpf"];
				}
				$pf->setFromArray($dados);
				$tb = new TbFuncionario();
				$funcionario = $tb->getPorPessoaFisica($pf);
				if (!$funcionario) {
					$funcionario = $tb->createRow();
				}
				$funcionario->setFromArray($dados);
				$lotacaos = $this->view->setor->pegaLotacao($funcionario);
				if ($lotacaos) {
					$lotacao = $lotacaos[0];
				} else {
					$tb = new TbLotacao();
					$lotacao = $tb->createRow();
				}
				$lotacao->setFromArray($dados);
				$this->view->pf = $pf;
				$this->view->funcionario = $funcionario;
				$this->view->lotacao = $lotacao;
				$lots = $funcionario->pegaLotacao("N");
				if ($lots) {
					$this->view->lotacao_atual = $lots[0];
				}
				$button = Escola_Button::getInstance();
				if ($this->view->funcionario->getId()) {
					$button->setTitulo("CADASTRO DE FUNCIONÁRIO - ALTERAR");
				} else {
					$button->setTitulo("CADASTRO DE FUNCIONÁRIO - INSERIR");
				}
				$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
				$button->addAction("Cancelar", $this->_request->getControllerName(), "funcionario", "icon-remove-circle", array("id" => $this->view->setor->getId()));
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function excluirlotacaoAction()
	{
		$id = $this->_request->getParam("id_lotacao");
		if ($id) {
			$tb = new TbLotacao();
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
		$this->_redirect($this->_request->getControllerName() . "/funcionario/id/" . $this->_request->getParam("id"));
	}

	public function viewlotacaoAction()
	{
		$tb = new TbSetor();
		$this->view->setor = $tb->getPorId($this->_request->getParam("id"));
		$id = $this->_request->getParam("id_lotacao");
		if ($id) {
			$tb = new TbLotacao();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$this->view->funcionario = $registro->pega_funcionario();
				$this->view->pf = $this->view->funcionario->pega_pessoa_fisica();
				$this->view->pessoa = $this->view->pf->pega_pessoa();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR FUNCIONÁRIO");
				$button->addAction("Voltar", $this->_request->getControllerName(), "funcionario", "icon-reply", array("id" => $this->view->setor->getId()));
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
