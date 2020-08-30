<?php
class LoteController extends Escola_Controller_Logado {

    public function filtroAction() {
        $session = Escola_Session::getInstance();
		$filtros = array("page", "id_vinculo", "id_vinculo_lote_status", "mes", "ano");
        $session->atualizaFiltros($filtros);
		$this->_redirect("lote/index");
    }

	public function indexAction() {
        $usuario = TbUsuario::pegaLogado();
        if ($usuario) {
            $tb = new TbVinculoLote();
            $session = Escola_Session::getInstance();
            $filtros = array("page", "id_vinculo", "id_vinculo_lote_status", "mes", "ano");
            $this->view->dados = $session->atualizaFiltros($filtros);
            $this->view->dados["pagina_atual"] = $this->view->dados["page"];
            $this->view->dados["id_pessoa_fisica_coordenador"] = $usuario->id_pessoa_fisica;
            $this->view->registros = $tb->listar_por_pagina($this->view->dados);
            $this->view->meses = Escola_Util::pegaMeses();
            $tb = new TbVinculo();
            $this->view->vinculos = $tb->listar(array("id_pessoa_fisica" => $usuario->id_pessoa_fisica));
            $button = Escola_Button::getInstance();
            $button->setTitulo("PROJETOS > LOTES DISPONÍVEIS");
            $button->addFromArray(array("titulo" => "Pesquisar",
                                        "onclick" => "pesquisar()",
                                        "img" => "icon-search",
                                        "params" => array("id" => 0)));
            $button->addFromArray(array("titulo" => "Voltar",
                                    "controller" => "intranet",
                                    "action" => "index",
                                    "img" => "icon-reply",
                                    "params" => array("id" => 0)));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect("intranet/index");
        }
    }
    
