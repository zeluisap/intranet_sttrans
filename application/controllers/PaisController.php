<?php
class PaisController extends Escola_Controller
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("listar", "json");
		$ajaxContext->addActionContext("salvar", "json");
		$ajaxContext->initContext();
	}

	public function listarAction()
	{
		$result = false;
		$tb = new TbPais();
		$registros = $tb->listar($this->getRequest()->getPost());
		if ($registros && $registros->count()) {
			$result = array();
			foreach ($registros as $registro) {
				$obj = new stdClass();
				$obj->id = $registro->getId();
				$obj->descricao = $registro->toString();
				$result[] = $obj;
			}
		}
		$this->view->result = $result;
	}

	public function salvarAction()
	{
		$result = new stdClass();
		$result->mensagem = false;
		$result->id = 0;
		$dados = $this->_request->getPost();
		$tb = new TbPais();
		$registro = $tb->createRow();
		$registro->setFromArray(array("descricao" => utf8_decode($dados["descricao"])));
		$errors = $registro->getErrors();
		if (!$errors) {
			$registro->save();
			$result->id = $registro->getId();
		} else {
			$result->mensagem = implode("<br>", $errors);
		}
		$this->view->result = $result;
	}
}
