<?php

class FuncionarioController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("listar", "json");
        $ajaxContext->addActionContext("listarporpagina", "json");
        $ajaxContext->initContext();
    }

    public function listarporpaginaAction()
    {
        $result = false;
        $tb = new TbFuncionario();
        $dados = $this->getRequest()->getPost();
        $dados["filtro_situacao"] = "A";
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
                $registro = TbFuncionario::pegaPorId($registro["id_funcionario"]);
                $pf = $registro->findParentRow("TbPessoaFisica");
                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->nome = $pf->nome;
                $obj->cpf = Escola_Util::formatCpf($pf->cpf);
                $obj->matricula = $registro->matricula;
                $obj->cargo = $registro->findParentRow("TbCargo")->toString();
                $obj->setor = $registro->pegaLotacaoPrincipal()->findParentRow("TbSetor")->toString();
                $items[] = $obj;
            }
            $this->view->items = $items;
        }
    }

    public function indexAction()
    {
        $tb = new TbFuncionario();
        $page = $this->_getParam("page");
        $dados = $this->_request->getParams();
        $session = Escola_Session::getInstance();
        $filtros = array("filtro_cargo", "filtro_setor", "filtro_matricula", "page", "filtro_cpf", "filtro_nome", "filtro_id_funcionario_situacao");
        $this->view->dados = $session->atualizaFiltros($filtros);
        $this->view->dados["pagina_atual"] = $page;
        $this->view->registros = $tb->listar_por_pagina($this->view->dados);
        $button = Escola_Button::getInstance();
        $button->setTitulo("FUNCIONÃRIOS");
        $button->addFromArray(array(
            "titulo" => "Adicionar",
            "onclick" => "add_usuario()",
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
            "titulo" => "Folha de Ponto",
            "onclick" => "folha_ponto()",
            "img" => "icon-time",
            "params" => array("id" => 0)
        ));
        $button->addFromArray(array(
            "titulo" => "Importar",
            "onclick" => "importar()",
            "img" => "icon-download-alt",
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
        $tb = new TbCargo();
        $cargos = $tb->listar();
        if (!count($cargos)) {
            $this->_flashMessage("NENHUM CARGO CADASTRADO!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
        $tb = new TbSetor();
        $setores = $tb->listar();
        if (!count($setores)) {
            $this->_flashMessage("NENHUM SETOR CADASTRADO!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
        $id = $this->_request->getParam("id");
        $tb = new TbFuncionario();
        if ($id) {
            $registro = $tb->getPorId($id);
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
                $registro->save();
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        }
        $this->view->registro = $registro;
        $this->view->pf = $registro->pega_pessoa_fisica();
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE GRUPO - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE GRUPO - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "disk.png");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "delete.png");
    }

    public function excluirAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbFuncionario();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $registro->delete();
                $this->_flashMessage("OPERAï¿½ï¿½O EFETUADA COM SUCESSO!", "Messages");
            } else {
                $this->_flashMessage("INFORMAï¿½ï¿½O RECEBIDA INVï¿½LIDA!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }

    public function viewAction()
    {
        $tb = new TbFuncionario();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $this->view->registro = $registro;
            $this->view->pf = $this->view->registro->pega_pessoa_fisica();
            $this->view->pessoa = $this->view->pf->pega_pessoa();
            $this->view->lotacao = $this->view->registro->pegaLotacaoPrincipal();
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR FUNCIONï¿½RIO");
            $button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function addfuncionarioAction()
    {
        $funcionario = false;
        $dados = $this->_request->getPost();
        if ($this->_request->getParam("id_funcionario")) {
            $funcionario = TbFuncionario::pegaPorId($this->_request->getParam("id_funcionario"));
        }
        if (isset($dados["cpf"]) && $dados["cpf"]) {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            $dados["jan_cpf"] = $dados["cpf"];
            if (isset($dados["id_funcionario"])) {
                $funcionario = TbFuncionario::pegaPorId($dados["id_funcionario"]);
            }
            if ($funcionario) {
                unset($dados["id_funcionario"]);
            } else {
                $tb = new TbFuncionario();
                $funcionario = $tb->createRow();
            }
            $funcionario->setFromArray($dados);
            $errors = $funcionario->getErrors();
            if (!$errors) {
                $id_funcionario = $funcionario->save();
                if ($id_funcionario) {
                    $lotacao = $funcionario->pegaLotacaoPrincipal();
                    if (!$lotacao) {
                        $tb = new TbLotacao();
                        $lotacao = $tb->createRow();
                    }
                    $dados["id_funcionario"] = $id_funcionario;
                    $lotacao->setFromArray($dados);
                    try {
                        $id_lotacao = $lotacao->save();
                    } catch (Exception $ex) {
                        die($ex->getMessage());
                    }
                    if ($id_lotacao) {
                        $db->commit();
                        $this->_flashMessage("OPERAï¿½ï¿½O EFETUADA COM SUCESSO!", "Messages");
                        $this->_redirect("funcionario/index");
                    } else {
                        $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, CHAME O ADMINISTRADOR!");
                    }
                } else {
                    $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, CHAME O ADMINISTRADOR!");
                }
            } else {
                $this->view->actionErrors = $errors;
            }
            $db->rollBack();
        }
        if ($funcionario || isset($dados["jan_cpf"])) {
            if (isset($dados["id_funcionario"])) {
                unset($dados["id_funcionario"]);
            }
            if ($funcionario) {
                $pf = $funcionario->pega_pessoa_fisica();
            } else {
                $tb = new TbPessoaFisica();
                $pf = $tb->getPorCPF($dados["jan_cpf"]);
                if (!$pf) {
                    $pf = $tb->createRow();
                    $pf->cpf = $dados["jan_cpf"];
                }
            }
            $pf->setFromArray($dados);
            if (!$funcionario) {
                $tb = new TbFuncionario();
                $funcionario = $tb->getPorPessoaFisica($pf);
                if (!$funcionario) {
                    $funcionario = $tb->createRow();
                }
            }
            $funcionario->setFromArray($dados);
            $lotacao = $funcionario->pegaLotacaoPrincipal();
            if (!$lotacao) {
                $tb = new TbLotacao();
                $lotacao = $tb->createRow();
            }
            $lotacao->setFromArray($dados);
            $this->view->pf = $pf;
            $this->view->funcionario = $funcionario;
            $this->view->lotacao = $lotacao;
            $button = Escola_Button::getInstance();
            if ($this->view->funcionario->getId()) {
                $button->setTitulo("CADASTRO DE FUNCIONï¿½RIO - ALTERAR");
            } else {
                $button->setTitulo("CADASTRO DE FUNCIONï¿½RIO - INSERIR");
            }
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function lotacaoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $funcionario = TbFuncionario::pegaPorId($id);
            if ($funcionario) {
                $this->view->funcionario = $funcionario;
                $this->view->registros = $funcionario->pegaLotacao();
                $button = Escola_Button::getInstance();
                $button->setTitulo("LOTAï¿½ï¿½O");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarlotacao",
                    "img" => "icon-plus-sign",
                    "params" => array("id_lotacao" => 0, "id" => $funcionario->getId())
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id_lotacao" => 0)
                ));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewlotacaoAction()
    {
        $registro = TbLotacao::pegaPorId($this->_request->getParam("id_lotacao"));
        if ($registro->getId()) {
            $this->view->registro = $registro;
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR LOTAï¿½ï¿½O");
            $button->addAction("Voltar", $this->_request->getControllerName(), "lotacao", "icon-reply", array("id" => $this->view->registro->id_funcionario));
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarlotacaoAction()
    {
        $id_lotacao = $this->_request->getParam("id_lotacao");
        $id = $this->_request->getParam("id");
        $registro = TbLotacao::pegaPorId($id_lotacao);
        $funcionario = TbFuncionario::pegaPorId($id);
        if (!$registro) {
            $tb = new TbLotacao();
            $registro = $tb->createRow();
        }
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $registro->setFromArray($dados);
            if (!$registro->id_lotacao) {
                $tb = new TbLotacaoTipo();
                $lt = $tb->getPorChave("P");
                if ($lt) {
                    $registro->id_lotacao_tipo = $lt->getId();
                }
            }
            $errors = $registro->getErrors();
            if ($errors) {
                $this->view->actionErrors = $errors;
            } else {
                $id_lotacao = $registro->save();
                if ($id_lotacao) {
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/lotacao/id/" . $funcionario->getId());
                } else {
                    $this->view->actionErrors = array("FALHA AO EXECUTAR OPERAï¿½ï¿½O, CHAME O ADMINISTRADOR!");
                }
            }
        }
        $this->view->registro = $registro;
        $this->view->funcionario = $funcionario;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE LOTAï¿½ï¿½O - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE LOTAï¿½ï¿½O - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "lotacao", "icon-remove-circle", array("id" => $this->view->funcionario->getId()));
    }

    public function excluirlotacaoAction()
    {
        $id = $this->_request->getParam("id_lotacao");
        if ($id) {
            $registro = TbLotacao::pegaPorId($id);
            if ($registro) {
                $funcionario = $registro->findParentRow("TbFuncionario");
                if (!$registro->findParentRow("TbLotacaoTipo")->normal()) {
                    $registro->delete();
                    $this->_flashMessage("OPERAï¿½ï¿½O EFETUADA COM SUCESSO!", "Messages");
                } else {
                    $this->_flashMessage("LOTAï¿½ï¿½O PRINCIPAL Nï¿½O PODE SER EXCLUï¿½DA!");
                }
            } else {
                $this->_flashMessage("INFORMAï¿½ï¿½O RECEBIDA INVï¿½LIDA!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/lotacao/id/" . $funcionario->getId());
    }

    public function mostrarAction()
    {
        $id = $this->_request->getParam("id");
        $funcionario = TbFuncionario::pegaPorId($id);
        if ($funcionario) {
            $this->view->funcionario = $funcionario;
            $this->view->pf = $funcionario->pega_pessoa_fisica();
            $this->view->lotacao = $funcionario->pegaLotacaoPrincipal();
            $button = Escola_Button::getInstance();
            $button->setTitulo("FUNCIONï¿½RIO");
            $button->addScript("Mensagem", "mensagem()", "comments.png");
            $button->addAction("Cancelar", "index", "index", "delete.png");
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
            $this->_redirect("index");
        }
    }

    public function listarAction()
    {
        $func = false;
        $id = $this->_request->getPost("id");
        if ($id) {
            $funcionario = TbFuncionario::pegaPorId($id);
            if ($funcionario) {
                $pf = $funcionario->pega_pessoa_fisica();
                $func = new stdClass;
                $func->cpf = Escola_Util::formatCpf($pf->cpf);
                $func->nome = $pf->nome;
                $func->email = $pf->pega_pessoa()->email;
                $func->matricula = $funcionario->matricula;
                $func->cargo = $funcionario->findParentRow("TbCargo")->toString();
                $func->lotacao = $funcionario->pegaLotacaoPrincipal()->findParentRow("TbSetor")->toString();
                $func->telefone = $pf->pega_pessoa()->mostrarTelefones();
                $func->foto = $pf->mostrarFoto(80, "right", true);
            }
        }
        $this->view->funcionario = $func;
    }

    public function searchAction()
    {
        $filtros = array("filtro_nome", "filtro_setor");
        $sessao = Escola_Session::getInstance();
        $dados = $sessao->atualizaFiltros($filtros);
        $tb = new TbFuncionarioSituacao();
        $fs = $tb->getPorChave("A");
        $dados["filtro_id_funcionario_situacao"] = $fs->getId();
        //$dados = $this->_request->getPost();
        $page = $this->_getParam("page");
        $dados["pagina_atual"] = $page;
        $tb = new TbFuncionario();
        $this->view->registros = $tb->listar_por_pagina($dados);
        $this->view->dados = $dados;
        $button = Escola_Button::getInstance();
        $button->setTitulo("BUSCA DE FUNCIONï¿½RIOS");
        $button->addFromArray(array(
            "titulo" => "Pesquisar",
            "onclick" => "pesquisar()",
            "img" => "icon-search",
            "params" => array("id" => 0)
        ));
        $button->addAction("Cancelar", "intranet", "index", "icon-remove-circle");
    }

    public function arquivoAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $funcionario = TbFuncionario::pegaPorId($id);
            if ($funcionario) {
                $this->view->funcionario = $funcionario;
                $this->view->registros = $funcionario->pegaDocumentoRef("P");
                $button = Escola_Button::getInstance();
                $button->setTitulo("ARQUIVOS");
                $button->addFromArray(array(
                    "titulo" => "Baixar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "baixar",
                    "img" => "icon-download",
                    "params" => array("id_documento_ref" => 0, "id" => $funcionario->getId())
                ));
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editararquivo",
                    "img" => "icon-plus-sign",
                    "params" => array("id_documento_ref" => 0, "id" => $funcionario->getId())
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id_documento_ref" => 0, "id" => $funcionario->getId())
                ));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewarquivoAction()
    {
        $id = $this->_request->getParam("id");
        $this->view->funcionario = TbFuncionario::pegaPorId($id);
        $id = $this->_request->getParam("id_documento_ref");
        if ($id) {
            $tb = new TbDocumentoRef();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->documento = $registro->findParentRow("TbDocumento");
                $this->view->arquivo = $this->view->documento->pega_arquivo();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAï¿½ï¿½O DE ARQUIVO DE FUNCIONï¿½RIO");
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "arquivo",
                    "img" => "icon-reply",
                    "params" => array("id" => $this->_getParam("id"))
                ));
            } else {
                $this->_flashMessage("INFORMAï¿½ï¿½O RECEBIDA INVï¿½LIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
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
                $this->_flashMessage("OPERAï¿½ï¿½O EFETUADA COM SUCESSO!", "Messages");
            } else {
                $this->_flashMessage("INFORMAï¿½ï¿½O RECEBIDA INVï¿½LIDA!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/arquivo/id/" . $this->_getParam("id"));
    }

    public function editararquivoAction()
    {
        $id = $this->_request->getParam("id");
        $funcionario = TbFuncionario::pegaPorId($id);
        $id = $this->_request->getParam("id_documento_ref");
        $registro = TbDocumentoRef::pegaPorId($id);
        if (!$registro) {
            $tb = new TbDocumentoRef();
            $registro = $tb->createRow();
        }
        $this->view->registro = $registro;
        $documento = $registro->findParentRow("TbDocumento");
        $this->view->documento = false;
        $this->view->arquivo = false;
        $this->view->dados = array();
        $this->view->operacao = "";
        if ($documento) {
            $this->view->documento = $documento;
            $this->view->arquivo = $documento->pega_arquivo();
            $this->view->dados["id_documento_tipo_target"] = $documento->findParentRow("TbDocumentoTipo")->findParentRow("TbDocumentoTipoTarget")->getId();
            if ($documento->eAdministrativo()) {
                $this->view->operacao = "set_documento";
            }
        }
        $this->view->funcionario = $funcionario;
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $this->view->dados = $dados;
            if (isset($dados["id_documento"]) && $dados["id_documento"]) {
                $documento = TbDocumento::pegaPorId($dados["id_documento"]);
            }
            if (isset($dados["operacao"]) && ($dados["operacao"] == "set_documento")) {
                $documento->addFuncionario($funcionario);
                $this->_flashMessage("OPERAï¿½ï¿½O EFETUADA COM SUCESSO!", "Messages");
                $this->_redirect($this->_request->getControllerName() . "/arquivo/id/" . $this->view->funcionario->getId());
            } else {
                if (!$documento) {
                    $tb = new TbDocumento();
                    $documento = $tb->createRow();
                    $tb = new TbDocumentoModo();
                    $dm = $tb->getPorChave("N");
                    if ($dm) {
                        $dados["id_documento_modo"] = $dm->getId();
                    }
                }
                $arquivo = Escola_Util::getUploadedFile("arquivo");
                if ($arquivo && $arquivo["size"]) {
                    $dados["arquivo"] = $arquivo;
                }
                $documento->setFromArray($dados);
                $errors = $documento->getErrors();
                if ($errors) {
                    $this->view->actionErrors = $errors;
                } else {
                    $id = $documento->save();
                    if ($id) {
                        $documento->addFuncionario($funcionario);
                    }
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/arquivo/id/" . $this->view->funcionario->getId());
                }
            }
        }
        $this->view->documento = $documento;
        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE ARQUIVO DE FUNCIONï¿½RIO");
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Cancelar",
            "controller" => $this->_request->getControllerName(),
            "action" => "arquivo",
            "img" => "icon-remove-circle",
            "params" => array("id" => $this->_getParam("id"))
        ));
    }

    public function importarAction()
    {
        $arquivo = Escola_Util::getUploadedFile("arquivo");
        if ($arquivo && $arquivo["size"]) {
            $tb = new TbFuncionario();
            $tb->importar_arquivo($arquivo["tmp_name"], $this);
        } else {
            $session = Escola_Session::getInstance();
            $filtros = array("filtro_cargo", "filtro_setor", "filtro_matricula", "page", "filtro_cpf", "filtro_nome", "filtro_id_funcionario_situacao");
            $dados = $session->atualizaFiltros($filtros);
            $sql = "select a.*, 
					b.nome as desc_naturalidade, 
					c.nome as desc_funcao,
					d.nome as setor_sigla,
					e.nome as setor_descricao,
					f.nome as desc_vinculo,
					g.nome as desc_uf
					from funcionarios a, naturalidade b, funcao c, setores d, departamentos e, vinculo f, uf g
					where (a.naturalidade = b.naturalidade) 
					and (a.funcao = c.funcao)
					and (a.setores = d.setores)
					and (d.departamentos = e.departamentos)
					and (a.vinculo = f.vinculo)
					and (a.uf = g.uf)";
            if (isset($dados["filtro_matricula"]) && $dados["filtro_matricula"]) {
                $sql .= " and (a.funcionarios = {$dados["filtro_matricula"]}) ";
            }
            $objs = $funcionarios = Escola_Util::consulta_ibase($sql);
            $tb = new TbFuncionario();
            $linhas = array();
            $linhas[] = "cpf;nome;e_mail;data_nascimento;municipio_nascimento;identidade_numero;identidade_orgao_expedidor;identidade_uf;pis_pasep;matricula;cargo;data_ingresso;setor_sigla;setor_descricao;vinculo;nascimento_uf_descricao;";
            foreach ($objs as $obj) {
                $fun = $tb->getPorMatricula($obj->FUNCIONARIOS);
                if (!$fun) {
                    $linha = array();
                    $linha["cpf"] = $obj->CPF;
                    $linha["nome"] = $obj->NOME;
                    $linha["e_mail"] = $obj->EMAIL;
                    $linha["data_nascimento"] = Escola_Util::formatData($obj->DATANASCIMENTO);
                    $linha["municipio_nascimento"] = $obj->DESC_NATURALIDADE;
                    $linha["identidade_numero"] = $obj->NUMERORG;
                    $linha["identidade_orgao_expedidor"] = $obj->ORGAOEMISSOR;
                    $linha["identidade_uf"] = $obj->UF;
                    $linha["pis_pasep"] = $obj->PIS;
                    $linha["matricula"] = $obj->FUNCIONARIOS;
                    $linha["cargo"] = $obj->DESC_FUNCAO;
                    $linha["data_ingresso"] = Escola_Util::formatData($obj->DATAADMISSAO);
                    $linha["cargo"] = $obj->DESC_FUNCAO;
                    $linha["setor_sigla"] = $obj->SETOR_SIGLA;
                    $linha["setor_descricao"] = $obj->SETOR_DESCRICAO;
                    $linha["vinculo"] = $obj->DESC_VINCULO;
                    $linha["nascimento_uf_descricao"] = $obj->DESC_UF;
                    $linhas[] = implode(";", $linha);
                }
            }
            $filename = ROOT_DIR . "/application/file/tmp_import_file.csv";
            $f = fopen($filename, "w+");
            fwrite($f, implode(PHP_EOL, $linhas));
            fclose($f);
            $tb->importar_arquivo($filename);
            unlink($filename);
        }
        $this->_redirect("funcionario/index");
    }

    public function ocorrenciaAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $funcionario = TbFuncionario::pegaPorId($id);
            if ($funcionario) {
                $this->view->funcionario = $funcionario;
                $this->view->registros = $funcionario->pegaOcorrencia();
                $button = Escola_Button::getInstance();
                $button->setTitulo("OCORRï¿½NCIAS");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarocorrencia",
                    "img" => "icon-plus-sign",
                    "params" => array("id_funcionario_ocorrencia" => 0, "id" => $funcionario->getId())
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id_funcionario_ocorrencia" => 0)
                ));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editarocorrenciaAction()
    {
        $id_ocorrencia = $this->_request->getParam("id_funcionario_ocorrencia");
        $id = $this->_request->getParam("id");
        $registro = TbFuncionarioOcorrencia::pegaPorId($id_ocorrencia);
        $funcionario = TbFuncionario::pegaPorId($id);
        if (!$registro) {
            $tb = new TbFuncionarioOcorrencia();
            $registro = $tb->createRow();
        }
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $registro->setFromArray($dados);
            $errors = $registro->getErrors();
            if ($errors) {
                $this->view->actionErrors = $errors;
            } else {
                $id = $registro->save();
                if ($id) {
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/ocorrencia/id/" . $funcionario->getId());
                } else {
                    $this->view->actionErrors = array("FALHA AO EXECUTAR OPERAï¿½ï¿½O, CHAME O ADMINISTRADOR!");
                }
            }
        }
        $this->view->registro = $registro;
        $this->view->funcionario = $funcionario;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE OCORRï¿½NCIA - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE OCORRï¿½NCIA - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "ocorrencia", "icon-remove-circle", array("id" => $funcionario->getId()));
    }

    public function viewocorrenciaAction()
    {
        $registro = TbFuncionarioOcorrencia::pegaPorId($this->_request->getParam("id_funcionario_ocorrencia"));
        if ($registro) {
            $this->view->registro = $registro;
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR OCORRï¿½NCIA");
            $button->addAction("Voltar", $this->_request->getControllerName(), "ocorrencia", "icon-reply", array("id" => $registro->id_funcionario));
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function excluirocorrenciaAction()
    {
        $id = $this->_request->getParam("id_funcionario_ocorrencia");
        if ($id) {
            $registro = TbFuncionarioOcorrencia::pegaPorId($id);
            if ($registro) {
                $registro->delete();
                $this->_flashMessage("OPERAï¿½ï¿½O EFETUADA COM SUCESSO!", "Messages");
            } else {
                $this->_flashMessage("INFORMAï¿½ï¿½O RECEBIDA INVï¿½LIDA!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAï¿½ï¿½O RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/ocorrencia/id/" . $this->_request->getParam("id"));
    }

    public function imprimirAction()
    {
        $tb = new TbFuncionario();
        $id_funcionarios = array();
        $dados = $this->_request->getParams();
        if (isset($dados["ponto_id_funcionario"]) && $dados["ponto_id_funcionario"]) {
            $f = $tb->pegaPorId($dados["ponto_id_funcionario"]);
            if ($f->ativo()) {
                $id_funcionarios[] = $f;
            }
        } else {
            $session = Escola_Session::getInstance();
            $filtros = array("filtro_cargo", "filtro_setor", "filtro_matricula", "page", "filtro_cpf", "filtro_nome", "filtro_id_funcionario_situacao");
            $filtro_dados = $session->atualizaFiltros($filtros);
            $funcionarios = $tb->listar($filtro_dados);
            if ($funcionarios) {
                foreach ($funcionarios as $funcionario) {
                    if ($funcionario->ativo()) {
                        $id_funcionarios[] = $funcionario;
                    }
                }
            }
        }
        if (count($id_funcionarios)) {
            if (isset($dados["ano_mes"]) && $dados["ano_mes"]) {
                $relatorio = new Escola_Relatorio_Ponto($dados["ano_mes"]);
                $relatorio->set_funcionarios($id_funcionarios);
                $relatorio->imprimir();
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS1!");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS2!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }

    public function baixarAction()
    {
        $funcionario = TbFuncionario::pegaPorId($this->_request->getParam("id"));
        if ($funcionario) {
            $pf = $funcionario->pega_pessoa_fisica();
            $registros = $funcionario->pegaDocumentoRef("P");
            if ($registros && count($registros)) {
                $path_tmp = ROOT_DIR . "/application/file/tmp/{$funcionario->getId()}/";
                if (!file_exists($path_tmp)) {
                    $flag = mkdir($path_tmp);
                    if (!$flag) {
                        $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, IMPOSSï¿½VEL CRIAR PASTA TEMPORï¿½RIA!");
                        $this->_redirect($this->_request->getControllerName() . "/arquivo/id/{$funcionario->getId()}");
                    }
                }
                $files = glob($path_tmp . "*.*");
                if ($files && is_array($files) && count($files)) {
                    foreach ($files as $file) {
                        unlink($file);
                    }
                }
                $arquivos = array();
                foreach ($registros as $registro) {
                    $doc = $registro->findParentRow("TbDocumento");
                    if ($doc) {
                        $arquivo = $doc->pega_arquivo();
                        if ($arquivo && $arquivo->existe()) {
                            $at = $arquivo->findParentRow("TbArquivoTipo");
                            if ($at) {
                                $nome_completo = $arquivo->pegaNomeCompleto();
                                $filter = new Zend_Filter_CharConverter();
                                $filename = str_replace(" ", "_", $filter->filter(Escola_Util::minuscula($pf->nome . "__" . $doc->resumo)));
                                $filename_new = $path_tmp . $filename . "." . $at->extensao;
                                $flag = copy($nome_completo, $filename_new);
                                if ($flag) {
                                    $arquivos[] = $filename_new;
                                }
                            }
                        }
                    }
                }
                $zip = new Zend_Filter_Compress_Zip();
                $filename = str_replace(" ", "_", $filter->filter(Escola_Util::minuscula($pf->nome)));
                $zip->setArchive($path_tmp . "{$filename}.zip");
                //$zip->setTarget(ROOT_DIR . PATH_SEPARATOR . "application" . PATH_SEPARATOR . "file" . PATH_SEPARATOR);
                $arquivoZipado = $zip->compress($path_tmp);
                if ($arquivoZipado && file_exists($arquivoZipado)) {
                    header("Content-Type: " . mime_content_type($arquivoZipado));
                    header("Content-Disposition: attachment; filename={$filename}.zip");
                    $f = fopen($arquivoZipado, "r");
                    $buffer = fread($f, filesize($arquivoZipado));
                    fclose($f);
                    echo $buffer;
                    die();
                }
            } else {
                $this->_flashMessage("NENHUM ARQUIVO DISPONï¿½VEL PARA IMPORTAï¿½ï¿½O!");
                $this->_redirect($this->_request->getControllerName() . "/arquivo/id/{$funcionario->getId()}");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAï¿½ï¿½O, DADOS INVï¿½LIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
}
