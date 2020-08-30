<?php
class VinculoController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("listarbolsista", "json");
        $ajaxContext->addActionContext("vinculoloteitemform", "json");
        $ajaxContext->addActionContext("listarbolsatipo", "json");
        $ajaxContext->initContext();
    }

    public function listarbolsatipoAction()
    {
        $this->view->bolsa_tipos = false;
        $dados = $this->getRequest()->getPost();
        $tb = new TbBolsaTipo();
        $registros = $tb->listar($dados);
        if ($registros && count($registros)) {
            $items = array();
            foreach ($registros as $registro) {
                $pt = $registro->findParentRow("TbPrevisaoTipo");
                $obj = new stdClass();
                $obj->previsao_tipo = new stdClass();
                if ($pt) {
                    $obj->previsao_tipo->id = $pt->getId();
                    $obj->previsao_tipo->chave = $pt->chave;
                    $obj->previsao_tipo->descricao = $pt->descricao;
                }
                $obj->id = $registro->getId();
                $obj->chave = $registro->chave;
                $obj->descricao = $registro->descricao;
                $obj->to_string = $registro->toString();
                $obj->valor = $registro->pega_valor()->toString();
                $items[] = $obj;
            }
            $this->view->bolsa_tipos = $items;
        }
    }

    public function listarbolsistaAction()
    {
        $result = false;
        $dados = $this->getRequest()->getPost();
        $tb = new TbBolsistaStatus();
        $bs = $tb->getPorChave("A");
        if ($bs) {
            $dados["id_bolsista_status"] = $bs->getId();
        }
        $tb = new TbBolsista();
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
                $bt = $registro->findParentRow("TbBolsaTipo");
                $pf = $registro->findParentRow("TbPessoaFisica");
                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->bolsa_tipo = $bt->toString();
                $obj->cpf = Escola_Util::formatCpf($pf->cpf);
                $obj->nome = $pf->nome;
                $obj->valor = $bt->pega_valor()->toString();
                $items[] = $obj;
            }
            $this->view->items = $items;
        }
    }

    public function vinculoloteitemformAction()
    {
        $toform = false;
        $id_previsao_tipo = $this->_request->getPost("id_previsao_tipo");
        $id_vinculo_lote = $this->_request->getPost("id_vinculo_lote");
        if ($id_previsao_tipo && $id_vinculo_lote) {
            $pt = TbPrevisaoTipo::pegaPorId($id_previsao_tipo);
            $lote = TbVinculoLote::pegaPorId($id_vinculo_lote);
            if ($pt && $lote) {
                $tb = new TbVinculoLoteItem();
                $vli = $tb->createRow();
                $vli->tipo = $pt->chave;
                $vli->id_vinculo_lote = $lote->getId();
                $toform = $vli->toForm($this->view);
            }
        }
        $this->view->toform = $toform;
    }

    public function filtroAction()
    {
        $session = Escola_Session::getInstance();
        $filtros = array("filtro_id_vinculo_tipo", "filtro_codigo", "filtro_ano", "page", "filtro_descricao", "filtro_id_vinculo_status");
        $session->atualizaFiltros($filtros);
        $this->_redirect("vinculo/index");
    }

    public function indexAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_vinculo)) {
            unset($session->id_vinculo);
        }
        if (isset($session->id_info_bancaria)) {
            unset($session->id_info_bancaria);
        }
        $session->limparFiltro("id_info_bancaria");
        $tb = new TbVinculo();
        $page = $this->_getParam("page");
        $dados = $this->_request->getParams();
        $session = Escola_Session::getInstance();
        $filtros = array("filtro_id_vinculo_tipo", "filtro_codigo", "filtro_ano", "page", "filtro_descricao", "filtro_id_vinculo_status");
        $this->view->dados = $session->atualizaFiltros($filtros);
        $this->view->dados["pagina_atual"] = $page;
        $this->view->registros = $tb->listar_por_pagina($this->view->dados);
        $button = Escola_Button::getInstance();
        $button->setTitulo("VÍNCULOS");
        $button->addFromArray(array(
            "titulo" => "Adicionar",
            "controller" => "vinculo",
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
        $tb = new TbVinculoPessoaTipo();
        $vpts = $tb->listar();
        $tb = new TbVinculoTipo();
        $vts = $tb->listar();
        if (!count($vts)) {
            $this->_flashMessage("NENHUM TIPO DE VÍNCULO CADASTRADO!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
        $tb = new TbVinculoStatus();
        $vss = $tb->listar();
        if (!count($vss)) {
            $this->_flashMessage("NENHUM STATUS DE VÍNCULO CADASTRADO!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
        $id = $this->_request->getParam("id");
        $tb = new TbVinculo();
        if ($id) {
            $registro = $tb->getPorId($id);
        } else {
            $registro = $tb->createRow();
        }
        $dados = array();
        $ibs = array();
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $errors = array();
            $registro->setFromArray($dados);
            $vinculo_errors = $registro->getErrors();
            if (isset($dados["info_bancaria"]) && is_array($dados["info_bancaria"]) && count($dados["info_bancaria"])) {
                $ibs = $dados["info_bancaria"];
            } else {
                $vinculo_errors[] = "NENHUMA INFORMAÇÃO BANCÁRIA CADASTRADA!";
            }
            if (!isset($dados["id_pessoa_fisica_CO"]) || !$dados["id_pessoa_fisica_CO"]) {
                $pf_coordenador = $registro->pega_coordenador();
                if (!$pf_coordenador) {
                    $tb = new TbVinculoPessoaTipo();
                    $vpt = $tb->getPorChave("CO");
                    if ($vpt) {
                        $vinculo_errors[] = "CAMPO {$vpt->toString()} OBRIGATÓRIO!";
                    } else {
                        $vinculo_errors[] = "CAMPO COORDENADOR OBRIGATÓRIO!";
                    }
                }
            }
            if ($vinculo_errors) {
                $errors = $vinculo_errors;
            }
            if (count($errors)) {
                $this->view->actionErrors = $errors;
            } else {
                $registro->save();
                if ($registro->getId()) {
                    $tb = new TbVinculoPessoaTipo();
                    $vpts = $tb->listar();
                    if ($vpts) {
                        foreach ($vpts as $vpt) {
                            $field_name = "id_pessoa_fisica_" . $vpt->chave;
                            if (isset($dados[$field_name]) && $dados[$field_name]) {
                                $registro->set_vinculo_pessoa($vpt->getId(), $dados[$field_name]);
                            }
                        }
                    }
                    if (isset($ibs) && is_array($ibs) && count($ibs)) {
                        $rs = $registro->pega_info_bancaria();
                        if ($rs) {
                            foreach ($rs as $rs_ib) {
                                $flag = false;
                                foreach ($ibs as $ib) {
                                    if (isset($ib["id_info_bancaria"]) && ($rs_ib->getId() == $ib["id_info_bancaria"])) {
                                        $flag = true;
                                        break;
                                    }
                                }
                                if (!$flag) {
                                    $rs_ib->delete();
                                }
                            }
                        }
                        $tb = new TbInfoBancaria();
                        foreach ($ibs as $ib) {
                            $ib_row = false;
                            if (isset($ib["id_info_bancaria"]) && $ib["id_info_bancaria"]) {
                                $ib_row = $tb->getPorId($ib["id_info_bancaria"]);
                                unset($ib["id_info_bancaria"]);
                            }
                            if (!$ib_row) {
                                $ib_row = $tb->createRow();
                            }
                            $ib_row->setFromArray($ib);
                            $ib_errors = $ib_row->getErrors();
                            if (!$ib_errors) {
                                $ib_row->save();
                                $registro->add_info_bancaria($ib_row);
                            }
                        }
                    }
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                } else {
                    $this->view->actionErrors = array("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                }
            }
        } else {
            $rs = $registro->pega_info_bancaria();
            if ($rs) {
                foreach ($rs as $obj) {
                    $obj_ib = array();
                    $obj_ib["id_info_bancaria"] = $obj->getId();
                    $obj_ib["id_info_bancaria_tipo"] = $obj->id_info_bancaria_tipo;
                    $obj_ib["info_bancaria_tipo"] = "";
                    $ibt = $obj->findParentRow("TbInfoBancariaTipo");
                    if ($ibt) {
                        $obj_ib["info_bancaria_tipo"] = $ibt->toString();
                    }
                    $obj_ib["id_banco"] = $obj->id_banco;
                    $obj_ib["banco"] = "";
                    $banco = $obj->findParentRow("TbBanco");
                    if ($banco) {
                        $obj_ib["banco"] = $banco->toString();
                    }
                    $obj_ib["agencia"] = $obj->agencia;
                    $obj_ib["agencia_dv"] = $obj->agencia_dv;
                    $obj_ib["agencia_show"] = $obj->mostrar_agencia();
                    $obj_ib["conta"] = $obj->conta;
                    $obj_ib["conta_dv"] = $obj->conta_dv;
                    $obj_ib["conta_show"] = $obj->mostrar_conta();
                    $ibs[] = $obj_ib;
                }
            }
        }
        $this->view->vpts = $vpts;
        $this->view->registro = $registro;
        $this->view->vts = $vts;
        $this->view->vss = $vss;
        $this->view->dados = $dados;
        $this->view->ibs = $ibs;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE VÍNCULO - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE VÍNCULO - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction()
    {
        $registro = TbVinculo::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $errors = $registro->getDeleteErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->_flashMessage($erro);
                }
            } else {
                $registro->delete();
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }

    public function viewAction()
    {
        $tb = new TbVinculo();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $tb = new TbVinculoPessoaTipo();
            $vpts = $tb->listar();
            if ($vpts) {
                $this->view->registro = $registro;
                $this->view->vpts = $vpts;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR VÍNCULO");
                $button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function bolsatipoAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        if ($vinculo) {
            $tb = new TbBolsaTipo();
            $page = $this->_getParam("page");
            $dados = $this->_request->getParams();
            $dados["pagina_atual"] = $page;
            $this->view->registros = $tb->listar_por_pagina($dados);
            $this->view->vinculo = $vinculo;
            $button = Escola_Button::getInstance();
            $button->setTitulo("VÍNCULO > TIPOS DE DESPESA");
            $button->addFromArray(array(
                "titulo" => "Adicionar",
                "controller" => $this->_request->getControllerName(),
                "action" => "editarbolsatipo",
                "img" => "icon-plus-sign",
                "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
            ));
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "index",
                "img" => "icon-reply",
                "params" => array("id" => 0)
            ));
        } else {
            $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarbolsatipoAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        if ($vinculo) {
            $tb = new TbMoeda();
            $moedas = $tb->listar();
            if (!count($moedas)) {
                $this->_flashMessage("Nenhuma Moeda Cadastrada, Cadastre uma Moeda antes de continuar!");
                $this->_redirect($this->_request->getControllerName() . "/bolsatipo/id_vinculo/{$vinculo->getId()}/id/0");
            }
            $id = $this->_request->getParam("id");
            $tb = new TbBolsaTipo();
            if ($id) {
                $registro = $tb->getPorId($id);
            } else {
                $registro = $tb->createRow();
            }
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $dados["id_vinculo"] = $vinculo->getId();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors && count($errors)) {
                    $this->view->actionErrors = $errors;
                } else {
                    $registro->save();
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/bolsatipo/id_vinculo/{$vinculo->getId()}/id/0");
                }
            }
            $this->view->registro = $registro;
            $this->view->vinculo = $vinculo;
            $button = Escola_Button::getInstance();
            if ($this->view->registro->getId()) {
                $button->setTitulo("CADASTRO DE TIPO DE DESPESA - ALTERAR");
            } else {
                $button->setTitulo("CADASTRO DE TIPO DE DESPESA - INSERIR");
            }
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "bolsatipo",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
            ));
        } else {
            $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewbolsatipoAction()
    {
        $tb = new TbBolsaTipo();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $this->view->vinculo = $registro->findParentRow("TbVinculo");
            $this->view->registro = $registro;
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR TIPO DE DESPESA");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "bolsatipo",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $this->view->vinculo->getId(), "id" => 0)
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function excluirbolsatipoAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        $registro = TbBolsaTipo::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $errors = $registro->getDeleteErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->_flashMessage($erro);
                }
            } else {
                $registro->delete();
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/bolsatipo/id_vinculo/" . $vinculo->getId());
    }

    public function bolsistaAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_bolsista)) {
            unset($session->id_bolsista);
        }
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        if ($vinculo) {
            $this->view->bts = $vinculo->pega_bolsa_tipo();
            $bts = $vinculo->findDependentRowSet("TbBolsaTipo");
            if ($bts && count($bts)) {
                $tb = new TbBolsista();
                $page = $this->_getparam("page");
                $session = Escola_Session::getInstance();
                $filtros = array("page", "filtro_nome", "filtro_id_bolsa_tipo", "filtro_id_bolsista_status");
                $this->view->dados = $session->atualizaFiltros($filtros);
                $this->view->dados["pagina_atual"] = $page;
                $this->view->dados["id_vinculo"] = $vinculo->getId();
                $this->view->vinculo = $vinculo;
                $this->view->registros = $tb->listar_por_pagina($this->view->dados);
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > BOLSISTAS");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => "vinculo",
                    "action" => "editarbolsista",
                    "img" => "icon-plus-sign",
                    "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Pesquisar",
                    "onclick" => "pesquisar()",
                    "img" => "icon-search",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0)
                ));
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Nenhum Tipo de Bolsa Cadastrado!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarbolsistaAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        if ($vinculo) {
            $bts = $vinculo->pega_bolsa_tipo();
            if (!$bts) {
                $this->_flashMessage("Nenhum Tipo de Bolsa Cadastrado, Cadastre um tipo de bolsa para poder Cadastrar um Bolsista!");
                $this->_redirect($this->_request->getControllerName() . "/bolsista/id_vinculo/{$vinculo->getId()}/id/0");
            }
            $id = $this->_request->getParam("id");
            $tb = new TbBolsista();
            if ($id) {
                $registro = $tb->getPorId($id);
            } else {
                $registro = $tb->createRow();
            }
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $dados["id_vinculo"] = $vinculo->getId();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors && count($errors)) {
                    $this->view->actionErrors = $errors;
                } else {
                    $registro->save();
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/bolsista/id_vinculo/{$vinculo->getId()}/id/0");
                }
            }
            $this->view->registro = $registro;
            $this->view->vinculo = $vinculo;
            $this->view->bts = $bts;
            $this->view->ib = $registro->pega_info_bancaria();
            $button = Escola_Button::getInstance();
            if ($this->view->registro->getId()) {
                $button->setTitulo("CADASTRO DE BOLSISTA - ALTERAR");
            } else {
                $button->setTitulo("CADASTRO DE BOLSISTA - INSERIR");
            }
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "bolsista",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
            ));
        } else {
            $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewbolsistaAction()
    {
        $tb = new TbBolsista();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $pf = $registro->findParentRow("TbPessoaFisica");
            $pessoa = $pf->findParentRow("TbPessoa");
            $this->view->vinculo = $registro->findParentRow("TbVinculo");
            $this->view->registro = $registro;
            $this->view->ib = $registro->pega_info_bancaria();
            $this->view->pf = $pf;
            $this->view->pessoa = $pf->findParentRow("TbPessoa");
            $this->view->ocorrencias = $registro->pega_ocorrencia();
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR BOLSISTA");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "bolsista",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $this->view->vinculo->getId(), "id" => 0)
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function excluirbolsistaAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        $registro = TbBolsista::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $errors = $registro->getDeleteErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->_flashMessage($erro);
                }
            } else {
                $registro->delete();
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/bolsista/id_vinculo/" . $vinculo->getId());
    }

    public function desativarAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        $registro = TbBolsista::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $registro->desativar();
            if ($registro->ativo()) {
                $this->_flashMessage("Falha ao Executar Operação, Chame o Administrador!");
            } else {
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/bolsista/id_vinculo/" . $vinculo->getId());
    }

    public function ativarAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        $registro = TbBolsista::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $registro->ativar();
            if (!$registro->ativo()) {
                $this->_flashMessage("Falha ao Executar Operação, Chame o Administrador!");
            } else {
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/bolsista/id_vinculo/" . $vinculo->getId());
    }

    public function previsaoAction()
    {
        $sessao = Escola_Session::getInstance();
        $sessao->limparFiltros(array("id_vinculo", "id_previsao_tipo", "id_bolsa_tipo", "ano"));
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        if ($vinculo) {
            $this->view->bts = $vinculo->pega_bolsa_tipo();
            $bts = $vinculo->findDependentRowSet("TbBolsaTipo");
            if ($bts && count($bts)) {
                $tb = new TbPrevisao();
                $page = $this->_getparam("page");
                $session = Escola_Session::getInstance();
                $filtros = array("page", "filtro_id_previsao_tipo", "filtro_id_bolsa_tipo", "filtro_ano", "filtro_mes");
                $this->view->dados = $session->atualizaFiltros($filtros);
                $this->view->dados["pagina_atual"] = $page;
                $this->view->dados["id_vinculo"] = $vinculo->getId();
                $this->view->registros = $tb->listar_por_pagina($this->view->dados);
                $this->view->vinculo = $vinculo;
                $this->view->meses = Escola_Util::pegaMeses();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > PREVISÃO");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => "vinculo",
                    "action" => "addprevisao",
                    "img" => "icon-plus-sign",
                    "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Pesquisar",
                    "onclick" => "pesquisar()",
                    "img" => "icon-search",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0)
                ));
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Nenhum Tipo de Bolsa Cadastrado!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function addprevisaoAction()
    {
        $sessao = Escola_Session::getInstance();
        $filtros = array("id_vinculo", "id_previsao_tipo", "id_bolsa_tipo", "ano");
        $dados = $sessao->atualizaFiltros($filtros);
        $vinculo = TbVinculo::pegaPorId($dados["id_vinculo"]);
        if ($vinculo) {
            $tb = new TbPrevisaoTipo();
            $pts = $tb->listar();
            if ($pts) {
                $this->view->pts = $pts;
                $this->view->bts = $vinculo->pega_bolsa_tipo();
                $this->view->vinculo = $vinculo;
                $this->view->id_previsao_tipo = $dados["id_previsao_tipo"];
                $this->view->id_bolsa_tipo = $dados["id_bolsa_tipo"];
                $this->view->ano = $dados["ano"];
                if (!$this->view->ano) {
                    $this->view->ano = date("Y");
                }
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > PREVISÃO");
                $button->addScript("Continuar", "salvarFormulario('formulario')", "icon-hand-right");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "previsao",
                    "img" => "icon-reply",
                    "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
                ));
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
                $this->_redirect($this->_request->getControllerName() . "/vinculo/id_vinculo/" . $vinculo->getId());
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function addprevisaovalorAction()
    {
        $sessao = Escola_Session::getInstance();
        $filtros = array("id_vinculo", "id_previsao_tipo", "id_bolsa_tipo", "ano");
        $dados = $sessao->atualizaFiltros($filtros);
        $vinculo = TbVinculo::pegaPorId($dados["id_vinculo"]);
        if ($vinculo) {
            $pt = TbPrevisaoTipo::pegaPorId($dados["id_previsao_tipo"]);
            if ($pt) {
                $bt = TbBolsaTipo::pegaPorId($dados["id_bolsa_tipo"]);
                if (!$bt) {
                    $this->_flashMessage("Falha ao Executar Operação, Tipo de Despesa Obrigatório!");
                    $this->_redirect($this->_request->getControllerName() . "/addprevisao/id_vinculo/" . $vinculo->getId());
                }
                $ano = $dados["ano"];
                if (!$ano) {
                    $this->_flashMessage("Falha ao Executar Operação, Campo Ano Obrigatório!");
                    $this->_redirect($this->_request->getControllerName() . "/addprevisao/id_vinculo/" . $vinculo->getId());
                }
                $this->view->vinculo = $vinculo;
                $this->view->pt = $pt;
                $this->view->bt = $bt;
                $this->view->ano = $ano;
                $this->view->meses = Escola_Util::pegaMeses();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > PREVISÃO");
                $button->addScript("Continuar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "previsao",
                    "img" => "icon-reply",
                    "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
                ));
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Tipo de Previsão Obrigatório!");
                $this->_redirect($this->_request->getControllerName() . "/addprevisao/id_vinculo/" . $vinculo->getId());
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function salvarprevisaovalorAction()
    {
        $sessao = Escola_Session::getInstance();
        $filtros = array("id_vinculo", "id_previsao_tipo", "id_bolsa_tipo", "ano");
        $dados = $sessao->atualizaFiltros($filtros);
        if (!$dados["id_bolsa_tipo"]) {
            unset($dados["id_bolsa_tipo"]);
        }
        $post = $this->_request->getPost();
        $tb = new TbPrevisao();
        if (isset($post["valor"]) && is_array($post["valor"]) && count($post["valor"])) {
            foreach ($post["valor"] as $mes => $valor) {
                $vlr = Escola_Util::montaNumero($valor);
                if (($vlr > 0)) {
                    $dados["mes"] = $mes;
                    $previsao = false;
                    $previsaos = $tb->listar($dados);
                    if ($previsaos) {
                        $previsao = $previsaos->current();
                    } else {
                        $previsao = $tb->createRow();
                    }
                    $dados["valor"] = $valor;
                    $previsao->setFromArray($dados);
                    $errors = $previsao->getErrors();
                    if (!$errors) {
                        $previsao->save();
                    }
                }
            }
            $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
            $this->_redirect($this->_request->getControllerName() . "/previsao/id_vinculo/" . $dados["id_vinculo"]);
        }
    }

    public function viewprevisaoAction()
    {
        $tb = new TbPrevisao();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $this->view->registro = $registro;
            $this->view->vinculo = $registro->findParentRow("TbVinculo");
            $this->view->pt = $registro->findParentRow("TbPrevisaoTipo");
            $this->view->bt = $registro->findParentRow("TbBolsaTipo");
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR PREVISÃO");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "previsao",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $this->view->vinculo->getId(), "id" => 0)
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarprevisaoAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        if ($vinculo) {
            $tb = new TbMoeda();
            $moedas = $tb->listar();
            if (!count($moedas)) {
                $this->_flashMessage("Nenhuma Moeda Cadastrada, Cadastre uma Moeda antes de continuar!");
                $this->_redirect($this->_request->getControllerName() . "/bolsatipo/id_vinculo/{$vinculo->getId()}/id/0");
            }
            $id = $this->_request->getParam("id");
            $tb = new TbPrevisao();
            if ($id) {
                $registro = $tb->getPorId($id);
            } else {
                $registro = $tb->createRow();
            }
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors && count($errors)) {
                    $this->view->actionErrors = $errors;
                } else {
                    $registro->save();
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/previsao/id_vinculo/{$vinculo->getId()}/id/0");
                }
            }
            $this->view->registro = $registro;
            $this->view->vinculo = $vinculo;
            $this->view->pt = $registro->findParentRow("TbPrevisaoTipo");
            $this->view->bt = $registro->findParentRow("TbBolsaTipo");
            $button = Escola_Button::getInstance();
            if ($this->view->registro->getId()) {
                $button->setTitulo("CADASTRO DE PREVISÃO - ALTERAR");
            } else {
                $button->setTitulo("CADASTRO DE PREVISÃO - INSERIR");
            }
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "previsao",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
            ));
        } else {
            $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function excluirprevisaoAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        $registro = TbPrevisao::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $errors = $registro->getDeleteErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->_flashMessage($erro);
                }
            } else {
                $registro->delete();
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/previsao/id_vinculo/" . $vinculo->getId());
    }

    public function loteAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_vinculo_lote)) {
            unset($session->id_vinculo_lote);
        }

        $id_vinculo = 0;
        if (isset($session->id_vinculo)) {
            $id_vinculo = $session->id_vinculo;
        } else {
            $id_vinculo = $this->_request->getParam("id_vinculo");
        }
        $vinculo = TbVinculo::pegaPorId($id_vinculo);
        if ($vinculo) {
            $session->id_vinculo = $id_vinculo;
            $tb = new TbVinculoLote();
            $session = Escola_Session::getInstance();
            $filtros = array("page", "filtro_id_vinculo", "filtro_id_vinculo_lote_status", "filtro_mes", "filtro_ano");
            $this->view->dados = $session->atualizaFiltros($filtros);
            $this->view->dados["pagina_atual"] = $this->view->dados["page"];
            $this->view->dados["id_vinculo"] = $vinculo->getId();
            $this->view->registros = $tb->listar_por_pagina($this->view->dados);
            $this->view->vinculo = $vinculo;
            $this->view->meses = Escola_Util::pegaMeses();
            $button = Escola_Button::getInstance();
            $button->setTitulo("VÍNCULO > LOTES");
            $button->addFromArray(array(
                "titulo" => "Adicionar",
                "onclick" => "adicionar()",
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
                "controller" => $this->_request->getControllerName(),
                "action" => "index",
                "img" => "icon-reply",
                "params" => array("id" => 0)
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function addloteAction()
    {
        $session = Escola_Session::getInstance();
        $id_vinculo = 0;
        if (isset($session->id_vinculo)) {
            $id_vinculo = $session->id_vinculo;
        } else {
            $id_vinculo = $this->_request->getParam("id_vinculo");
        }
        $dados = $this->_request->getPost();
        $vinculo = TbVinculo::pegaPorId($id_vinculo);
        if ($vinculo) {
            $tb = new TbVinculoLote();
            $dados["id_vinculo"] = $vinculo->getId();
            $dados["ano"] = $dados["jan_ano"];
            $dados["mes"] = $dados["jan_mes"];
            $objs = $tb->listar($dados);
            if ($objs) {
                $this->_flashMessage("Lote já cadastrado!");
            } else {
                $dados["ano"] = $dados["jan_ano"];
                $dados["mes"] = $dados["jan_mes"];
                $registro = $tb->createRow();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors) {
                    foreach ($errors as $erro) {
                        $this->_flashMessage($erro);
                    }
                } else {
                    $id = $registro->save();
                    if ($id) {
                        $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                    } else {
                        $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
                    }
                }
            }
            $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/" . $vinculo->getId());
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function excluirloteAction()
    {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $errors = $registro->getDeleteErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->_flashMessage($erro);
                }
            } else {
                $registro->delete();
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/lote");
    }

    public function viewloteAction()
    {
        $tb = new TbVinculoLote();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $this->view->registro = $registro;
            $this->view->ocorrencias = $registro->pega_ocorrencia();
            $this->view->arquivo_pc = $registro->pega_arquivo_pc();
            $this->view->vinculo = $registro->findParentRow("TbVinculo");
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR LOTE");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "lote",
                "img" => "icon-reply",
                "params" => array("id" => 0)
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function loteitemAction()
    {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $vls = $registro->findParentRow("TbVinculoLoteStatus");
            $dados = array("id_vinculo_lote" => $registro->getId(), "pagina_atual" => $this->_getParam("page"));
            $tb = new TbVinculoLoteItem();
            $this->view->registro = $registro;
            $this->view->vinculo = $registro->findParentRow("TbVinculo");
            $this->view->registros = $tb->listar_por_pagina($dados);
            $button = Escola_Button::getInstance();
            $button->setTitulo("VINCULO > LOTE > GERENCIAR LOTES");
            if ($vls->aguardando_liberacao() || $vls->aguardando_aprovacao()) {
                $button->addFromArray(array(
                    "titulo" => "Adicionar Pagamento",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "addloteitem",
                    "img" => "icon-plus-sign",
                    "params" => array("id_vinculo" => $this->view->vinculo->getId(), "id" => $registro->getId())
                ));
                $button->addFromArray(array(
                    "titulo" => "Atualiza Bolsistas Ativos",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "atualizaloteitem",
                    "img" => "icon-refresh",
                    "class" => "link_confirma",
                    "params" => array("id_vinculo" => $this->view->vinculo->getId(), "id" => $registro->getId())
                ));
            }
            $button->addFromArray(array(
                "titulo" => "Resumo",
                "onclick" => "resumo_{$registro->getId()}()",
                "img" => "icon-list-alt",
                "params" => array("id" => 0)
            ));
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "lote",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $this->view->vinculo->getId(), "id" => 0)
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function atualizaloteitemAction()
    {
        $lote = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($lote) {
            $vinculo = $lote->findParentRow("TbVinculo");
            $flag = $lote->atualiza_bolsista();
            if ($flag) {
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                $this->_redirect($this->_request->getControllerName() . "/loteitem/id_vinculo/{$vinculo->getId()}/id/{$lote->getId()}");
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index/id_vinculo/{$vinculo->getId()}");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewloteitemAction()
    {
        $tb = new TbVinculoLoteItem();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $this->view->registro = $registro;
            $this->view->lote = $registro->findParentRow("TbVinculoLote");
            $this->view->vinculo = $this->view->lote->findParentRow("TbVinculo");
            $this->view->ocorrencias = $registro->pega_ocorrencia();
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR ÍTEM DE LOTE");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "loteitem",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $this->view->vinculo->getId(), "id" => $this->view->lote->getId())
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function excluirloteitemAction()
    {
        $registro = TbVinculoLoteItem::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $lote = $registro->findParentRow("TbVinculoLote");
            $vinculo = $lote->findParentRow("TbVinculo");
            $errors = $registro->getDeleteErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->_flashMessage($erro);
                }
            } else {
                $registro->delete();
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
            $this->_redirect($this->_request->getControllerName() . "/loteitem/id_vinculo/{$vinculo->getId()}/id/{$lote->getId()}");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function liberarAction()
    {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $vinculo = $registro->findParentRow("TbVinculo");
            if ($registro->findParentRow("TbVinculoLoteStatus")->aguardando_liberacao()) {
                $registro->liberar();
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Lote Não Pode ser Liberado!");
            }
            $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function gerarnfAction()
    {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $vinculo = $registro->findParentRow("TbVinculo");
            if ($registro->findParentRow("TbVinculoLoteStatus")->aprovado()) {
                $usuario = TbUsuario::pegaLogado();
                $registro->gerar_nf($usuario);
                if ($registro->findParentRow("TbVinculoLoteStatus")->nf()) {
                    $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                } else {
                    $this->_flashMessage("Falha ao Executar Operação, Chame o Administrador!");
                }
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Status Inválido!");
            }
            $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function recursoAction()
    {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $vinculo = $registro->findParentRow("TbVinculo");
            if ($registro->findParentRow("TbVinculoLoteStatus")->nf()) {
                $usuario = TbUsuario::pegaLogado();
                $registro->registra_recurso($usuario);
                if ($registro->findParentRow("TbVinculoLoteStatus")->recurso()) {
                    $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                } else {
                    $this->_flashMessage("Falha ao Executar Operação, Chame o Administrador!");
                }
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Status Inválido!");
            }
            $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function pagarAction()
    {
        $tb_fp = new TbFormaPagamento();
        $tb_dc = new TbDocComprovacao();
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $vinculo = $registro->findParentRow("TbVinculo");
            $vls = $registro->findParentRow("TbVinculoLoteStatus");
            if ($vls->aprovado() || $vls->recurso()) {
                $session = Escola_Session::getInstance();
                $session->id_vinculo_lote = $registro->getId();

                $this->view->tipos = $registro->listar_tipo();
                $this->view->registro = $registro;
                $this->view->vinculo = $vinculo;
                $fps = $tb_fp->listar();
                if (!$fps || !count($fps)) {
                    $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA FORMA DE PAGAMENTO INFORMADA!");
                    $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
                }
                $this->view->fps = $fps;
                $dcs = $tb_dc->listar();
                if (!$dcs || !count($dcs)) {
                    $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUM TIPO DE DOCUMENTO DE COMPROVAÇÃO INFORMADO!");
                    $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
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
                    $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
                }
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > LOTE > CONFIRMAR PAGAMENTO");
                //                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "lote",
                    "img" => "icon-reply",
                    "params" => array("id_vinculo" => $vinculo->getId(), "id" => 0)
                ));
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Status Inválido!");
                $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function confirmarpcAction()
    {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $vinculo = $registro->findParentRow("TbVinculo");
            if ($registro->findParentRow("TbVinculoLoteStatus")->aguardando_pc()) {
                $usuario = TbUsuario::pegaLogado();
                try {
                    $registro->confirmar_pc($usuario);
                    if (!$registro->findParentRow("TbVinculoLoteStatus")->pc()) {
                        throw new Exception("Falha ao Executar Operação, Chame o Administrador!");
                    }
                    $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                } catch (Exception $ex) {
                    $this->_flashMessage($ex->getMessage());
                }
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Status Inválido!");
            }
            $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$vinculo->getId()}/id/{$registro->getId()}");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function resumoAction()
    {
        $tb = new TbVinculo();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $filtros = array("filtro_ano");
            $session = Escola_Session::getInstance();
            $dados = $session->atualizaFiltros($filtros);
            if (!$dados["filtro_ano"]) {
                $dados["filtro_ano"] = $registro->ano;
            }
            $this->view->dados = $dados;
            $this->view->registro = $registro;
            $this->view->meses = Escola_Util::pegaMeses();
            $this->view->anos = $registro->pega_anos();
            $button = Escola_Button::getInstance();
            $button->setTitulo("RESUMO DO PROJETO");
            $button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function bolsistapagamentoAction()
    {
        $session = Escola_Session::getInstance();
        $id_bolsista = $this->_getParam("id");
        if (isset($session->id_bolsista) && $session->id_bolsista) {
            $id_bolsista = $session->id_bolsista;
        }
        $tb = new TbBolsista();
        $bolsista = $tb->getPorId($id_bolsista);
        if ($bolsista) {
            $session->id_bolsista = $bolsista->getId();
            $this->view->bolsista = $bolsista;
            $page = $this->_getParam("page");
            $tb = new TbVinculoLoteItem();
            $dados = array();
            $dados["pagina_atual"] = $page;
            $dados["tipo"] = "BO";
            $dados["chave"] = $bolsista->getId();
            $this->view->registros = $tb->listar_por_pagina($dados);
            $button = Escola_Button::getInstance();
            $button->setTitulo("BOLSISTAS > PAGAMENTOS");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "bolsista",
                "img" => "icon-reply",
                "params" => array("id" => 0, "id_vinculo" => $bolsista->id_vinculo)
            ));
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewbolsistapagamentoAction()
    {
        $tb = new TbVinculoLoteItem();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $bolsista = $registro->pega_referencia();
            $vl = $registro->findParentRow("TbVinculoLote");
            $this->view->registro = $registro;
            $this->view->bolsista = $bolsista;
            $this->view->vinculo_lote = $vl;
            $this->view->ocorrencias = $vl->pega_ocorrencia();
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR BOLSISTA > PAGAMENTO");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "bolsistapagamento",
                "img" => "icon-reply",
                "params" => array("id" => 0)
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/bolsistapagamento");
        }
    }

    public function aprovarAction()
    {
        $registro = TbVinculoLote::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $usuario = TbUsuario::pegaLogado();
            $erros = $registro->get_erros_aprovar($usuario);
            if (!$erros) {
                $flag = $registro->aprovar($usuario);
                if ($flag) {
                    $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
                } else {
                    $this->_flashMessage("Falha ao Executar Operação, Lote não disponível para Aprovação!");
                }
            } else {
                foreach ($erros as $erro) {
                    $this->_flashMessage($erro);
                }
            }
            $this->_redirect($this->_request->getControllerName() . "/lote/id_vinculo/{$registro->id_vinculo}/id/{$registro->getId()}");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function addloteitemAction()
    {
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
                        $this->_redirect($this->_request->getControllerName() . "/loteitem/id/{$registro->getId()}");
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
        $button->setTitulo("VÍNCULO > LOTE > ADICIONAR PAGAMENTO");
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "loteitem",
            "img" => "icon-reply",
            "params" => array("id" => $registro->getId())
        ));
    }

    public function movimentoAction()
    {
        $id_vinculo = false;
        $session = Escola_Session::getInstance();
        if (isset($session->id_vinculo)) {
            $id_vinculo = $session->id_vinculo;
        } else {
            $id_vinculo = $this->_request->getParam("id");
        }
        $vinculo = TbVinculo::pegaPorId($id_vinculo);
        if ($vinculo) {
            $session->id_vinculo = $vinculo->getId();
            $ibs = $vinculo->pega_info_bancaria();
            $dados = $session->atualizaFiltros(array("id_info_bancaria"));
            if (!$dados["id_info_bancaria"]) {
                $info_bancaria = $ibs->current();
                $dados["id_info_bancaria"] = $info_bancaria->getId();
            }
            $tb = new TbInfoBancaria();
            $info_bancaria = $tb->getPorId($dados["id_info_bancaria"]);
            if ($info_bancaria) {
                $session->id_info_bancaria = $info_bancaria->getId();
                $info_bancaria->atualizaSaldoAnterior();
                $this->view->vinculo = $vinculo;
                $this->view->info_bancaria = $info_bancaria;
                $this->view->ibs = $ibs;
                $tb = new TbVinculoMovimento();
                $page = $this->_request->getParam("page");
                $this->view->registros = $tb->listar_por_pagina(array("filtro_id_info_bancaria" => $info_bancaria->getId(), "pagina_atual" => $page));
                $button = Escola_Button::getInstance();
                $button->setTitulo("VINCULO > MOVIMENTO");
                $button->addFromArray(array(
                    "titulo" => "Adicionar Entrada de Recurso",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "addreceita",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Adicionar Saída de Recurso",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "adddespesa",
                    "img" => "icon-minus-sign",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Imprimir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "movimentoprint",
                    "img" => "icon-print",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0)
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function addreceitaAction()
    {
        $session = Escola_Session::getInstance();
        $id_vinculo = $session->id_vinculo;
        $id_vm = $this->_request->getParam("id");
        if ($id_vinculo) {
            $vinculo = TbVinculo::pegaPorId($id_vinculo);
            if ($vinculo) {
                $session = Escola_Session::getInstance();
                $session->id_vinculo = $vinculo->getId();
                $info_bancaria = TbInfoBancaria::pegaPorId($session->id_info_bancaria);
                //$info_bancaria = $vinculo->pega_info_bancaria();
                if ($info_bancaria) {
                    $this->view->vinculo = $vinculo;
                    $this->view->info_bancaria = $info_bancaria;

                    $tb = new TbVinculoMovimento();
                    if ($id_vm) {
                        $receita = $tb->pegaPorId($id_vm);
                    } else {
                        $receita = $tb->createRowReceita();
                    }

                    $this->view->receita = $receita;

                    if ($this->_request->isPost()) {
                        $dados = $this->_request->getPost();
                        $receita->set_info_bancaria($info_bancaria);
                        $receita->setFromArray($dados);
                        $errors = $receita->getErrors();
                        if ($errors) {
                            foreach ($errors as $erro) {
                                $this->view->actionErrors[] = $erro;
                            }
                        } else {
                            $receita->save();
                            if ($receita->getId()) {
                                $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
                                $this->_redirect("vinculo/movimento");
                            } else {
                                $this->view->actionErrors[] = "FALHA AO EXECUTAR OPERAÇÃO!";
                            }
                        }
                    }

                    $button = Escola_Button::getInstance();
                    if ($id_vm) {
                        $button->setTitulo("VÍNCULO > MOVIMENTO > ALTERAR RECEITA");
                    } else {
                        $button->setTitulo("VÍNCULO > MOVIMENTO > ADICIONAR RECEITA");
                    }
                    $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                    $button->addFromArray(array(
                        "titulo" => "Cancelar",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "movimento",
                        "img" => "icon-remove-circle",
                        "params" => array("id" => 0)
                    ));
                } else {
                    $this->_flashMessage("NENHUMA CONTA DISPONÍVEL!");
                    $this->_redirect($this->_request->getControllerName() . "/movimento");
                }
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function adddespesaAction()
    {
        $session = Escola_Session::getInstance();
        $id_vinculo = $session->id_vinculo;
        $id_vm = $this->_request->getParam("id");
        if ($id_vinculo) {
            $vinculo = TbVinculo::pegaPorId($id_vinculo);
            if ($vinculo) {
                $session = Escola_Session::getInstance();
                $session->id_vinculo = $vinculo->getId();
                $info_bancaria = TbInfoBancaria::pegaPorId($session->id_info_bancaria);
                //$info_bancaria = $vinculo->pega_info_bancaria();
                if ($info_bancaria) {
                    $this->view->vinculo = $vinculo;
                    $this->view->info_bancaria = $info_bancaria;
                    $tb = new TbVinculoMovimento();

                    $tb = new TbVinculoMovimento();
                    if ($id_vm) {
                        $despesa = $tb->pegaPorId($id_vm);
                    } else {
                        $despesa = $tb->createRowDespesa();
                    }

                    $this->view->despesa = $despesa;

                    if ($this->_request->isPost()) {
                        $dados = $this->_request->getPost();
                        $despesa->set_info_bancaria($info_bancaria);
                        $despesa->setFromArray($dados);
                        $errors = $despesa->getErrors();
                        if ($errors) {
                            foreach ($errors as $erro) {
                                $this->view->actionErrors[] = $erro;
                            }
                        } else {
                            $despesa->save();
                            if ($despesa->getId()) {
                                $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
                                $this->_redirect("vinculo/movimento");
                            } else {
                                $this->view->actionErrors[] = "FALHA AO EXECUTAR OPERAÇÃO!";
                            }
                        }
                    }

                    $button = Escola_Button::getInstance();
                    if ($id_vm) {
                        $button->setTitulo("VÍNCULO > MOVIMENTO > ALTERAR DESPESA");
                    } else {
                        $button->setTitulo("VÍNCULO > MOVIMENTO > ADICIONAR DESPESA");
                    }
                    $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                    $button->addFromArray(array(
                        "titulo" => "Cancelar",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "movimento",
                        "img" => "icon-remove-circle",
                        "params" => array("id" => 0)
                    ));
                } else {
                    $this->_flashMessage("NENHUMA CONTA DISPONÍVEL!");
                    $this->_redirect($this->_request->getControllerName() . "/movimento");
                }
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function movimentoprintAction()
    {
        $id_vinculo = false;
        $session = Escola_Session::getInstance();
        if (isset($session->id_vinculo)) {
            $id_vinculo = $session->id_vinculo;
        } else {
            $id_vinculo = $this->_request->getParam("id");
        }
        $vinculo = TbVinculo::pegaPorId($id_vinculo);
        if ($vinculo) {
            $session->id_vinculo = $vinculo->getId();
            $info_bancaria = TbInfoBancaria::pegaPorId($session->id_info_bancaria);
            //$info_bancaria = $vinculo->pega_info_bancaria();
            if ($info_bancaria) {
                $relatorio = new Escola_Relatorio_VinculoMovimento($vinculo);
                $relatorio->set_info_bancaria($info_bancaria);
                $errors = $relatorio->validarEmitir();
                if (!$errors) {
                    $relatorio->imprimir();
                } else {
                    foreach ($errors as $erro) {
                        $this->_flashMessage($erro);
                    }
                    $this->_redirect($this->_request->getControllerName() . "/movimento");
                }
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/movimento");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarvalorAction()
    {
        $id = $this->_request->getParam("id");
        $tb = new TbVinculoLoteItem();
        if ($id) {
            $registro = $tb->getPorId($id);
            if ($registro && $registro->getId()) {
                $lote = $registro->findParentRow("TbVinculoLote");
                $vinculo = $lote->findParentRow("TbVinculo");
                if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    $registro->setFromArray($dados);
                    $errors = $registro->getErrors();
                    if ($errors && count($errors)) {
                        $this->view->actionErrors = $errors;
                    } else {
                        $registro->save();
                        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                        $this->_redirect($this->_request->getControllerName() . "/loteitem/id_vinculo/{$vinculo->getId()}/id/{$lote->getId()}");
                    }
                }
                $this->view->registro = $registro;
                $this->view->lote = $lote;
                $this->view->vinculo = $vinculo;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > LOTE > ÍTEM > ALTERAR VALOR");
                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "loteitem",
                    "img" => "icon-reply",
                    "params" => array("id" => $lote->getId(), "id_vinculo" => $vinculo->getId())
                ));
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/vinculo/index/id_vinculo/0/id/0");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/vinculo/index/id_vinculo/0/id/0");
        }
    }

    public function bolsistaarquivoAction()
    {
        $id_bolsista = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_bolsista) && $session->id_bolsista) {
            $id_bolsista = $session->id_bolsista;
        } else {
            $id_bolsista = $this->getParam("id");
        }
        if ($id_bolsista) {
            $bolsista = TbBolsista::pegaPorId($id_bolsista);
            if ($bolsista) {
                $vinculo = $bolsista->findParentRow("TbVinculo");
                $session->id_bolsista = $bolsista->getId();
                $this->view->bolsista = $bolsista;
                $tb = new TbDocumentoRef();
                $this->view->registros = $tb->listar_por_pagina(array("tipo" => "B", "chave" => $bolsista->getId()));
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > BOLSISTA > ARQUIVOS");
                if ($this->view->registros && count($this->view->registros)) {
                    $button->addFromArray(array(
                        "titulo" => "Baixar",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "baixar",
                        "img" => "icon-download",
                        "params" => array("id_documento_ref" => 0)
                    ));
                }
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "bolsistaeditararquivo",
                    "img" => "icon-plus-sign",
                    "params" => array("id_documento_ref" => 0, "id_vinculo" => $vinculo->getId())
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "bolsista",
                    "img" => "icon-reply",
                    "params" => array("id_documento_ref" => 0, "id_vinculo" => $vinculo->getId())
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
    public function bolsistaeditararquivoAction()
    {
        $id_bolsista = 0;
        $session = Escola_Session::getInstance();
        if (isset($session->id_bolsista) && $session->id_bolsista) {
            $id_bolsista = $session->id_bolsista;
        }
        if ($id_bolsista) {
            $bolsista = TbBolsista::pegaPorId($id_bolsista);
            if ($bolsista) {
                $id = $this->_request->getParam("id_documento_ref");
                $registro = TbDocumentoRef::pegaPorId($id);
                if (!$registro) {
                    $tb = new TbDocumentoRef();
                    $registro = $tb->createRow();
                }
                $documento = $registro->findParentRow("TbDocumento");
                if (!$documento) {
                    $tb_doc = new TbDocumento();
                    $documento = $tb_doc->createRow();
                    $tb_doc_modo = new TbDocumentoModo();
                    $doc_modo = $tb_doc_modo->getPorChave("N");
                    if ($doc_modo) {
                        $documento->id_documento_modo = $doc_modo->getId();
                    }
                }
                $this->view->registro = $registro;
                $this->view->bolsista = $bolsista;
                if ($this->_request->isPost()) {
                    $dados = $this->_request->getPost();
                    $file = Escola_Util::getUploadedFile("arquivo");
                    $dados["arquivo"] = $file;
                    $db = Zend_Registry::get("db");
                    $db->beginTransaction();
                    try {
                        $documento->setFromArray($dados);
                        $errors = $documento->getErrors();
                        if (!$errors) {
                            $documento->save();
                            if ($documento->getId()) {
                                $documento->addBolsista($bolsista);
                                $db->commit();
                                $this->_redirect($this->_request->getControllerName() . "/bolsistaarquivo");
                            }
                        } else {
                            foreach ($errors as $erro) {
                                $this->addErro($erro);
                            }
                            $db->rollBack();
                        }
                    } catch (Exception $ex) {
                        $db->rollBack();
                        Zend_Debug::dump($dados);
                        Zend_Debug::dump($ex->getMessage());
                        die();
                    }
                }
                $this->view->documento = $documento;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > BOLSISTA > CADASTRO DE ARQUIVO");
                $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
                $button->addFromArray(array(
                    "titulo" => "Cancelar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "bolsistaarquivo",
                    "img" => "icon-remove-circle",
                    "params" => array("id" => $this->_getParam("id"))
                ));
            }
        }
    }
    public function bolsistaviewarquivoAction()
    {
        $session = Escola_Session::getInstance();
        $id_bolsista = 0;
        if (isset($session->id_bolsista)) {
            $id_bolsista = $session->id_bolsista;
        }
        $id = $this->getParam("id_documento_ref");
        if ($id_bolsista && $id) {
            $bolsista = TbBolsista::pegaPorId($id_bolsista);
            if ($bolsista) {
                $this->view->bolsista = $bolsista;
                $tb = new TbDocumentoRef();
                $registro = $tb->getPorId($id);
                if ($registro) {
                    $this->view->registro = $registro;
                    $this->view->documento = $registro->findParentRow("TbDocumento");
                    $this->view->arquivo = $this->view->documento->pega_arquivo();
                    $button = Escola_Button::getInstance();
                    $button->setTitulo("VINCULO > BOLSISTA > VISUALIZAÇÃO DE ARQUIVO");
                    $button->addFromArray(array(
                        "titulo" => "Voltar",
                        "controller" => $this->_request->getControllerName(),
                        "action" => "bolsistaarquivo",
                        "img" => "icon-reply",
                        "params" => array("id" => $this->_getParam("id"))
                    ));
                } else {
                    $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function bolsistaexcluirarquivoAction()
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
        $this->_redirect($this->_request->getControllerName() . "/bolsistaarquivo/id/" . $this->_getParam("id"));
    }

    public function baixarAction()
    {
        $session = Escola_Session::getInstance();
        $bolsista = TbBolsista::pegaPorId($session->id_bolsista);
        if ($bolsista) {
            $tb = new TbDocumentoRef();
            $erro = $tb->baixar(array("tipo" => "B", "chave" => $bolsista->getId(), "prefixo" => $bolsista->toString()));
            if ($erro) {
                $this->_flashMessage($erro);
                $this->_redirect($this->_request->getControllerName() . "/bolsista/bolsistaarquivo");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function aditivoAction()
    {
        $id_vinculo = $this->_request->getParam("id");
        $session = Escola_Session::getInstance();
        if (isset($session->id_vinculo) && $session->id_vinculo) {
            $id_vinculo = $session->id_vinculo;
        }
        if ($id_vinculo) {
            $vinculo = TbVinculo::pegaPorId($id_vinculo);
            if ($vinculo) {
                $session->id_vinculo = $vinculo->getId();
                $tb = new TbAditivo();
                $page = $this->_getParam("page");
                $dados = array("id_vinculo" => $vinculo->getId());
                $dados["pagina_atual"] = $page;
                $this->view->registros = $tb->listar_por_pagina($dados);
                $this->view->vinculo = $vinculo;
                $button = Escola_Button::getInstance();
                $button->setTitulo("VÍNCULO > TERMOS ADITIVOS");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editaraditivo",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0)
                ));
            } else {
                $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editaraditivoAction()
    {
        $session = Escola_Session::getInstance();
        $vinculo = TbVinculo::pegaPorId($session->id_vinculo);
        if ($vinculo) {
            $tb = new TbMoeda();
            $moedas = $tb->listar();
            if (!count($moedas)) {
                $this->_flashMessage("Nenhuma Moeda Cadastrada, Cadastre uma Moeda antes de continuar!");
                $this->_redirect($this->_request->getControllerName() . "/aditivo/id/0");
            }
            $id = $this->_request->getParam("id");
            $tb = new TbAditivo();
            if ($id) {
                $registro = $tb->getPorId($id);
            } else {
                $registro = $tb->createRow();
            }
            if ($this->_request->isPost()) {
                $dados = $this->_request->getPost();
                $dados["id_vinculo"] = $vinculo->getId();
                $registro->setFromArray($dados);
                $errors = $registro->getErrors();
                if ($errors && count($errors)) {
                    $this->view->actionErrors = $errors;
                } else {
                    $registro->save();
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/aditivo");
                }
            }
            $this->view->registro = $registro;
            $this->view->vinculo = $vinculo;
            $button = Escola_Button::getInstance();
            if ($this->view->registro->getId()) {
                $button->setTitulo("CADASTRO DE TERMO ADITIVO - ALTERAR");
            } else {
                $button->setTitulo("CADASTRO DE TERMO ADITIVO - INSERIR");
            }
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "aditivo",
                "img" => "icon-reply",
                "params" => array("id" => 0)
            ));
        } else {
            $this->_flashMessage("Falha ao Executar Operação, Dados Inválidos!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewaditivoAction()
    {
        $tb = new TbAditivo();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $this->view->vinculo = $registro->findParentRow("TbVinculo");
            $this->view->registro = $registro;
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR VÍNCULO > ADITIVO");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "aditivo",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $this->view->vinculo->getId(), "id" => 0)
            ));
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function excluiraditivoAction()
    {
        $vinculo = TbVinculo::pegaPorId($this->_request->getParam("id_vinculo"));
        $registro = TbAditivo::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $errors = $registro->getDeleteErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->_flashMessage($erro);
                }
            } else {
                $registro->delete();
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/aditivo");
    }

    public function excluirmovimentoAction()
    {
        $registro = TbVinculoMovimento::pegaPorId($this->_request->getParam("id"));
        if ($registro) {
            $errors = $registro->getDeleteErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->_flashMessage($erro);
                }
            } else {
                $registro->delete();
                $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/movimento");
    }

    public function pagaritemAction()
    {
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

            $tb = new TbFormaPagamento();
            $fps = $tb->listar();
            if (!$fps) {
                throw new Exception("Falha ao Executar Operação, Nenhuma Forma de Pagamento Cadastrada!!");
            }

            $tb = new TbDocComprovacao();
            $docs = $tb->listar();
            if (!$docs) {
                throw new Exception("Falha ao Executar Operação, Nenhuma Documento de Comprovação Cadastrado!!");
            }

            $this->view->lote = $lote;
            $this->view->tipo = $dt;
            $this->view->bolsa_tipo = $bt;

            $this->view->fps = $fps;
            $this->view->docs = $docs;

            $obj = $lote->pegaPagamento($dt->getId(), $bt->getId());
            if (!$obj) {
                $tb = new TbVinculoLoteOcorrenciaPgto();
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
                    $this->_redirect($this->_request->getControllerName() . "/pagar/id_vinculo/{$id_vinculo}/id/{$id_vinculo_lote}");
                } catch (Exception $ex) {
                    $this->view->actionErrors[] = $ex->getMessage();
                }
            }

            $this->view->obj = $obj;

            $button = Escola_Button::getInstance();
            $button->setTitulo("VINCULO > LOTE > PAGAMENTO");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "pagar",
                "img" => "icon-reply",
                "params" => array("id_vinculo" => $id_vinculo, "id" => $id_vinculo_lote)
            ));
        } catch (UnexpectedValueException $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/pagar/id_vinculo/{$id_vinculo}/id/{$id_vinculo_lote}");
        }
    }

    public function cancelarpagarAction()
    {
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

            $obj = TbVinculoLoteOcorrenciaPgto::pegaPorId($id);
            if (!$obj) {
                throw new Exception("Falha ao Executar Operação, Pagamento não Localizado!!");
            }

            $obj->delete();

            $this->_flashMessage("Operação Efetuada com Sucesso!", "Messages");
            $this->_redirect($this->_request->getControllerName() . "/pagar/id_vinculo/{$id_vinculo}/id/{$id_vinculo_lote}");
        } catch (UnexpectedValueException $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/pagar/id_vinculo/{$id_vinculo}/id/{$id_vinculo_lote}");
        }
    }
}
