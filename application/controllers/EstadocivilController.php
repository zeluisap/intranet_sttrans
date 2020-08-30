<?php
class EstadocivilController extends Escola_Controller
{
	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("salvar", "json");
		$ajaxContext->addActionContext("listar", "json");
		$ajaxContext->initContext();
	}

	public function salvarAction()
	{
		$result = new stdClass;
		$result->mensagem = false;
		$result->id = 0;
		$result->descricao = "";
		$tb = new TbEstadoCivil();
		$ec = $tb->createRow();
		$dados = $this->getRequest()->getPost();
		$ec->setFromArray($dados);
		$errors = $ec->getErrors();
		if ($errors) {
			$result->mensagem = implode("<br>", $errors);
		} else {
			$id = $ec->save();
			if ($id) {
				$result->id = $id;
				$result->descricao = $ec->descricao;
			}
		}
		$this->view->result = $result;
	}

	public function listarAction()
	{
		$result = false;
		$tb = new TbEstadoCivil();
		$registros = $tb->listar();
		if ($registros && $registros->count()) {
			$result = array();
			foreach ($registros as $registro) {
				$obj = new stdClass();
				$obj->id = $registro->getId();
				$obj->descricao = $registro->descricao;
				$result[] = $obj;
			}
		}
		$this->view->result = $result;
	}
}
