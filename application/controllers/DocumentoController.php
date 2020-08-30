<?php
class DocumentoController extends Escola_Controller_Logado
{

	public function init()
	{
		$ajaxContext = $this->_helper->getHelper("AjaxContext");
		$ajaxContext->addActionContext("listarvinculoporpagina", "json");
		$ajaxContext->addActionContext("listarporpagina", "json");
		$ajaxContext->addActionContext("info", "json");
		$ajaxContext->addActionContext("toform", "json");
		$ajaxContext->initContext();
	}

	public function toformAction()
	{
		$this->view->result = "";
		$dados = $this->_request->getPost();
		if (isset($dados["id_documento_tipo"]) && $dados["id_documento_tipo"]) {
			$tb = new TbDocumento();
			$id_documento = 0;
			if (isset($dados["id_documento"]) && $dados["id_documento"]) {
				$id_documento = $dados["id_documento"];
			}
			$doc = $tb->pegaPorId($id_documento);
			if (!$doc) {
				$doc = $tb->createRow($dados);
			}
			$funcionario = false;
			if (isset($dados["id_funcionario"]) && $dados["id_funcionario"]) {
				$funcionario = TbFuncionario::pegaPorId($dados["id_funcionario"]);
			}
			if ($doc) {
				$this->view->result = $doc->toForm($this->view, $funcionario);
			}
		}
	}

	public function infoAction()
	{
		$this->view->doc = false;
		$dados = $this->_request->getPost();
		if (isset($dados["id_documento"]) && $dados["id_documento"]) {
			$doc = TbDocumento::pegaPorId($dados["id_documento"]);
			if ($doc) {
				$this->view->doc = $doc->toString();
			}
		}
	}

