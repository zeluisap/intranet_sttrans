<?php
class RequerimentoController extends Escola_Controller_Default
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("get", "json");
		$ajaxContext->addActionContext("salvar", "json");
		$ajaxContext->addActionContext("indeferir", "json");
		$ajaxContext->addActionContext("deferir", "json");
		$ajaxContext->initContext();
	}

	public function getAction()
	{

		$result = [];

		try {

			$params = $this->getRequest()->getPost();

			$id_requerimento = Escola_Util::valorOuNulo($params, "id_requerimento");
			if (!$id_requerimento) {
				throw new Escola_Exception("Falha ao localizar requerimento.");
			}

			$requerimento = TbRequerimento::pegaPorId($id_requerimento);
			if (!$requerimento) {
				throw new Escola_Exception("Falha ao localizar requerimento.");
			}

			$result = $requerimento->toArray();
		} catch (Exception $ex) {
			$result["erro"] = $ex->getMessage();
		}

		$this->view->result = $result;
	}

	public function salvarAction()
	{

		$db = Zend_Registry::get("db");
		$db->beginTransaction();
		try {

			$result = [];

			$tb = new TbRequerimento();
			$params = $this->getRequest()->getPost();

			$id_pf = Escola_Util::valorOuNulo($params, "pessoa->id_pessoa_fisica");
			if (!$id_pf) {
				throw new Exception("Não foi possível identificar o solicitante do requerimento.");
			}

			$pf = TbPessoaFisica::pegaPorId($id_pf);
			if (!$pf) {
				throw new Exception("Não foi possível identificar o solicitante do requerimento!");
			}

			$servicos = Escola_Util::valorOuNulo($params, "servicos");
			if (!($servicos && is_array($servicos) && count($servicos))) {
				throw new Exception("Não foi possível identificar serviço requerido!");
			}

			$tb = new TbRequerimento();
			$requerimento = null;
			$id = Escola_Util::valorOuNulo($params, "requerimento->id");
			if ($id) {
				$requerimento = TbRequerimento::pegaPorId($id);
				if (!$requerimento) {
					throw new Exception("Identificador do requerimento é inválido!");
				}
			} else {
				$requerimento = $tb->createRow();
			}

			$requerimento->id_pessoa = $pf->id_pessoa;

			$requerimento->save();

			$tb_req_item = new TbRequerimentoItem();
			$ids = [];
			foreach ($servicos as $servico) {
				if ($req_item = $tb_req_item->salvarServicoAvulso($requerimento, $servico)) {
					$ids[] = $req_item->getId();
				}
				if ($req_item = $tb_req_item->salvarServico($requerimento, $servico)) {
					$ids[] = $req_item->getId();
				}
			}

			if (count($ids)) {
				$sql = "
					delete from requerimento_item 
					where (id_requerimento = :id_requerimento)
					and (not id_requerimento_item in (" . implode(", ", $ids) . "))
				";
				Escola_DbUtil::query($sql, [
					"id_requerimento" => $requerimento->getId()
				], $db);
			}


			$result["requerimento"] = $requerimento->toObjeto();

			$db->commit();
		} catch (Exception $ex) {
			$db->rollBack();
			$result["erro"] = $ex->getMessage();
		}

		$this->view->result = $result;
	}

	public function finalizaranaliseAction()
	{

		$result = [];

		$db = Zend_Registry::get("db");
		$db->beginTransaction();
		try {

			$params = $this->getRequest()->getPost();

			if (!$id = Escola_Util::valorOuNulo($params, "id_requerimento")) {
				throw new Error("Requerimento não identificado.");
			}

			if (!$req = TbRequerimento::pegaPorId($id)) {
				throw new Error("Requerimento não identificado.");
			}

			$req->finalizarAnalise();

			$db->commit();

			return $req;
		} catch (Exception $ex) {
			$db->rollBack();
			$result["erro"] = $ex->getMessage();
		}

		$this->view->result = $result;
	}

	public function deferirtodosAction()
	{

		$result = [];

		$db = Zend_Registry::get("db");
		$db->beginTransaction();
		try {

			$params = $this->getRequest()->getPost();

			if (!$id = Escola_Util::valorOuNulo($params, "id")) {
				throw new Escola_Exception("Requerimento não informado.");
			}

			if (!$req = TbRequerimento::pegaPorId($id)) {
				throw new Escola_Exception("Requerimento não informado.");
			}

			if (!$req->pendente()) {
				throw new Escola_Exception("Requerimento já analisado.");
			}

			$itens = $req->getItens();
			if ($itens && is_array($itens) && count($itens)) {
				foreach ($itens as $item) {
					$item->situacao = Requerimento::$SITUACAO_DEFERIDO;
					$item->indeferimento_motivo = null;
					$item->save();
				}
			}

			$result["requerimento"] = $req->toArray();

			$db->commit();
		} catch (Exception $ex) {
			$db->rollBack();
			$result["erro"] = $ex->getMessage();
		}

		$this->view->result = $result;
	}

	public function deferirAction()
	{

		$result = [];

		$db = Zend_Registry::get("db");
		$db->beginTransaction();
		try {

			$params = $this->getRequest()->getPost();

			if (!$item = Escola_Util::valorOuNulo($params, "item")) {
				throw new Escola_Exception("Ítem de requerimento não informado.");
			}

			if (!$id = Escola_Util::valorOuNulo($item, "id")) {
				throw new Escola_Exception("Ítem de requerimento não informado.");
			}

			if (!$req_item = TbRequerimentoItem::pegaPorId($id)) {
				throw new Escola_Exception("Ítem de requerimento não informado.");
			}

			if (!$req = $req_item->getRequerimento()) {
				throw new Escola_Exception("Requerimento não localizado.");
			}

			if (!$req->pendente()) {
				throw new Escola_Exception("Requerimento já analisado.");
			}

			$req_item->situacao = Requerimento::$SITUACAO_DEFERIDO;
			$req_item->indeferimento_motivo = null;

			$req_item->save();

			$result["item"] = $req_item->toArray();

			$db->commit();
		} catch (Exception $ex) {
			$db->rollBack();
			$result["erro"] = $ex->getMessage();
		}

		$this->view->result = $result;
	}

	public function indeferirAction()
	{

		$result = [];

		$db = Zend_Registry::get("db");
		$db->beginTransaction();
		try {

			$params = $this->getRequest()->getPost();

			if (!$id = Escola_Util::valorOuNulo($params, "id_requerimento")) {
				throw new Escola_Exception("Requerimento não localizado.");
			}

			$req = TbRequerimento::pegaPorId($id);
			if (!$req) {
				throw new Escola_Exception("Requerimento não localizado.");
			}

			if (!$req->pendente()) {
				throw new Escola_Exception("Requerimento já analisado.");
			}

			if (!$motivo = Escola_Util::valorOuNulo($params, "motivo")) {
				throw new Escola_Exception("Motivo do indeferimento não informado.");
			}

			$itens = [];
			$item = Escola_Util::valorOuNulo($params, "item");
			if ($item) {
				if (!$item_id = Escola_Util::valorOuNulo($item, "id")) {
					throw new Escola_Exception("Ítem do requerimento não identificado.");
				}
				$it = TbRequerimentoItem::pegaPorId($item_id);
				if (!$it) {
					throw new Escola_Exception("Ítem do requerimento não identificado.");
				}
				$itens = [$it];
			} else {
				$itens = $req->getItens();
			}

			if ($itens && is_array($itens) && count($itens)) {
				foreach ($itens as $item) {
					$item->situacao = Requerimento::$SITUACAO_INDEFERIDO;
					$item->indeferimento_motivo = $motivo;
					$item->save();
				}
			}

			$result["requerimento"] = $req->toArray();

			$db->commit();
		} catch (Exception $ex) {
			$db->rollBack();
			$result["erro"] = $ex->getMessage();
		}

		$this->view->result = $result;
	}

	public function indexAction()
	{

		$dados = $this->atualizaFiltros([
			"filtro_numero",
			"filtro_ano",
			"filtro_nome",
			"filtro_situacao",
			"filtro_data_criacao_inicio",
			"filtro_data_criacao_fim",
			"page"
		]);

		$tb = new TbRequerimento();

		$this->view->registros = $tb->listar_por_pagina($dados);
		$this->view->dados = $dados;

		$button = Escola_Button::getInstance();
		$button->setTitulo("REQUERIMENTO");
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
		try {
			$id = $this->_request->getParam("id");
			$tb = new TbRequerimento();

			if ($id) {
				$registro = $tb->getPorId($id);
				if (!$registro->pendente()) {
					throw new Escola_Exception("Somente é permitido editar requerimentos pendentes.");
				}
			} else {
				$registro = $tb->createRow();
			}

			$this->view->registro = $registro;

			$button = Escola_Button::getInstance();
			if ($this->view->registro->getId()) {
				$button->setTitulo("CADASTRO REQUERIMENTO - ALTERAR");
			} else {
				$button->setTitulo("CADASTRO REQUERIMENTO - INSERIR");
			}
			$button->addScript("Salvar", "salvarForm()", "icon-save", [], 'salvar', 'btn-topo');
			$button->addScript("Cancelar", "cancelar()", "icon-remove-circle", [], 'cancelar', 'btn-topo');
			$button->addScript("Imprimir", "imprimir()", "icon-print", [], 'imprimir', 'btn-topo');
		} catch (Exception $ex) {
			$this->_flashMessage($ex->getMessage());
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function excluirAction()
	{
		$db = Zend_Registry::get("db");
		$db->beginTransaction();

		try {
			$id = $this->_request->getParam("id");
			if (!$id) {
				throw new Escola_Exception("NENHUMA INFORMAÇÃO RECEBIDA!");
			}

			$registro = TbRequerimento::pegaPorId($id);
			if (!$registro) {
				throw new Escola_Exception("INFORMAÇÃO RECEBIDA INVÁLIDA!");
			}

			$registro->delete();

			$db->commit();

			$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
			$this->_redirect($this->_request->getControllerName() . "/index");
		} catch (Exception $ex) {
			$db->rollBack();

			$this->_flashMessage($ex->getMessage());
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function viewAction()
	{

		try {

			$id = $this->_request->getParam("id");
			if (!$id) {
				throw new Escola_Exception("Nenhum identificador informado.");
			}

			$tb = new TbRequerimento();
			$registro = $tb->getPorId($id);
			if (!$registro) {
				throw new Escola_Exception("Informação Recebida Inválida!");
			}

			$this->view->registro = $registro;

			$button = Escola_Button::getInstance();
			$button->setTitulo("VISUALIZAR REQUERIMENTO");

			$button->addFromArray(array(
				"titulo" => "Analisar",
				"controller" => $this->_request->getControllerName(),
				"action" => "analise",
				"img" => "icon-edit",
				"params" => array("id" => $id)
			));

			$button->addFromArray(array(
				"titulo" => "Imprimir",
				"controller" => $this->_request->getControllerName(),
				"action" => "imprimir",
				"img" => "icon-print",
				"params" => array("id" => $id)
			));

			$button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
		} catch (Exception $ex) {
			$this->_flashMessage($ex->getMessage());
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function imprimirAction()
	{

		try {

			$id = $this->_request->getParam("id");
			if (!$id) {
				throw new Exception("Nenhum identificador informado.");
			}

			$tb = new TbRequerimento();
			$registro = $tb->getPorId($id);
			if (!$registro) {
				throw new Exception("Informação Recebida Inválida!");
			}

			$relatorio = new Escola_Relatorio_Requerimento();
			$relatorio->imprimir($registro);
		} catch (Escola_Exception $ex) {
			$this->_flashMessage($ex->getMessage());
			$this->_redirect($this->_request->getControllerName() . "/view/id/" . $id);
		} catch (Exception $ex) {
			$this->_flashMessage($ex->getMessage());
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function analiseAction()
	{

		try {

			$id = $this->_request->getParam("id");
			if (!$id) {
				throw new Escola_Exception("Nenhum identificador informado.");
			}

			$tb = new TbRequerimento();
			$registro = $tb->getPorId($id);
			if (!$registro) {
				throw new Escola_Exception("Informação Recebida Inválida!");
			}

			if (!$registro->pendente()) {
				throw new Escola_Exception("Apenas requerimentos pendentes podem ser analisados!");
			}

			$this->view->registro = $registro;

			$button = Escola_Button::getInstance();
			$button->setTitulo("ANALISANDO REQUERIMENTO");
			$button->addScript("FinalizarAnalise", "finalizarAnalise()", "icon-save", [], 'salvar', 'btn-topo');
			$button->addScript("Deferir Todos", "deferirTodos()", "icon-thumbs-up", [], 'deferir');
			$button->addScript("Indeferir Todos", "indeferirTodos()", "icon-thumbs-down");
			$button->addVoltar($this);
		} catch (Exception $ex) {

			$this->_flashMessage($ex->getMessage());

			$this->voltar();
		}
	}

	private function voltar()
	{
		$anterior = $this->getActionAnterior();
		if (!$anterior) {
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
		$this->_redirect($this->view->url($anterior, null, true));
	}
}
