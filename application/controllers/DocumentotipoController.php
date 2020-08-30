<?php
class DocumentotipoController extends Escola_Controller_Logado
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("dados", "json");
		$ajaxContext->initContext();
	}

	public function dadosAction()
	{
		$result = false;
		$tb = new TbDocumentoTipo();
		$id = $this->getRequest()->getParam("id");
		if ($id) {
			$dt = $tb->pegaPorId($id);
			if ($dt) {
				$result = new stdClass();
				$result->chave = $dt->chave;
				$result->descricao = $dt->descricao;
				$result->id_documento_tipo_target = $dt->id_documento_tipo_target;
				$result->possui_numero = $dt->possui_numero();
			}
		}
		$this->view->result = $result;
	}

	public function indexAction()
	{
		$tb = new TbDocumentoTipoTarget();
		$tb->recuperar();
		$tb = new TbDocumentoTipo();
		$page = $this->_getParam("page");
		$this->view->registros = $tb->listarPorPagina(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("TIPOS DE DOCUMENTOS");
		$button->addFromArray(array(
			"titulo" => "Adicionar",
			"controller" => $this->_request->getControllerName(),
			"action" => "editar",
			"img" => "icon-plus-sign",
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
		$tb = new TbDocumentoTipo();
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
			$button->setTitulo("CADASTRO DE TIPOS DE DOCUMENTO- ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE TIPOS DE DOCUMENTO - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}

	public function excluirAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbDocumentoTipo();
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

	public function viewAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbDocumentoTipo();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR TIPO DE DOCUMENTO");
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
}