	public function listarvinculoporpaginaAction()
	{
		$result = false;
		$dados = $this->getRequest()->getPost();
		if (isset($dados["filtro_id_documento"]) && $dados["filtro_id_documento"]) {
			$doc_principal = TbDocumento::pegaPorId($dados["filtro_id_documento"]);
			if ($doc_principal) {

				$tb = new TbDocumentoTipoTarget();
				$dtt = $tb->getPorChave("D");
				if ($dtt) {
					$dados["filtro_id_documento_tipo_target"] = $dtt->getId();
				}
				$tb = new TbDocumento();
				$dados["filtro_opcao"] = "V";
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
						$registro = TbDocumento::pegaPorId($registro["id_documento"]);
						if ($registro) {
							if (!$registro->vinculado() && !$doc_principal->possui($registro)) {
								$obj = new stdClass();
								$obj->id = $registro->getId();
								$obj->data_hora = Escola_Util::formatData($registro->data_criacao) . " " . $registro->hora_criacao;
								$obj->tipo = $registro->findParentRow("TbDocumentoTipo")->toString();
								$obj->numero = $registro->mostrarNumero();
								$obj->resumo = $registro->resumo;
								$items[] = $obj;
							}
						}
					}
					$this->view->items = $items;
				}
			}
		}
	}

	public function listarporpaginaAction()
	{
		$tb = new TbDocumento();
		$dados = $this->getRequest()->getPost();
		if (is_array($dados) && count($dados)) {
			foreach ($dados as $k => $v) {
				$dados[$k] = utf8_decode($v);
			}
		}

		$registros = $tb->listarporpagina($dados);

		$info = $registros->getPages();

		$this->view->items = false;
		$this->view->total_pagina = $info->pageCount;
		$this->view->pagina_atual = $info->current;
		$this->view->primeira = $info->first;
		$this->view->ultima = $info->last;

		if (!($registros && count($registros))) {
			return;
		}

		$items = array();
		foreach ($registros as $registro) {
			$registro = TbDocumento::pegaPorId($registro["id_documento"]);
			$obj = new stdClass();
			$obj->id = $registro->getId();
			$obj->data_hora = Escola_Util::formatData($registro->data_criacao) . " " . $registro->hora_criacao;
			$obj->tipo = $registro->findParentRow("TbDocumentoTipo")->toString();
			$obj->numero = $registro->mostrarNumero();
			$obj->resumo = $registro->resumo;
			$items[] = $obj;
		}
		
		$this->view->items = $items;
	}

	public function filtroAction()
	{
		$session = Escola_Session::getInstance();
		$filtros = array("filtro_opcao", "filtro_id_documento_tipo", "filtro_id_documento_modo", "filtro_numero", "filtro_ano", "filtro_setor", "filtro_funcionario", "filtro_interessado", "filtro_resumo", "filtro_id_documento_status");
		$session->atualizaFiltros($filtros);
		$this->_redirect("documento/index");
	}

	public function indexAction()
	{
		$tb = new TbDocumentoTipoTarget();
		$dtt = $tb->getPorChave("D");
		$tb = new TbDocumento();
		$page = $this->_getParam("page");
		$session = Escola_Session::getInstance();
		if (isset($session->id_doc_enc_multiplo)) {
			unset($session->id_doc_enc_multiplo);
		}
		$filtros = array("filtro_opcao", "filtro_id_documento_tipo", "filtro_id_documento_modo", "filtro_numero", "filtro_ano", "filtro_setor", "filtro_funcionario", "filtro_interessado", "filtro_resumo", "filtro_id_documento_status", "filtro_data_inicial", "filtro_data_final");
		$this->view->dados = $session->atualizaFiltros($filtros);
		$flag = false;
		foreach ($this->view->dados as $valor) {
			if ($valor) {
				$flag = true;
				break;
			}
		}
		if ($dtt) {
			$this->view->dados["filtro_id_documento_tipo_target"] = $dtt->getId();
		}
		if (!$flag) {
			$this->view->dados["filtro_opcao"] = "R";
		}
		$dados = $this->view->dados;
		$dados["pagina_atual"] = $page;
		$dados["qtd_por_pagina"] = 20;
		try {
			$this->view->registros = $tb->listarPorPagina($dados);
		} catch (Exception $ex) {
			$this->_flashMessage($ex->getMessage());
			$this->_redirect("intranet/index");
			die();
		}
		$tb = new TbFuncionario();
		$this->view->funcionario = $tb->pegaLogado();
		$button = Escola_Button::getInstance();
		$button->setTitulo("PROTOCOLO");
		$button->addFromArray(array(
			"titulo" => "Novo",
			"controller" => $this->_request->getControllerName(),
			"action" => "editar",
			"img" => "icon-plus-sign",
			"params" => array("id" => 0)
		));
		$button->addFromArray(array(
			"titulo" => "Imprimir",
			"controller" => $this->_request->getControllerName(),
			"action" => "relatorio",
			"img" => "icon-print",
			"params" => array("id" => 0)
		));
		$button->addFromArray(array(
			"titulo" => "Pesquisar",
			"onclick" => "pesquisar()",
			"img" => "icon-search",
			"params" => array("id" => 0)
		));
		if ($this->view->registros && count($this->view->registros)) {
			$button->addFromArray(array(
				"titulo" => "Encaminhar",
				"onclick" => "encaminhar()",
				"img" => "icon-share",
				"id" => "btn_encaminhar",
				"params" => array("id" => 0)
			));
		}
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
		$tb = new TbFuncionario();
		$this->view->funcionario = $tb->pegaLogado();
		$this->view->lotacao = false;
		if ($this->view->funcionario) {
			$this->view->lotacao = $this->view->funcionario->pegaLotacaoAtual();
		}
		$id = $this->_request->getParam("id");
		$tb = new TbDocumento();
		if ($id) {
			$registro = $tb->getPorId($id);
		} else {
			$registro = $tb->createRow();
		}
		if ($this->_request->isPost()) {
			$db = Zend_Registry::get("db");
			$db->beginTransaction();
			try {
				$arquivos = array();
				$files = Escola_Util::getUploadedFiles();
				if ($files && is_array($files) && count($files)) {
					/*
                            foreach ($files as $arquivo_idc => $arquivo) {
                                if (isset($arquivo["tmp_name"])) {
                                    Zend_Debug::dump(file_exists($arquivo["tmp_name"])); 
                                }
                            }
                            die();
                            */
					$tb = new TbArquivo();
					$contador = 0;
					foreach ($files as $arquivo_idc => $arquivo) {
						$contador++;
						$row = $tb->createRow();
						$dados = array("arquivo" => $arquivo, "legenda" => "Arquivo Importado na Criação!");
						$legenda = $this->_request->getPost("legenda_{$contador}");
						if ($legenda) {
							$dados["legenda"] = $legenda;
						}
						$row->setFromArray($dados);
						if (!$row->getErrors()) {
							$id = $row->save();
							if ($id) {
								$arquivos[] = $row;
							}
						}
					}
				}
				$dados = $this->_request->getPost();
				if (!$registro->getId()) {
					$tb = new TbDocumento();
					$registro = $tb->createRow($dados);
				}
				$registro->setFromArray($dados);
				$errors = $registro->getErrors();
				if ($errors) {
					$this->view->actionErrors = $errors;
				} else {
					$registro->save();
					if (isset($dados["ck_processo"]) && $dados["ck_processo"]) {
						$dados["numero"] = $dados["processo_numero"];
						$dados["ano"] = $dados["processo_ano"];
						$dados["id_funcionario"] = $this->view->funcionario->getId();
						$dados["id_setor"] = $this->view->lotacao->id_setor;
						$registro = $registro->tornar_processo($dados);
					}
					if ((isset($dados["tipo_destino"]) && $dados["tipo_destino"]) && (isset($dados["id_destino"]) && $dados["id_destino"])) {
						$tb = new TbMovimentacaoTipo();
						$mt = $tb->getPorChave("E");
						$flag = array(
							"id_movimentacao_tipo" => $mt->getId(),
							"id_funcionario" => $this->view->funcionario->getId(),
							"id_setor" => $this->view->lotacao->id_setor,
							"despacho" => "Primeiro Encaminhamento",
							"tipo_destino" => $dados["tipo_destino"],
							"id_destino" => $dados["id_destino"]
						);
						$flag = $registro->encaminhar($flag);
						if ($flag) {
							$this->_flashMessage("DOCUMENTO ENCAMINHADO COM SUCESSO!", "Messages");
						} else {
							$this->_flashMessage("FALHA AO ENCAMINHAR DOCUMENTO!");
						}
					}
					if (count($arquivos)) {
						foreach ($arquivos as $arquivo) {
							$registro->addArquivo($arquivo);
						}
					}
					$db->commit();
					$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
					$this->_redirect($this->_request->getControllerName() . "/index");
				}
			} catch (Exception $e) {
				$db->rollBack();
				$this->addErro("FALHA AO SALVAR DOCUMENTO!");
				$this->addErro($e->getMessage());
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		}
		$this->view->registro = $registro;
		$button = Escola_Button::getInstance();
		if ($this->view->registro->getId()) {
			$button->setTitulo("CADASTRO DE DOCUMENTO - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE DOCUMENTO - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}

	public function excluirAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbDocumento();
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
		$flag = $this->_request->getParam("flag");
		if ($id) {
			$registro = TbDocumento::pegaPorId($id);
			if ($registro) {
				$ds = $registro->findParentRow("TbDocumentoStatus");
				if (!$flag && $ds && $ds->processo()) {
					$registro = $registro->pegaProcesso();
				}
				$this->view->registro = $registro;
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR DOCUMENTO");
				$button->addFromArray(array(
					"titulo" => "Imprimir",
					"controller" => $this->_request->getControllerName(),
					"action" => "imprimir",
					"img" => "icon-print",
					"params" => array("id" => $id)
				));
				$button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
			} else {
				$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function receberAction()
	{
		$tb = new TbDocumento();
		$registro = $tb->getPorId($this->_request->getParam("id"));
		if ($registro->getId()) {
			$tb = new TbFuncionario();
			$funcionario = $tb->pegaLogado();
			if ($funcionario) {
				$flag = $registro->receber($funcionario);
				if ($flag) {
					$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
				} else {
					$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO!");
				}
			}
		}
		$this->_redirect($this->_request->getControllerName() . "/index");
	}

	public function encaminharAction()
	{
		$registro = TbDocumento::pegaPorId($this->_request->getParam("id"));
		if ($registro) {
			$tb = new TbFuncionario();
			$funcionario = $tb->pegaLogado();
			if ($funcionario) {
				if ($registro->habilitaEncaminhar($funcionario)) {
					$this->view->registro = $registro;
					$this->view->funcionario = $funcionario;
					$lotacao = $funcionario->pegaLotacaoAtual();
					if ($lotacao) {
						$this->view->setor = $lotacao->findParentRow("TbSetor");
					}
					if ($this->_request->isPost()) {
						$dados = $this->_request->getPost();
						$tb = new TbMovimentacaoTipo();
						$mt = $tb->getPorChave("E");
						if ($mt) {
							$dados["id_movimentacao_tipo"] = $mt->getId();
						}
						$dados["id_documento"] = $registro->getId();
						$dados["id_funcionario"] = $funcionario->getId();
						$dados["id_setor"] = $lotacao->id_setor;
						$tb = new TbMovimentacao();
						$mov = $tb->createRow();
						$mov->setFromArray($dados);
						$errors = $mov->getErrors();
						if ($errors) {
							$this->view->actionErrors = $errors;
						} else {
							$registro->encaminhar($dados);
							$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
							$this->_redirect("documento/index");
						}
					}
					$button = Escola_Button::getInstance();
					$button->setTitulo("ENCAMINHAR DOCUMENTO");
					$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
					$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
				} else {
					$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, PERMISSÃO NEGADA!");
					$this->_redirect("documento/index");
				}
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUM FUNCIONÁRIO LOGADO!");
				$this->_redirect("documento/index");
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect("documento/index");
		}
	}

	public function arquivarAction()
	{
		$registro = TbDocumento::pegaPorId($this->_request->getParam("id"));
		if ($registro) {
			$tb = new TbFuncionario();
			$funcionario = $tb->pegaLogado();
			if ($funcionario) {
				if ($registro->habilitaArquivar($funcionario)) {
					$this->view->registro = $registro;
					$this->view->funcionario = $funcionario;
					$lotacao = $funcionario->pegaLotacaoAtual();
					if ($lotacao) {
						$this->view->setor = $lotacao->findParentRow("TbSetor");
					}
					if ($this->_request->isPost()) {
						$dados = $this->_request->getPost();
						$tb = new TbMovimentacaoTipo();
						$mt = $tb->getPorChave("A");
						if ($mt) {
							$dados["id_movimentacao_tipo"] = $mt->getId();
						}
						$dados["id_documento"] = $registro->getId();
						$dados["id_funcionario"] = $funcionario->getId();
						$dados["id_setor"] = $lotacao->id_setor;
						$tb = new TbMovimentacao();
						$mov = $tb->createRow();
						$mov->setFromArray($dados);
						$errors = $mov->getErrors();
						if ($errors) {
							$this->view->actionErrors = $errors;
						} else {
							$registro->arquivar($dados);
							$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
							$this->_redirect("documento/index");
						}
					}
					$button = Escola_Button::getInstance();
					$button->setTitulo("ARQUIVAR DOCUMENTO");
					$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
					$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
				} else {
					$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, PERMISSÃO NEGADA!");
					$this->_redirect("documento/index");
				}
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUM FUNCIONÁRIO LOGADO!");
				$this->_redirect("documento/index");
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect("documento/index");
		}
	}

	public function cancelarAction()
	{
		$registro = TbDocumento::pegaPorId($this->_request->getParam("id"));
		if ($registro) {
			$tb = new TbFuncionario();
			$funcionario = $tb->pegaLogado();
			if ($funcionario) {
				if ($registro->habilitaCancelarArquivar($funcionario)) {
					$this->view->registro = $registro;
					$this->view->funcionario = $funcionario;
					$lotacao = $funcionario->pegaLotacaoAtual();
					if ($lotacao) {
						$this->view->setor = $lotacao->findParentRow("TbSetor");
					}
					if ($this->_request->isPost()) {
						$dados = $this->_request->getPost();
						$tb = new TbMovimentacaoTipo();
						$mt = $tb->getPorChave("C");
						if ($mt) {
							$dados["id_movimentacao_tipo"] = $mt->getId();
						}
						$dados["id_documento"] = $registro->getId();
						$dados["id_funcionario"] = $funcionario->getId();
						$dados["id_setor"] = $lotacao->id_setor;
						$tb = new TbMovimentacao();
						$mov = $tb->createRow();
						$mov->setFromArray($dados);
						$errors = $mov->getErrors();
						if ($errors) {
							$this->view->actionErrors = $errors;
						} else {
							$registro->cancelar_arquivar($dados);
							$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
							$this->_redirect("documento/index");
						}
					}
					$button = Escola_Button::getInstance();
					$button->setTitulo("CANCELAR ARQUIVAMENTO DE DOCUMENTO");
					$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
					$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
				} else {
					$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, PERMISSÃO NEGADA!");
					$this->_redirect("documento/index");
				}
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUM FUNCIONÁRIO LOGADO!");
				$this->_redirect("documento/index");
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect("documento/index");
		}
	}

	public function processoAction()
	{
		$registro = TbDocumento::pegaPorId($this->_request->getParam("id"));
		if ($registro) {
			$tb = new TbFuncionario();
			$funcionario = $tb->pegaLogado();
			if ($funcionario) {
				if ($registro->habilitaTornarProcesso($funcionario)) {
					$dados = $this->_request->getPost();
					$this->view->registro = $registro;
					$this->view->funcionario = $funcionario;
					$dados["id_funcionario"] = $funcionario->getId();
					$lotacao = $funcionario->pegaLotacaoAtual();
					$dados["id_setor"] = $lotacao->id_setor;
					$doc = $registro->tornar_processo($dados);
					if ($doc) {
						$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
					} else {
						$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
					}
				} else {
					$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, PERMISSÃO NEGADA!");
				}
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUM FUNCIONÁRIO LOGADO!");
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
		}
		$this->_redirect("documento/index");
	}

	public function vincularAction()
	{
		$flag = false;
		$dados = $this->_request->getParams();
		if (isset($dados["id_documento"]) && $dados["id_documento"]) {
			$documento = TbDocumento::pegaPorId($dados["id_documento"]);
			if ($documento) {
				if (isset($dados["id_documento_anexo"]) && $dados["id_documento_anexo"]) {
					$anexo = TbDocumento::pegaPorId($dados["id_documento_anexo"]);
					if ($anexo) {
						$flag = $documento->addDocumento($anexo);
					}
				}
			}
		}
		if ($flag) {
			$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO!");
		}
		$this->_redirect("documento/index");
	}

	public function addarquivoAction()
	{
		$id = $this->_request->getParam("id");
		$documento = TbDocumento::pegaPorId($id);
		$this->view->documento = $documento;
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$contador = 0;
			if (isset($dados["file_count"]) && $dados["file_count"]) {
				$files = Escola_Util::getUploadedFiles();
				for ($i = 1; $i <= $dados["file_count"]; $i++) {
					if (isset($files["arquivo" . $i])) {
						$arquivo = $files["arquivo" . $i];
						$tb = new TbArquivo();
						$row = $tb->createRow();
						$row->setFromArray(array("arquivo" => $arquivo, "legenda" => $dados["legenda" . $i]));
						$id = $row->save();
						if ($id) {
							$contador++;
							$documento->addArquivo($row);
						}
					}
				}
			}
			if ($contador) {
				$this->_flashMessage($contador . " Arquivos Importados.", "Messages");
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUM ARQUIVO IMPORTADO!");
			}
			$this->_redirect("documento/arquivo/id/" . $this->_getParam("id"));
		}
		$button = Escola_Button::getInstance();
		$button->setTitulo("ADICIONAR ANEXO");
		$button->addScript("Adicionar", "addFieldset()", "icon-plus-sign");
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addFromArray(array(
			"titulo" => "Cancelar",
			"controller" => $this->_request->getControllerName(),
			"action" => "arquivo",
			"img" => "icon-remove-circle",
			"params" => array("id" => $this->_getParam("id"))
		));
	}

	public function arquivoAction()
	{
		$id = $this->_request->getParam("id");
		if ($id) {
			$documento = TbDocumento::pegaPorId($id);
			if ($documento) {
				$this->view->documento = $documento;
				$this->view->registros = $documento->pegaAnexos();
				$button = Escola_Button::getInstance();
				$button->setTitulo("ARQUIVOS");
				$button->addFromArray(array(
					"titulo" => "Adicionar",
					"controller" => $this->_request->getControllerName(),
					"action" => "addarquivo",
					"img" => "icon-plus-sign",
					"params" => array("id_documento_ref" => 0, "id" => $documento->getId())
				));
				$button->addFromArray(array(
					"titulo" => "Voltar",
					"controller" => $this->_request->getControllerName(),
					"action" => "index",
					"img" => "icon-reply",
					"params" => array("id_documento_ref" => 0, "id" => $documento->getId())
				));
			} else {
				$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function editararquivoAction()
	{
		$id = $this->_request->getParam("id");
		$documento = TbDocumento::pegaPorId($id);
		$id = $this->_request->getParam("id_documento_ref");
		$registro = TbDocumentoRef::pegaPorId($id);
		if (!$registro) {
			$tb = new TbDocumentoRef();
			$registro = $tb->createRow();
		}
		$this->view->registro = $registro;
		$arquivo = $registro->pegaObjeto();
		$this->view->arquivo = $arquivo;
		$this->view->documento = $documento;
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$arq = Escola_Util::getUploadedFile("arquivo");
			if ($arq) {
				$dados["arquivo"] = $arq;
			}
			$arquivo->setFromArray($dados);
			$errors = $arquivo->getErrors();
			if ($errors) {
				$this->_flashMessage(implode("<br>", $errors));
			} else {
				$arquivo->save();
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
			}
			$this->_redirect("documento/arquivo/id/" . $documento->getId());
		}
		$button = Escola_Button::getInstance();
		$button->setTitulo("ALTERAR ANEXO DE DOCUMENTO");
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addFromArray(array(
			"titulo" => "Cancelar",
			"controller" => $this->_request->getControllerName(),
			"action" => "arquivo",
			"img" => "icon-remove-circle",
			"params" => array("id" => $this->_getParam("id"))
		));
	}

	public function viewarquivoAction()
	{
		$id = $this->_request->getParam("id");
		$this->view->documento = TbDocumento::pegaPorId($id);
		$id = $this->_request->getParam("id_documento_ref");
		if ($id) {
			$tb = new TbDocumentoRef();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$this->view->arquivo = $registro->pegaObjeto();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAÇÃO DE ARQUIVO");
				$button->addFromArray(array(
					"titulo" => "Voltar",
					"controller" => $this->_request->getControllerName(),
					"action" => "arquivo",
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

	public function excluirarquivoAction()
	{
		$id = $this->_request->getParam("id_documento_ref");
		if ($id) {
			$tb = new TbDocumentoRef();
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
		$this->_redirect($this->_request->getControllerName() . "/arquivo/id/" . $this->_getParam("id"));
	}

	public function imprimirAction()
	{
		$documento = TbDocumento::pegaPorId($this->_request->getParam("id"));
		if ($documento) {
			$rel = new Escola_Relatorio_Documento();
			$rel->set_dados(array("documento" => $documento));
			$rel->imprimir();
		} else {
			$this->_flashMessage("Falha ao Executar Operação, dados inválidos!!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function excluirmovimentacaoAction()
	{
		$mov = TbMovimentacao::pegaPorId($this->_request->getParam("id"));
		if ($mov) {
			$doc = $mov->findParentRow("TbDocumento");
			if ($doc) {
				$mov->delete();
				$tb = new TbDocumentoStatus();
				$ds = $tb->getPorChave("E");
				if ($ds) {
					$doc->id_documento_status = $ds->getId();
					$doc->save();
				}
				$this->_redirect($this->_request->getControllerName() . "/view/id/" . $doc->getId());
			} else {
				$this->_flashMessage("Falha ao Executar Operação, dados inválidos!!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("Falha ao Executar Operação, dados inválidos!!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function relatorioAction()
	{
		$session = Escola_Session::getInstance();
		$filtros = array("filtro_opcao", "filtro_id_documento_tipo", "filtro_id_documento_modo", "filtro_numero", "filtro_ano", "filtro_setor", "filtro_funcionario", "filtro_interessado", "filtro_resumo", "filtro_id_documento_status", "filtro_data_inicial", "filtro_data_final");
		$dados = $session->atualizaFiltros($filtros, "index");
		$tb = new TbDocumentoTipoTarget();
		$dtt = $tb->getPorChave("D");
		if ($dtt) {
			$dados["filtro_id_documento_tipo_target"] = $dtt->getId();
		}
		$array = array();
		if ($dados["filtro_opcao"]) {
			if ($dados["filtro_opcao"] == "R") {
				$array["Opção de Filtro"] = "A RECEBER";
			}
			if ($dados["filtro_opcao"] == "S") {
				$array["Opção de Filtro"] = "DOCUMENTOS QUE ESTÃO NO SETOR";
			}
			if ($dados["filtro_opcao"] == "P") {
				$array["Opção de Filtro"] = "DOCUMENTOS QUE PERTENCEM AO SETOR";
			}
		}
		if ($dados["filtro_id_documento_tipo"]) {
			$obj = TbDocumentoTipo::pegaPorId($dados["filtro_id_documento_tipo"]);
			if ($obj) {
				$array["Tipo de Documento"] = $obj->toString();
			}
		}
		if ($dados["filtro_id_documento_modo"]) {
			$obj = TbDocumentoModo::pegaPorId($dados["filtro_id_documento_modo"]);
			if ($obj) {
				$array["Modo"] = $obj->toString();
			}
		}
		if ($dados["filtro_numero"]) {
			$array["Número"] = $dados["filtro_numero"];
		}
		if ($dados["filtro_ano"]) {
			$array["Ano"] = $dados["filtro_ano"];
		}
		if ($dados["filtro_setor"]) {
			$array["Setor"] = $dados["filtro_setor"];
		}
		if ($dados["filtro_funcionario"]) {
			$array["Funcionário"] = $dados["filtro_funcionario"];
		}
		if ($dados["filtro_interessado"]) {
			$array["Interessado"] = $dados["filtro_interessado"];
		}
		if ($dados["filtro_data_inicial"]) {
			$array["Data Inicial"] = $dados["filtro_data_inicial"];
		}
		if ($dados["filtro_data_final"]) {
			$array["Data Final"] = $dados["filtro_data_final"];
		}
		if ($dados["filtro_resumo"]) {
			$array["Resumo"] = $dados["filtro_resumo"];
		}
		if ($dados["filtro_id_documento_status"]) {
			$obj = TbDocumentoStatus::pegaPorId($dados["filtro_id_documento_status"]);
			if ($obj) {
				$array["Status"] = $obj->toString();
			}
		}
		$tb = new TbFuncionario();
		$funcionario = $tb->pegaLogado();
		$relatorio = new Escola_Relatorio_Protocolo();
		$relatorio->set_dados($array);
		$relatorio->set_funcionario($funcionario);
		$relatorio->set_filtro($dados);
		$relatorio->imprimir();
	}

	public function encaminhamultiplaAction()
	{
		try {
			$session = Escola_Session::getInstance();

			if (isset($session->id_doc_enc_multiplo)) {
				$ids = $session->id_doc_enc_multiplo;
			} else {
				if (!$this->_request->isPost()) {
					throw new Exception("Falha ao Executar Operação, Dados Inválidos!");
				}
				$ids = $this->_request->getPost("lista_id");
				$session->id_doc_enc_multiplo = $ids;
			}

			if (!$ids) {
				throw new Exception("Falha ao Executar Operação, Nenhum Documento Informado para Encaminhamento!");
			}

			if (!(is_array($ids) && count($ids))) {
				throw new Exception("Falha ao Executar Operação, Nenhum Documento Informado para Encaminhamento!!");
			}

			$tb = new TbFuncionario();
			$func = $tb->pegaLogado();
			if (!$func) {
				throw new Exception("Falha ao Executar Operação, Usuario Nao e Funcionario!!");
			}

			$encaminhar = $erros = array();

			$tb = new TbDocumento();
			foreach ($ids as $id) {
				$doc = $tb->pegaPorId($id);
				if ($doc && $doc->getId()) {
					if ($doc->habilitaEncaminhar($func)) {
						$encaminhar[] = $doc;
					} else {
						$erros[] = $doc->toString();
					}
				}
			}

			if (!count($encaminhar)) {
				throw new Exception("Falha ao Executar Operação, Nenhum Documento Habilitado para Encaminhamento!!");
			}

			if (count($erros)) {
				$html = "Os Seguintes Documentos não podem ser Encaminhados: ";
				$html .= "<ul><li>" . implode("</li><li>", $erros) . "</li></ul>";
				$this->view->actionErrors[] = $html;
			}

			$this->view->funcionario = $func;
			$lotacao = $func->pegaLotacaoAtual();
			if (!$lotacao) {
				throw new Exception("Falha ao Executar Operação, Nenhuma Lotação Encontrada!");
			}

			$setor = $lotacao->findParentRow("TbSetor");
			if (!$setor) {
				throw new Exception("Falha ao Executar Operação, Nenhuma Lotação Encontrada!!");
			}

			$this->view->setor = $setor;
			$this->view->docs = $encaminhar;

			$button = Escola_Button::getInstance();
			$button->setTitulo("DOCUMENTO - ENCAMINHAR");

			$button->addScript("Encaminhar", "salvarFormulario('formulario')", "icon-save");
			$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
		} catch (Exception $ex) {
			$this->_flashMessage($ex->getMessage());
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}

	public function salvaencaminhamultiplaAction()
	{

		$db = Zend_Registry::get("db");
		$db->beginTransaction();
		try {

			if (!$this->_request->isPost()) {
				throw new Exception("Falha ao Executar Operação, Nenhuma Informação Recebida!");
			}

			$tb = new TbFuncionario();
			$func = $tb->pegaLogado();
			if (!$func) {
				throw new Exception("Falha ao Executar Operação, Nenhum Usuário Logado!");
			}

			$lotacao = $func->pegaLotacaoAtual();
			if (!$lotacao) {
				throw new Exception("Falha ao Executar Operação, Nenhuma Lotação Disponível para o Usuário!");
			}

			$ids = $this->_request->getPost("id_documento");

			if (!(is_array($ids) && count($ids))) {
				throw new Exception("Falha ao Executar Operação, Nenhum Documento para Encaminhar!");
			}

			$dados = array();
			$dados["tipo_destino"] = $this->_request->getPost("tipo_destino");
			if (!$dados["tipo_destino"]) {
				throw new UnexpectedValueException("Falha ao Executar Operação, Destino Inválido!");
			}
			$dados["id_destino"] = $this->_request->getPost("id_destino");
			if (!$dados["id_destino"]) {
				throw new UnexpectedValueException("Falha ao Executar Operação, Destino Inválido!");
			}

			$tb = new TbMovimentacaoTipo();
			$mt = $tb->getPorChave("E");
			if (!$mt) {
				throw new UnexpectedValueException("Falha ao Executar Operação, Status de Encaminhamento Não Disponível!");
			}

			$dados["despacho"] = $this->_request->getPost("despacho");

			if (!$dados["despacho"]) {
				throw new UnexpectedValueException("Falha ao Executar Operação, Nenhum Resumo Informado!");
			}

			$dados["id_movimentacao_tipo"] = $mt->getId();
			$dados["id_funcionario"] = $func->getId();
			$dados["id_setor"] = $lotacao->id_setor;

			$tb_doc = new TbDocumento();

			foreach ($ids as $id) {
				$doc = $tb_doc->pegaPorId($id);
				try {
					if ($doc && $doc->habilitaEncaminhar($func)) {
						$dados["id_documento"] = $id;
						$tb = new TbMovimentacao();
						$mov = $tb->createRow();
						$mov->setFromArray($dados);
						$errors = $mov->getErrors();
						if ($errors) {
							$this->_flashMessage("Falha ao Encaminhar Documento: " . $doc->toString());
							$this->_flashMessage("<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
						} else {
							$doc->encaminhar($dados);
							$this->_flashMessage("Documento {$doc->toString()} Encaminhado com Sucesso!", "Messages");
						}
					}
				} catch (Exception $ex) {
					var_dump($doc);
					var_dump($ex->getMessage());
					die();
				}
			}

			$db->commit();
			$this->_redirect("documento/index");
		} catch (UnexpectedValueException $ex) {
			$db->rollBack();

			$this->_flashMessage($ex->getMessage());
			$this->_redirect($this->_request->getControllerName() . "/encaminhamultipla");
		} catch (Exception $ex) {
			$db->rollBack();

			$this->_flashMessage($ex->getMessage());
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
}
