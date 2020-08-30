<?php
class InfoController extends Escola_Controller_Logado
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("listar", "json");
		$ajaxContext->initContext();
	}

	public function listarAction()
	{
		$result = false;
		$tb = new TbInfo();
		$dados = $this->getRequest()->getPost();
		$dados = array_map("utf8_decode", $dados);
		$registros = $tb->listarPorPagina($dados);
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
				$obj->id = $registro->id_info;
				$obj->data = Escola_Util::formatData($registro->data);
				$obj->tipo = $registro->findParentRow("TbInfoTipo")->toString();
				$obj->titulo = $registro->titulo;
				$items[] = $obj;
			}
			$this->view->items = $items;
		}
	}

	public function indexAction()
	{
		$session = Escola_Session::getInstance();
		$filtros = array("filtro_id_info_tipo", "filtro_id_info_status", "filtro_titulo", "page");
		$tb = new TbInfo();
		$this->view->dados = $session->atualizaFiltros($filtros);
		$this->view->dados["pagina_atual"] = $this->view->dados["page"];
		$this->view->registros = $tb->listarPorPagina($this->view->dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("INFORMAÇÕES DO SITE");
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
		$tb = new TbInfo();
		if ($id) {
			$registro = $tb->getPorId($id);
		} else {
			$registro = $tb->createRow();
		}
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$arquivo = Escola_Util::getUploadedFile("arquivo_destaque");
			if ($arquivo && $arquivo["size"]) {
				$dados["arquivo_destaque"] = $arquivo;
			}
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
			$button->setTitulo("CADASTRO DE INFORMAÇÕES DO SITE - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE INFORMAÇÕES DO SITE - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}

	public function excluirAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbInfo();
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
			$tb = new TbInfo();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR INFORMAÇÃO DO SITE");
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

	public function anexoAction()
	{
		$id = $this->_getParam("id");
		$info = TbInfo::pegaPorId($id);
		if ($info) {
			$this->view->info = $info;
			$this->view->registros = $info->pegaAnexos();
			$button = Escola_Button::getInstance();
			$button->setTitulo("INFORMAÇÕES - ANEXOS");
			$button->addFromArray(array(
				"titulo" => "Adicionar",
				"controller" => $this->_request->getControllerName(),
				"action" => "addanexo",
				"img" => "icon-plus-sign",
				"params" => array("id" => $info->getId(), "id_anexo" => 0)
			));
			$button->addFromArray(array(
				"titulo" => "Voltar",
				"controller" => $this->_request->getControllerName(),
				"action" => "index",
				"img" => "icon-reply"
			));
		}
	}

	public function addanexoAction()
	{
		$id = $this->_getParam("id");
		$info = TbInfo::pegaPorId($id);
		if ($info) {
			$this->view->info = $info;
			if ($this->_request->isPost()) {
				$dados = $this->_request->getPost();
				$contador = 0;
				if (isset($dados["file_count"]) && $dados["file_count"]) {
					$arquivos = Escola_Util::getUploadedFiles();
					$tb_arquivo = new TbArquivo();
					$tb_info_ref = new TbInfoRef();
					for ($i = 1; $i <= $dados["file_count"]; $i++) {
						$filename = "arquivo" . $i;
						if (isset($arquivos[$filename]["size"]) && $arquivos[$filename]["size"]) {
							$arquivo = $arquivos[$filename];
							$row = $tb_arquivo->createRow();
							$row->setFromArray(array("legenda" => $dados["legenda" . $i], "arquivo" => $arquivo));
							if (!$info->galeria() || ($info->galeria() && $row->eImagem())) {
								$row->save();
								$ir = $tb_info_ref->createRow();
								$ir->setFromArray(array(
									"tipo" => "A",
									"id_info" => $info->getId(),
									"chave" => $row->getId()
								));
								$ir->save();
								$contador++;
							}
						}
					}
				}
				if ($contador) {
					$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO, {$contador} ARQUIVO(S) ADICIONADO(S)!", "Messages");
					$this->_redirect("info/anexo/id/" . $info->getId());
				} else {
					$this->view->actionErrors[] = "FALHA AO EXECUTAR OPERAÇÃO, NENHUM ARQUIVO RECEBIDO!";
				}
			}
			if ($this->view->info->galeria()) {
				$this->view->actionErrors[] = "SOMENTE ARQUIVOS DE IMAGEM PODEM SER ADICIONADOS A UMA GALERIA, OUTROS ARQUIVOS SERÃO DESCARTADOS.";
			}
			$button = Escola_Button::getInstance();
			$button->setTitulo("INFORMAÇÕES - ADICIONANDO ANEXOS");
			$button->addScript("Adicionar", "addFieldset()", "icon-plus-sign");
			$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
			$button->addAction("Cancelar", $this->_request->getControllerName(), "anexo", "icon-remove-circle", array("id" => $info->getId()));
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect("info/index");
		}
	}

	public function excluiranexoAction()
	{
		$id = $this->_request->getParam("id_info_ref");
		if ($id) {
			$tb = new TbInfoRef();
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
		$this->_redirect($this->_request->getControllerName() . "/anexo/id/" . $this->_getParam("id"));
	}

	public function viewanexoAction()
	{
		$id = $this->_request->getParam("id_info_ref");
		if ($id) {
			$tb = new TbInfoRef();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$this->view->arquivo = $registro->pegaObjeto();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAÇÃO DE ANEXO");
				$button->addFromArray(array(
					"titulo" => "Voltar",
					"controller" => $this->_request->getControllerName(),
					"action" => "anexo",
					"img" => "icon-reply",
					"params" => array("id" => $this->_getParam("id"))
				));
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function editaranexoAction()
	{
		$id = $this->_request->getParam("id_info_ref");
		$registro = TbInfoRef::pegaPorId($id);
		if ($registro) {
			$this->view->registro = $registro;
			$this->view->arquivo = $registro->pegaObjeto();
			$this->view->info = $registro->findParentRow("TbInfo");
			if ($this->_request->isPost()) {
				$dados = $this->_request->getPost();
				$arquivo = Escola_Util::getUploadedFile("arquivo");
				if ($arquivo && $arquivo["size"]) {
					$dados["arquivo"] = $arquivo;
				}
				$this->view->arquivo->setFromArray($dados);
				$errors = $this->view->arquivo->getErrors();
				if ($errors) {
					$this->view->actionErrors = $errors;
				} else {
					if (!$this->view->info->galeria() || ($this->view->info->galeria() && $this->view->arquivo->eImagem())) {
						$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
						$this->view->arquivo->save();
						$this->_redirect($this->_request->getControllerName() . "/anexo/id/" . $this->view->info->getId());
					} else if ($this->view->info->galeria() && !$this->view->arquivo->eImagem()) {
						$this->view->actionErrors[] = "FORMATO DE ARQUIVO INVÁLIDO, SOMENTE IMAGEM PERMITIDO!";
					}
				}
			}
			if ($this->view->info->galeria()) {
				$this->view->actionErrors[] = "SOMENTE ARQUIVOS DE IMAGEM PODEM SER ADICIONADOS A UMA GALERIA, OUTROS ARQUIVOS SERÃO DESCARTADOS.";
			}
			$this->view->registro = $registro;
			$button = Escola_Button::getInstance();
			$button->setTitulo("CADASTRO DE ANEXO - ALTERAR");
			$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
			$button->addFromArray(array(
				"titulo" => "Cancelar",
				"controller" => $this->_request->getControllerName(),
				"action" => "anexo",
				"img" => "icon-remove-circle",
				"params" => array("id" => $this->_getParam("id"))
			));
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function referenciaAction()
	{
		$id = $this->_getParam("id");
		$info = TbInfo::pegaPorId($id);
		if ($info) {
			$this->view->info = $info;
			$this->view->registros = $info->pegaReferencia();
			$button = Escola_Button::getInstance();
			$button->setTitulo("INFORMAÇÕES - REFERÊNCIAS");
			$button->addFromArray(array(
				"titulo" => "Adicionar",
				"onclick" => "adicionar()",
				"img" => "icon-plus-sign"
			));
			$button->addFromArray(array(
				"titulo" => "Voltar",
				"controller" => $this->_request->getControllerName(),
				"action" => "index",
				"img" => "icon-reply"
			));
		}
	}

	public function salvareferenciaAction()
	{
		if ($this->_request->isPost()) {
			$id = $this->_getParam("id");
			$info = TbInfo::pegaPorId($id);
			$dados = $this->_request->getPost();
			if ($info) {
				if ($info->getId() != $dados["jan_id_info"]) {
					$tb = new TbInfoRef();
					$row = $tb->createRow();
					$row->setFromArray(array(
						"id_info" => $info->getId(),
						"tipo" => "I",
						"chave" => $dados["jan_id_info"]
					));
					if (!$row->getErrors()) {
						$row->save();
					}
				} else {
					$this->_flashMessage("REFERÊNCIA NÃO PODE APONTAR PARA SI MESMA!");
				}
			}
		}
		$this->_redirect("info/referencia/id/" . $this->_getParam("id"));
	}

	public function excluirreferenciaAction()
	{
		$id = $this->_request->getParam("id_info_ref");
		if ($id) {
			$tb = new TbInfoRef();
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
		$this->_redirect($this->_request->getControllerName() . "/referencia/id/" . $this->_getParam("id"));
	}
}
