<?php
class InfracaoController extends Escola_Controller_Logado
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("listarporpagina", "json");
		$ajaxContext->initContext();
	}

	public function listarporpaginaAction()
	{
		$superior = false;
		$dados = $this->getRequest()->getPost();
		$tb = new TbInfracao();
		$registros = $tb->listar_por_pagina($dados);
		$info = $registros->getPages();
		$this->view->items = false;
		$this->view->total_pagina = $info->pageCount;
		$this->view->pagina_atual = $info->current;
		$this->view->primeira = $info->first;
		$this->view->ultima = $info->last;
		if ($registros && count($registros)) {
			$items = array();
			foreach ($registros as $registro) {
				$valor = $registro->pega_valor();
				$obj = new stdClass();
				$obj->id = $registro->getId();
				$obj->id_amparo_legal = $registro->id_amparo_legal;
				$obj->codigo = $registro->codigo;
				$obj->descricao = $registro->descricao;
				$obj->amparo_legal = "";
				$amparo_legal = $registro->findParentRow("TbAmparoLegal");
				if ($amparo_legal) {
					$obj->amparo_legal = $amparo_legal->toString();
				}
				$obj->id_moeda = $valor->id_moeda;
				$obj->moeda = "";
				$moeda = $valor->findParentRow("TbMoeda");
				if ($moeda) {
					$obj->moeda = $moeda->simbolo;
				}
				$obj->valor = Escola_Util::number_format($valor->valor);
				$obj->tostring = $registro->toString();
				$items[] = $obj;
			}
			$this->view->items = $items;
		}
	}
}
