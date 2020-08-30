<?php 
class PortaldocumentoController extends Escola_Controller_Logado {
	
	public function indexAction() {
		$page = $this->_getParam("page");
        $tb = new TbDocumentoTipoTarget();
        $dtt = $tb->getPorChave("W");
        if ($dtt) {
            $tb = new TbDocumento();
            $this->view->registros = $tb->listar_por_pagina(array("pagina_atual" => $page, "filtro_id_documento_tipo_target" => $dtt->getId()));
            $button = Escola_Button::getInstance();
            $button->setTitulo("PORTAL > DOCUMENTOS");
            $button->addFromArray(array("titulo" => "Adicionar",
                                        "controller" => $this->_request->getControllerName(),
                                        "action" => "editar",
                                        "img" => "icon-plus-sign",
                                        "params" => array("id" => 0)));
            $button->addFromArray(array("titulo" => "Voltar",
                                        "controller" => "intranet",
                                        "action" => "index",
                                        "img" => "icon-reply",
                                        "params" => array("id" => 0)));
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect("intranet/index");
        }
	}
	
	public function editarAction() {
        $tb = new TbDocumentoTipoTarget();
        $dtt = $tb->getPorChave("W"); //documentos do portal
        if ($dtt) {
            $tb = new TbDocumentoTipo();
            $dts = $tb->listar(array("filtro_id_documento_tipo_target" => $dtt->getId()));
            if ($dts && count($dts)) {
                $this->view->dts = $dts;
            } else {
                $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                $this->_redirect("portaldocumento/index");
            }
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect("portaldocumento/index");
        }
		$id = $this->_request->getParam("id");
		$tb = new TbDocumento();
		if ($id) {
			$registro = $tb->getPorId($id);
		} else {
			$registro = $tb->createRow();
		}
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
            $dados["arquivo"] = Escola_Util::getUploadedFile("arquivo");
            $dados["resumo"] = Escola_Util::maiuscula($dados["resumo"]);
			$registro->setFromArray($dados);
            if (!$registro->getId()) {
                $tb = new TbDocumentoModo();
                $dm = $tb->getPorChave("N");
                if ($dm) {
                    $registro->id_documento_modo = $dm->getId();
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
			$button->setTitulo("CADASTRO DE PORTAL > DOCUMENTO - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE PORTAL > DOCUMENTO - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbDocumento();
			$registro = $tb->getPorId($id);
			if ($registro) {
                $errors = $registro->getDeleteErrors();
                if (!$errors) {
                    $registro->delete();
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");					
                } else {
                    foreach ($errors as $erro) {
                        $this->_flashMessage($erro);
                    }
                }
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
		}
		$this->_redirect($this->_request->getControllerName() . "/index");
	}
	
	public function viewAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbDocumento();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
                $this->view->arquivo = $registro->pega_arquivo();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR PORTAL > DOCUMENTO");
                $button->addFromArray(array("titulo" => "Alterar",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "editar",
                                            "img" => "icon-cog",
                                            "params" => array("id" => $id)));
                $button->addFromArray(array("titulo" => "Excluir",
                                            "controller" => $this->_request->getControllerName(),
                                            "action" => "excluir",
                                            "img" => "icon-trash",
                                            "class" => "link_excluir",
                                            "params" => array("id" => $id)));
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