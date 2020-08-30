<?php
class MensagemController extends Escola_Controller_Logado
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("adicionar", "json");
		$ajaxContext->initContext();
	}

	public function indexAction()
	{
		$tb = new TbMensagem();
		$page = $this->_getParam("page");
		$id = $this->_getParam("id");
		$usuario = Escola_Acl::getInstance()->getUsuarioLogado();
		$tb = new TbFuncionario();
		$funcionario = $tb->getPorUsuario($usuario);
		if ($funcionario) {
			$tb = new TbMensagem();
			$this->view->registros = $tb->listar(array("pagina_atual" => $page, "funcionario" => $funcionario));
			$button = Escola_Button::getInstance();
			$button->setTitulo("MINHAS MENSAGENS");
			$button->addFromArray(array(
				"titulo" => "Adicionar",
				"controller" => $this->_request->getControllerName(),
				"action" => "editar",
				"img" => "icon-plus-sign",
				"params" => array("id_mensagem" => 0)
			));
			$button->addFromArray(array(
				"titulo" => "Voltar",
				"controller" => "intranet",
				"action" => "index",
				"img" => "icon-reply",
				"params" => array("id_mensagem" => 0)
			));
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO!");
			$this->_redirect("index");
		}
	}

	public function entradaAction()
	{
		$tb = new TbMensagem();
		$page = $this->_getParam("page");
		$id = $this->_getParam("id");
		$funcionario = TbFuncionario::pegaPorId($id);
		if ($funcionario) {
			$this->view->registros = $tb->buscarEntrada(array("pagina_atual" => $page, "funcionario" => $funcionario));
			$button = Escola_Button::getInstance();
			$button->setTitulo("CAIXA DE ENTRADA");
			$button->addFromArray(array(
				"titulo" => "Voltar",
				"controller" => "index",
				"action" => "index",
				"img" => "icon-reply",
				"params" => array("id" => 0)
			));
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO!");
			$this->_redirect("index");
		}
	}

	public function adicionarAction()
	{
		$this->view->mensagem = false;
		$dados = $this->_request->getPost();
		$tb = new TbMensagem();
		$registro = $tb->createRow();
		$tb = new TbMensagemTipo();
		$mt = $tb->getPorChave("P");
		if ($mt) {
			$dados["id_mensagem_tipo"] = $mt->getId();
		}
		$registro->setFromArray($dados);
		$errors = $registro->getErrors();
		if ($errors) {
			$this->view->mensagem = implode("<br>", $errors);
		} else {
			$id = $registro->save();
			if (!$id) {
				$this->view->mensagem = "FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!";
			}
		}
	}

	public function visualizarAction()
	{
		$id = $this->_request->getParam("id_mensagem");
		$registro = TbMensagem::pegaPorId($id);
		if ($registro) {
			$usuario = Escola_Acl::getInstance()->getUsuarioLogado();
			$registro->ler($usuario);
			$this->view->registro = $registro;
			$button = Escola_Button::getInstance();
			$button->setTitulo("CAIXA DE ENTRADA");
			$button->addScript("Responder", "responder()", "icon-comments-alt");
			$button->addFromArray(array(
				"titulo" => "Voltar",
				"controller" => $this->_request->getControllerName(),
				"action" => "entrada",
				"img" => "icon-reply",
				"params" => array("id" => $this->_getParam("id"))
			));
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect("index");
		}
	}

	public function responderAction()
	{
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$mensagem = TbMensagem::pegaPorId($dados["id"]);
			$usu = $mensagem->findParentRow("TbUsuario");
			$tb = new TbFuncionario();
			$func = $tb->getPorUsuario($usu);
			$dados["chave_destino"] = $func->getId();
			$logado = Escola_Acl::getInstance()->getUsuarioLogado();
			$dados["id_usuario"] = $logado->getId();
			$tb = new TbMensagem();
			$registro = $tb->createRow();
			$tb = new TbMensagemTipo();
			$mt = $tb->getPorChave("P");
			if ($mt) {
				$dados["id_mensagem_tipo"] = $mt->getId();
			}
			$registro->setFromArray($dados);
			$errors = $registro->getErrors();
			if ($errors) {
				$this->view->mensagem = implode("<br>", $errors);
			} else {
				$id = $registro->save();
				if ($id) {
					$tb = new TbFuncionario();
					$funcionario = $tb->getPorUsuario($logado);
					$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
					$this->_redirect("mensagem/entrada/id/" . $funcionario->getId());
				} else {
					$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
				}
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect("index");
		}
	}

	public function viewAction()
	{
		$id = $this->_request->getParam("id_mensagem");
		$registro = TbMensagem::pegaPorId($id);
		if ($registro) {
			$this->view->registro = $registro;
			$button = Escola_Button::getInstance();
			$button->setTitulo("MINHAS MENSAGENS");
			$button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect("index");
		}
	}

	public function excluirAction()
	{
		$id = $this->_request->getParam("id_mensagem");
		$registro = TbMensagem::pegaPorId($id);
		if ($registro) {
			$registro->delete();
			$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
		}
		$this->_redirect("mensagem/index");
	}

	public function editarAction()
	{
		$id = $this->_request->getParam("id_mensagem");
		$tb = new TbMensagem();
		if ($id) {
			$registro = $tb->getPorId($id);
		} else {
			$registro = $tb->createRow();
		}
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			unset($dados["id_mensagem"]);
			$registro->setFromArray($dados);
			if (!$registro->id_usuario) {
				$usuario = Escola_Acl::getInstance()->getUsuarioLogado();
				if ($usuario) {
					$registro->id_usuario = $usuario->getId();
				}
			}
			$mt = $registro->findParentRow("TbMensagemTipo");
			if ($mt) {
				if ($mt->pessoal()) {
					$id_pf = $this->_request->getPost("id_pessoa_fisica");
					if ($id_pf) {
						$registro->chave_destino = $id_pf;
					}
				} elseif ($mt->setor()) {
					$id_setor = $this->_request->getPost("id_setor");
					if ($id_setor) {
						$registro->chave_destino = $id_setor;
					}
				}
			}
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
			$button->setTitulo("MINHAS MENSAGENS - ALTERAR");
		} else {
			$button->setTitulo("NOVA MENSAGEM");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
}
