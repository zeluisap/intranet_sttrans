<?php 
class BancoconvenioController extends Escola_Controller_Logado {

	public function indexAction() {
		$tb = new TbBancoConvenio();
		$page = $this->_getParam("page");
		$this->view->registros = $tb->listar_por_pagina(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("CONVÊNIOS BANCÁRIOS");
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
	}
	
	public function editarAction() {
		$id = $this->_request->getParam("id");
		$tb = new TbBancoConvenio();
		if ($id) {
			$registro = $tb->getPorId($id);
		} else {
			$registro = $tb->createRow();
		}
        $ib = $registro->pega_info_bancaria();
        if (!$ib) {
            $tb = new TbInfoBancaria();
            $ib = $tb->createRow();
        }
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$registro->setFromArray($dados);
            $ib->setFromArray($dados);
			$errors = $registro->getErrors();
            $ib = $registro->pega_info_bancaria();
            if (!$ib) {
                $tb = new TbInfoBancaria();
                $ib = $tb->createRow();
            }
            $ib->setFromArray($dados);
            $ib_errors = $ib->getErrors();
			if ($errors || $errors) {
                $err = array();
                if ($errors) {
                    $err = array_merge($err, $errors);
                }
                if ($ib_errors) {
                    $err = array_merge($err, $ib_errors);
                }
                if (count($err)) {
                    $this->view->actionErrors = $err;
                }
			} else {
                $db = Zend_Registry::get("db");
                $db->beginTransaction();
                try {
                    $registro->save();
                    $ib->save();
                    $tb = new TbInfoBancariaRef();
                    $ibr = $tb->createRow();
                    $ibr->setFromArray(array("id_info_bancaria" => $ib->getId(), "tipo" => "C", "chave" => $registro->getId()));
                    $errors = $ibr->getErrors();
                    if (!$errors) {
                        $ibr->save();
                    }
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $db->commit();
                } catch (Exception $e) {
                    $this->_flashMessage($e->getMessage());
                    $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                    $db->rollBack();
                }
				$this->_redirect($this->_request->getControllerName() . "/index");
			}  
		}
		$this->view->registro = $registro;
        $this->view->ib = $ib;
		$button = Escola_Button::getInstance();
		if ($this->view->registro->getId()) {
			$button->setTitulo("CADASTRO DE CONVÊNIO BANCÁRIO - ALTERAR");
		} else {
			$button->setTitulo("CADASTRO DE CONVÊNIO BANCÁRIO - INSERIR");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbBancoConvenio();
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
			$tb = new TbBancoConvenio();
			$registro = $tb->getPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
                $this->view->ib = $registro->pega_info_bancaria();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR CONVÊNIO BANCÁRIO");
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