    public function gerenciarAction() {
        $session = Escola_Session::getInstance();
        if (isset($session->lote_item_dados)) {
            unset($session->lote_item_dados);
        }
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $this->view->lote = $registro;
            $this->view->tipos = $registro->listar_tipo();
            $button = Escola_Button::getInstance();
            $button->setTitulo("PROJETOS > LOTES > GERENCIAR");
            $usuario = TbUsuario::pegaLogado();
            if ($registro->habilita_aprovar($usuario)) {                
                $button->addFromArray(array("titulo" => "Aprovar",
                                        "controller" => $this->_request->getControllerName(),
                                        "action" => "aprovar",
                                        "img" => "icon-thumbs-up",
                                        "params" => array("id" => $registro->getId()),
                                        "class" => "link_confirma"));
            }
            if ($registro->findParentRow("TbVinculoLoteStatus")->aguardando_aprovacao()) {
                $button->addFromArray(array("titulo" => "Adicionar",
                                        "controller" => $this->_request->getControllerName(),
                                        "action" => "addloteitem",
                                        "img" => "icon-plus-sign",
                                        "params" => array("id" => $registro->getId())));
            }
            $button->addFromArray(array("titulo" => "Resumo",
                                        "onclick" => "resumo_{$registro->getId()}()",
                                        "img" => "icon-list-alt",
                                        "params" => array("id" => 0)));
            $button->addFromArray(array("titulo" => "Voltar",
                                    "controller" => $this->_request->getControllerName(),
                                    "action" => "index",
                                    "img" => "icon-reply",
                                    "params" => array("id" => 0)));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function loteitemAction() {
        $session = Escola_Session::getInstance();
        if (isset($session->lote_item_dados)) {
            $dados = $session->lote_item_dados;
        } else {
            $dados = array("id_vinculo_lote" => $this->_request->getParam("id"),
                           "tipo" => $this->_request->getParam("tipo"),
                           "id_bolsa_tipo" => $this->_request->getParam("id_bolsa_tipo"));
        }
        $registro = TbVinculoLote::pegaPorId($dados["id_vinculo_lote"]);
        if ($registro) {
            $this->view->lote = $registro;
            if ($dados["tipo"]) {
                $session->lote_item_dados = $dados;
                $bt = false;
                $tb = new TbVinculoLoteItem();
                $page = $this->_getParam("page");
                $dados["pagina_atual"] = $page;
                $this->view->registros = $tb->listar_por_pagina($dados);
                $this->view->tipo = "";
                $this->view->bolsa_tipo = "";
                $this->view->pt = false;
                if (isset($dados["tipo"]) && $dados["tipo"]) {
                    $tb = new TbPrevisaoTipo();
                    $obj = $tb->getPorChave($dados["tipo"]);
                    if ($obj) {
                        $this->view->tipo = $obj->toString();
                    }
                    if (isset($dados["id_bolsa_tipo"]) && $dados["id_bolsa_tipo"]) {
                        $obj = TbBolsaTipo::pegaPorId($dados["id_bolsa_tipo"]);
                        if ($obj) {
                            $bt = $obj;
                            $this->view->bolsa_tipo = $obj->toString();
                        }
                    }
                    $tb = new TbPrevisaoTipo();
                    $pt = $tb->getPorChave($dados["tipo"]);
                    if ($pt) {
                        $this->view->pt = $pt;
                    }
                }
                $this->view->mensagem = $registro->pega_mensagem($dados);
                $this->view->total = $registro->pega_valor_total($dados);
                $this->view->previsao = $registro->pega_valor_previsao($dados);
                $button = Escola_Button::getInstance();
                $button->setTitulo("PROJETOS > LOTES > GERENCIAR > ÍTENS");
                $usuario = TbUsuario::pegaLogado();
                if ($registro->findParentRow("TbVinculoLoteStatus")->aguardando_aprovacao()) {
                    if ($pt->bolsista()) {
                        $button->addFromArray(array("titulo" => "Adicionar",
                                                    "onclick" => "adicionar_lote_item()",
                                                    "img" => "icon-plus-sign",
                                                    "params" => array("id" => 0)));
                    } else {
                        $params = array("tipo" => $pt->chave , "id" => $registro->getId());
                        if ($bt) {
                            $params["id_bolsa_tipo"] = $bt->getId();
                        }
                        $button->addFromArray(array("titulo" => "Adicionar",
                                                    "controller" => $this->_request->getControllerName(),
                                                    "action" => "loteitemeditar",
                                                    "img" => "icon-plus-sign",
                                                    "params" => $params));
                    }
                }
                $button->addFromArray(array("titulo" => "Voltar",
                                        "controller" => $this->_request->getControllerName(),
                                        "action" => "gerenciar",
                                        "img" => "icon-reply",
                                        "params" => array("id" => $registro->getId())));
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function loteitemexcluirAction() {
        $dados = $this->_request->getParams();
        $registro = TbVinculoLoteItem::pegaPorId($this->_request->getParam("id_vinculo_lote_item"));
        if ($registro) {
            $this->_flashMessage("Operação Efetuada com Scuesso", "Messages");
            $registro->delete();
            $this->_redirect("lote/loteitem");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }       
    }
    
    public function viewloteitemAction() {
        $registro = TbVinculoLoteItem::pegaPorId($this->_request->getParam("id_vinculo_lote_item"));
        if ($registro) {
            $registro_ref = $registro->getReferencia();
            $this->view->registro = $registro;
            if ($registro_ref) {
                $this->view->registro = $registro_ref;
            }
            $lote = $registro->findParentRow("TbVinculoLote");
            $vinculo = false;
            if ($lote) {
                $this->view->vinculo = $lote->findParentRow("TbVinculo");
            }
            $this->view->lote = $lote;
            $this->view->vinculo = $vinculo;
            $this->view->ocorrencias = $registro->pega_ocorrencia();
            $button = Escola_Button::getInstance();
            $button->setTitulo("PROJETOS > LOTES > GERENCIAR > VISUALIZAR ÍTENS");
            $button->addFromArray(array("titulo" => "Voltar",
                                    "controller" => $this->_request->getControllerName(),
                                    "action" => "loteitem",
                                    "img" => "icon-reply"));
      } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");            
        }
    }
    
    public function addloteitembolsistaAction() {
        $session = Escola_Session::getInstance();
        $registro = TbBolsista::pegaPorId($this->_request->getParam("id_bolsista"));
        $lote = TbVinculoLote::pegaPorId($session->lote_item_dados["id"]);
        if ($registro && $lote) {
            $lote->add_bolsista($registro);
            if ($lote->possui_bolsista($registro)) {
                $this->_flashMessage("Operação Efetuada com Scuesso", "Messages");
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            }
            $this->_redirect("lote/loteitem");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }       
    }    
    
    public function aprovarAction() {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $usuario = TbUsuario::pegaLogado();
            if ($registro->habilita_aprovar($usuario)) {
                $flag = $registro->aprovar($usuario);
                if ($flag) {
                    $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                } else {
                    $this->_flashMessage("Falha ao Executar Operação, Lote não disponível para Aprovação!");
                }
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Lote não disponível para Aprovação!");
            }
            $this->_redirect($this->_request->getControllerName() . "/gerenciar/id/{$registro->getId()}");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
	public function addloteitemAction() {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
            $errors = array();
            $pt = TbPrevisaoTipo::pegaPorId($this->_request->getParam("id_previsao_tipo"));
            if (!$pt) {
                $this->view->actionErrors[] = "Campo Tipo Obrigatório!";
            } else {
                $dados["id_vinculo_lote"] = $registro->getId();
                $dados["tipo"] = $pt->chave;
                $tb = new TbVinculoLoteItem();
                $vli = $tb->createRow();
                $vli->tipo = $dados["tipo"];
                $vli = $vli->getReferencia();
                if ($vli) {
                    $vli->setFromArray($dados);
                    $errors = $vli->getErrors();
                    if ($errors) {
                        $this->view->actionErrors = $errors;
                    } else {
                        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                        $vli->save();
                        $this->_redirect($this->_request->getControllerName() . "/gerenciar/id/{$registro->getId()}");
                    }  
                } else {
                    $this->view->actionErrors[] = "Falha ao Executar Operação, Dados Inválidos!";
                }
            }
		}
        $tb = new TbPrevisaoTipo();
        $this->view->pts = $tb->listar();
		$this->view->registro = $registro;
                $this->view->vinculo = $registro->findParentRow("TbVinculo");
		$button = Escola_Button::getInstance();
        $button->setTitulo("VÍNCULO LOTE > ADICIONAR PAGAMENTO");
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addFromArray(array("titulo" => "Voltar",
                                        "controller" => $this->_request->getControllerName(),
                                        "action" => "gerenciar",
                                        "img" => "icon-reply",
                                        "params" => array("id" => $registro->getId())));
	}
    
	public function loteitemeditarAction() {
            $session = Escola_Session::getInstance();
        $registro = TbVinculoLoteItem::pegaPorId($this->_request->getParam("id_vinculo_lote_item"));
        if (!$registro) {
            $tb = new TbVinculoLoteItem();
            $registro = $tb->createRow();
            $registro->id_vinculo_lote = $session->lote_item_dados["id_vinculo_lote"];
            $registro->tipo = $session->lote_item_dados["tipo"];
            $tb = new TbPrevisaoTipo();
            $pts = $tb->listar();
            if (!$pts) {
                $this->_flashMessage("Falha ao Executar Operação, Nenhum Tipo de Previsão Cadastrado!");
                $this->_redirect($this->_request->getControllerName() . "/loteitem");
            }
            $this->view->pts = $pts;
        }
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
                        $dados["id_bolsa_tipo"] = $session->lote_item_dados["id_bolsa_tipo"];
            $registro = $registro->getReferencia();
            if ($registro) {
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors) {
                    $this->view->actionErrors = $errors;
                } else {
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $registro->save();
                    $this->_redirect($this->_request->getControllerName() . "/loteitem");
                }  
            } else {
                $this->view->actionErrors[] = "Falha ao Executar Operação, Dados Inválidos!";
            }
		}
            $bt = TbBolsaTipo::pegaPorId($session->lote_item_dados["id_bolsa_tipo"]);
            if ($bt) {
                $this->view->bt = $bt;
            }
$this->view->registro = $registro;
        $this->view->lote = $registro->findParentRow("TbVinculoLote");
        $tb = new TbPrevisaoTipo();
        $pt = $tb->getPorChave($registro->tipo);
        $this->view->pt = $pt;
		$button = Escola_Button::getInstance();
        $button->setTitulo("VÍNCULO LOTE ÍTEM > EDITAR PAGAMENTO");
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addFromArray(array("titulo" => "Voltar",
                                        "controller" => $this->_request->getControllerName(),
                                        "action" => "loteitem",
                                        "img" => "icon-reply"));
	}
    
    public function addproblemaAction() {
        $registro = TbVinculoLoteItem::pegaPorId($this->_request->getParam("janela_problema_id"));
        if ($registro) {
            $problema = $this->_request->getParam("janela_problema_descricao");
            if (trim($problema)) {
                $registro->registrar_problema(array("usuario" => TbUsuario::pegaLogado(), "problema" => $problema));
                if ($registro->findParentRow("TbVinculoLoteItemStatus")->problema()) {
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                } else {
                    $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                }
            } else {
                $this->_flashMessage("CAMPO DESCRIÇÃO DO PROBLEMA OBRIGATÓRIO!");
            }
            $this->_redirect($this->_request->getControllerName() . "/loteitem/id/{$this->_request->getParam("id")}/tipo/{$this->_request->getParam("tipo")}/id_bolsa_tipo/{$this->_request->getParam("id_bolsa_tipo")}");            
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function viewAction() {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $this->view->registro = $registro;
            $this->view->ocorrencias = $registro->pega_ocorrencia();
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR LOTE");
            $button->addFromArray(array("titulo" => "Voltar",
                                    "controller" => $this->_request->getControllerName(),
                                    "action" => "index",
                                    "img" => "icon-reply"));
      } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");            
        }
    }
    
    public function addinaptoAction() {
        $registro = TbVinculoLoteItem::pegaPorId($this->_request->getParam("janela_inapto_id"));
        if ($registro) {
            $problema = $this->_request->getParam("janela_inapto_descricao");
            if (trim($problema)) {
                $registro->registrar_inapto(array("usuario" => TbUsuario::pegaLogado(), "problema" => $problema));
                if ($registro->findParentRow("TbVinculoLoteItemStatus")->inapto()) {
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                } else {
                    $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                }
            } else {
                $this->_flashMessage("CAMPO DESCRIÇÃO DO PROBLEMA OBRIGATÓRIO!");
            }
            $this->_redirect($this->_request->getControllerName() . "/loteitem/id/{$this->_request->getParam("id")}/tipo/{$this->_request->getParam("tipo")}/id_bolsa_tipo/{$this->_request->getParam("id_bolsa_tipo")}");            
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function cancelarinaptoAction() {
        $registro = TbVinculoLoteItem::pegaPorId($this->_request->getParam("id_vinculo_lote_item"));
        if ($registro && $registro->findParentRow("TbVinculoLoteItemStatus")->inapto()) {
            $registro->cancelar_inapto(array("usuario" => TbUsuario::pegaLogado()));
            if ($registro->findParentRow("TbVinculoLoteItemStatus")->pendente()) {
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
            }
            $this->_redirect($this->_request->getControllerName() . "/loteitem/id/{$this->_request->getParam("id")}/tipo/{$this->_request->getParam("tipo")}/id_bolsa_tipo/{$this->_request->getParam("id_bolsa_tipo")}");            
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function addprestacaocontaAction() {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("janela_pc_id"));
        if ($registro && $registro->findParentRow("TbVinculoLoteStatus")->pago()) {
            $arquivo = Escola_Util::getUploadedFile("janela_pc_arquivo");
            if ($arquivo && $arquivo["size"]) {
                $usuario = TbUsuario::pegaLogado();
                $flag = $registro->add_prestacao_conta(array("usuario" => $usuario, "arquivo" => $arquivo));
                if ($flag) {
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                } else {
                    $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                }
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }
    
    public function prestarcontaAction() {
        try {
            $tb_fp = new TbFormaPagamento();
            $tb_dc = new TbDocComprovacao();
            $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
            if (!$registro) {
                throw new Exception("Falha ao Executar Operação, Lote Não Localizado!");
            }
            $vinculo = $registro->findParentRow("TbVinculo");
            if (!$vinculo) {
                throw new Exception("Falha ao Executar Operação, Projeto Não Localizado!");
            }
            $vls = $registro->findParentRow("TbVinculoLoteStatus");
            if (!$vls) {
                throw new Exception("Falha ao Executar Operação, Lote Com Status inválido!");
            }
            if (!$vls->recurso() && !$vls->pago()) {
                throw new Exception("Falha ao Executar Operação, Status do Lote Inválido para Prestação de Contas!");
            }
            
            $session = Escola_Session::getInstance();
            $session->id_vinculo_lote = $registro->getId();

            $this->view->tipos = $registro->listar_tipo();
            $this->view->registro = $registro;
            $this->view->vinculo = $vinculo;
            $fps = $tb_fp->listar();
            if (!$fps || !count($fps)) {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA FORMA DE PAGAMENTO INFORMADA!");
                $this->_redirect($this->_request->getControllerName() . "/index/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
            }
            $this->view->fps = $fps;
            $dcs = $tb_dc->listar();
            if (!$dcs || !count($dcs)) {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUM TIPO DE DOCUMENTO DE COMPROVAÇÃO INFORMADO!");
                $this->_redirect($this->_request->getControllerName() . "/index/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
            }
            $this->view->dcs = $dcs;
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $usuario = TbUsuario::pegaLogado();
                $dados["id_usuario"] = $usuario->getId();
                $erro = $registro->pagar($dados);
                if ($erro) {
                    $this->addErro($erro);
                } else {
                    if ($registro->findParentRow("TbVinculoLoteStatus")->pago()) {
                        $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                    } else {
                        $this->_flashMessage("Falha ao Executar Operação, Chame o Administrador!");
                    }
                }
                $this->_redirect($this->_request->getControllerName() . "/index/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
            }
            $button = Escola_Button::getInstance();
            $button->setTitulo("VÍNCULO > LOTE > PRESTAÇÃO DE CONTAS");
            $button->addFromArray(array("titulo" => "Voltar",
                                        "controller" => $this->_request->getControllerName(),
                                        "action" => "index",
                                        "img" => "icon-reply",
                                        "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)));

        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function prestarcontaitemAction() {
        try {
            $id_vinculo = 0;
            $id_vinculo_lote = 0;
            $session = Escola_Session::getInstance();
            if (!isset($session->id_vinculo)) {
                throw new UnexpectedValueException("Falha ao Executar Operação, Registro do Vínculo Inválido!");
            }
            
            $id_vinculo = $session->id_vinculo;
            if (!isset($session->id_vinculo_lote)) {
                throw new UnexpectedValueException("Falha ao Executar Operação, Lote não Localizado!");
            }
            $id_vinculo_lote = $session->id_vinculo_lote;
            $lote = TbVinculoLote::pegaPorId($id_vinculo_lote);
            if (!$lote) {
                throw new Exception("Falha ao Executar Operação, Lote não Localizado!!");
            }
            
            $tipo = $this->getParam("tipo");
            $id_bolsa_tipo = $this->getParam("id_bolsa_tipo");
            if (empty($tipo) || empty($id_bolsa_tipo)) {
                throw new Exception("Falha ao Executar Operação, Dados do Ítem do Lote não Localizado!!");
            }
            
            $tb = new TbPrevisaoTipo();
            $dt = $tb->getPorChave($tipo);
            if (!$dt) {
                throw new Exception("Falha ao Executar Operação, Tipo de Despesa não Localizado!!");
            }
            
            $bt = TbBolsaTipo::pegaPorId($id_bolsa_tipo);
            if (!$dt) {
                throw new Exception("Falha ao Executar Operação, Tipo de Bolsa não Localizado!!");
            }
            
            $this->view->lote = $lote;
            $this->view->tipo = $dt;
            $this->view->bolsa_tipo = $bt;
                        
            $obj = $lote->pegaPrestacaoConta($dt->getId(), $bt->getId());
            if (!$obj) {
                $tb = new TbLotePrestacaoConta();
                $obj = $tb->createRow();
            }
            
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $files = Escola_Util::getUploadedFiles();
                $dados = array_merge($dados, $files);
                
                $obj->setFromArray($dados);
                try {
                    
                    $obj->save();
                    
                    $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/prestarconta/id_vinculo/{$id_vinculo}/id/{$id_vinculo_lote}");
                    
                } catch (Exception $ex) {
                    $this->view->actionErrors[] = $ex->getMessage();
                }                
            }
            
            $this->view->obj = $obj;
            
            $button = Escola_Button::getInstance();
            $button->setTitulo("VINCULO > LOTE > PRESTAÇÃO DE CONTAS");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");            
            $button->addFromArray(array("titulo" => "Voltar",
                                    "controller" => $this->_request->getControllerName(),
                                    "action" => "prestarconta",
                                    "img" => "icon-reply",
                                    "params" => array("id_vinculo" => $id_vinculo, "id" => $id_vinculo_lote)));
            
        } catch (UnexpectedValueException $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/prestarconta/id_vinculo/{$id_vinculo}/id/{$id_vinculo_lote}");
        }
    }
    
    public function prestarcontacancelarAction() {
        $id = $this->getParam("id");
        try {
            $id_vinculo = $id_vinculo_lote = 0;
            
            $session = Escola_Session::getInstance();
            if (!isset($session->id_vinculo)) {
                throw new UnexpectedValueException("Falha ao Executar Operação, Registro do Vínculo Inválido!");
            }
            
            $id_vinculo = $session->id_vinculo;
            if (!isset($session->id_vinculo_lote)) {
                throw new UnexpectedValueException("Falha ao Executar Operação, Lote não Localizado!");
            }
            $id_vinculo_lote = $session->id_vinculo_lote;
            
            settype($id, "integer");
            if (!$id) {
                throw new Exception("Falha ao Executar Operação, Pagamento não Localizado!");
            }            
            
            $obj = TbLotePrestacaoConta::pegaPorId($id);
            if (!$obj) {
                throw new Exception("Falha ao Executar Operação, Pagamento não Localizado!!");
            }
            
            $obj->delete();
            
            $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
            $this->_redirect($this->_request->getControllerName() . "/prestarconta/id_vinculo/{$id_vinculo}/id/{$id_vinculo_lote}");
            
        } catch (UnexpectedValueException $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/prestarconta/id_vinculo/{$id_vinculo}/id/{$id_vinculo_lote}");
        }
    }
}