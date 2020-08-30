<?php
class GrupoController extends Escola_Controller_Logado {
    
	public function indexAction() {
		$tb = new TbGrupo();
		$page = $this->_getParam("page");
		$this->view->grupos = $tb->listar(array("pagina_atual" => $page));
		$button = Escola_Button::getInstance();
		$button->setTitulo("GRUPOS");
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
		$tb = new TbGrupo();
		if ($id) {
			$registro = $tb->fetchRow("id_grupo = " . $id);
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
		$this->view->grupo = $registro;
		$button = Escola_Button::getInstance();
		if ($this->view->grupo->getId()) {
			$button->setTitulo("Cadastro de Grupo - Alterar");
		} else {
			$button->setTitulo("Cadastro de Grupo - Inserir");
		}
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
	}
	
	public function excluirAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbGrupo();
			$rg = $tb->find($id);
			if (count($rg)) {
				$rg->current()->delete();
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
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
			$tb = new TbGrupo();
			$rg = $tb->find($id);
			if (count($rg)) {
				$this->view->grupo = $rg->current();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR GRUPO");
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
	
	public function permissoesAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbGrupo();
			$rg = $tb->find($id);
			if (count($rg)) {
				$this->view->grupo = $rg->current();
				$tb = new TbModulo();
				$sql = $tb->select();
				$sql->where(" not controller is null ");
				$sql->order("descricao");
				$this->view->modulos = $tb->fetchAll($sql);
				if ($this->_request->isPost()) {
					$post_acaos = $this->_request->getPost("acao");
					foreach ($this->view->modulos as $modulo) {
						$acaos = $modulo->findDependentRowSet("TbAcao");
						foreach ($acaos as $acao) {
							if ($post_acaos && in_array($acao->getId(), $post_acaos)) {
								$this->view->grupo->permitir($acao);
							} else {
								$this->view->grupo->negar($acao);
							}
						}
					}
					$this->view->actionMessages[] = "PERMISSÕES SALVAS COM SUCESSO!";
				}
				$colspan = 1;
				foreach ($this->view->modulos as $modulo) {
					$acaos = $modulo->findDependentRowSet("TbAcao");
					if (count($acaos) && (count($acaos) > $colspan)) {
						$colspan = count($acaos);
					}
				}
				$this->view->colspan = $colspan;
				$button = Escola_Button::getInstance();
				$button->setTitulo("PERMISSÕES POR GRUPO");
				$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
				$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
			} else {
				$this->_flashMessage("INFORMAÇÃO INVÁLIDA!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
	
	public function usuariosAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$tb = new TbGrupo();
			$rg = $tb->find($id);
			if (count($rg)) {
				$grupo = $rg->current();
				$tb = new TbUsuario();
				$this->view->grupo = $grupo;
                $this->view->usuarios = $grupo->getUsuariosPorPagina(array("pagina_atual" => $this->_getParam("page")));
                if (!count($this->view->usuarios)) {
                    $this->view->actionErrors[] = "Nenhum Usuário Vinculado ao Grupo!";
                }
				$button = Escola_Button::getInstance();
				$button->setTitulo("USUÁRIOS POR GRUPO");
				$button->addScript("Adicionar", "adicionar()", "icon-plus-sign");
				$button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
			} else {
				$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
				$this->_redirect($this->_request->getControllerName() . "/index");				
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
    
    public function usuarioexcluirAction() {
        $grupo = TbGrupo::pegaPorId($this->_request->getParam("id"));
        $usuario = TbUsuario::pegaPorId($this->_request->getParam("id_usuario"));
        if ($grupo && $usuario) {
            $this->view->grupo = $grupo;
            $this->view->usuario = $usuario;
            if ($usuario->pertence($grupo)) {
                $flag = $usuario->removeGrupo($grupo);
                if ($flag) {
                    $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                } else {
                    $this->_flashMessage("Falha ao Executar Operação, Tente Novamente mais tarde!");
                }
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Usuário não pertence ao grupo!");
            }
            $this->_redirect($this->_request->getControllerName() . "/usuarios/id/" . $grupo->getId());
        } else {
 			$this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            $this->_redirect($this->_request->getControllerName() . "/index");
       }
    }
    
    public function addusuarioAction() {
        $grupo = TbGrupo::pegaPorId($this->_request->getParam("id"));
        $usuario = TbUsuario::pegaPorId($this->_request->getParam("id_usuario"));
        if ($grupo && $usuario) {
            $usuario->addGrupo($grupo);
            if ($usuario->pertence($grupo)) {
                $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Tente Novamente mais tarde!");
            }
            $this->_redirect($this->_request->getControllerName() . "/usuarios/id/" . $grupo->getId());
        } else {
 			$this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            $this->_redirect($this->_request->getControllerName() . "/index");
       }
    }
}