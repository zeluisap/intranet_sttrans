<?php

class PessoaController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("listarporpagina", "json");
        $ajaxContext->addActionContext("pjlistarporpagina", "json");
        $ajaxContext->addActionContext("salvar", "json");
        $ajaxContext->addActionContext("pjsalvar", "json");
        $ajaxContext->addActionContext("dados", "json");
        $ajaxContext->addActionContext("pessoalistarporpagina", "json");
        $ajaxContext->addActionContext("webcam", "json");
        $ajaxContext->initContext();
    }

    public function dadosAction()
    {
        try {
            $obj = new stdClass();
            $this->view->erro = "";
            $this->view->obj = false;
            $dados = $this->getRequest()->getPost();
            $tb = new TbPessoaFisica();

            $cpf_cnpj = $id_pf = false;

            if (isset($dados["cpf"]) && $dados["cpf"] && Escola_Util::limparNumero($dados["cpf"])) {
                $cpf_cnpj = Escola_Util::limparNumero($dados["cpf"]);
            }

            if (isset($dados["id_pessoa_fisica"]) && $dados["id_pessoa_fisica"]) {
                $id_pf = $dados["id_pessoa_fisica"];
            }

            if (!$id_pf && !$cpf_cnpj) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Nenhuma Pessoa Fï¿½sica Localizada!");
            }
            if ($id_pf) {
                $pf = $tb->getPorId($id_pf);
            } elseif ($cpf_cnpj) {
                $pf = $tb->getPorCPF($cpf_cnpj);
            }

            if (!$pf) {
                $pf = $tb->createRow();
            }

            $this->view->obj = $pf->toObjeto();
        } catch (Exception $ex) {
            $this->view->erro = $ex->getMessage();
        }
    }

    public function listarporpaginaAction()
    {
        $superior = false;
        $dados = $this->getRequest()->getPost();
        $tb = new TbPessoaFisica();
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
                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->cpf = Escola_Util::formatCPF($registro->cpf);
                $obj->nome = $registro->nome;
                $items[] = $obj;
            }
            $this->view->items = $items;
        }
    }

    public function salvarAction()
    {
        try {
            $this->view->id = false;
            $this->view->erro = "";
            $this->view->descricao = "";
            $dados = $this->getRequest()->getPost();
            $tb = new TbPessoaFisica();
            $cpf = $id_pf = $pessoa = false;

            if (isset($dados["id_pessoa_fisica"]) && $dados["id_pessoa_fisica"]) {
                $id_pf = $dados["id_pessoa_fisica"];
                unset($dados["id_pessoa_fisica"]);
            }

            if (isset($dados["cpf"])) {
                if ($dados["cpf"]) {
                    $cpf = $dados["cpf"];
                }
                unset($dados["cpf"]);
            }

            if ($id_pf) {
                $pessoa = $tb->getPorId($id_pf);
            } elseif ($cpf) {
                $pessoa = $tb->getPorCPF($cpf);
            }

            if (!$pessoa) {
                $pessoa = $tb->createRow();
                if ($cpf) {
                    $pessoa->cpf = $cpf;
                }
            }

            // $dados = array_map("utf8_decode", $dados);
            $pessoa->setFromArray($dados);
            if (isset($dados["flag_errors"])) {
                $flag_errors = (bool) $dados["flag_errors"];
            } else {
                $flag_errors = false;
            }
            $errors = $pessoa->getErrors($flag_errors);
            if ($errors) {
                throw new Exception(implode("<br>", $errors));
            }

            $id = $pessoa->save();

            if (!$id) {
                throw new Exception("Falha ao Executar OPERAÇÃO, Dados INVÁLIDOs!");
            }

            $p = $pessoa->pega_pessoa();
            $flag = array("C" => "telefone_celular", "F" => "telefone_fixo");
            foreach ($flag as $chave => $valor) {
                if (!(isset($dados[$valor]) && Escola_Util::limparNumero($dados[$valor]))) {
                    continue;
                }

                $fones = $p->getTelefones($chave);
                if ($fones) {
                    $telefone = $fones[0];
                } else {
                    $tb = new TbTelefoneTipo();
                    $tt = $tb->getPorChave($chave);
                    $tb = new TbTelefone();
                    $telefone = $tb->createRow();
                    $telefone->id_telefone_tipo = $tt->getId();
                }

                $fone_part = explode(")", $dados[$valor]);
                if (count($fone_part) == 2) {
                    $telefone->ddd = Escola_Util::limparNumero($fone_part[0]);
                    $telefone->numero = Escola_Util::limparNumero($fone_part[1]);
                }
                $errors = $telefone->getErrors();

                if ($errors) {
                    continue;
                }

                $telefone->save();
                $p->addTelefone($telefone);

            }
            $this->view->id = $id;
            $this->view->descricao = $pessoa->toString();

            $this->view->pessoa = $pessoa->toObjeto();

        } catch (Exception $ex) {
            $this->view->erro = $ex->getMessage();
        }
    }

    public function pjlistarporpaginaAction()
    {
        $superior = false;
        $dados = $this->getRequest()->getPost();
        $tb = new TbPessoaJuridica();
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
                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->cnpj = Escola_Util::formatCNPJ($registro->cnpj);
                $obj->sigla = $registro->sigla;
                $obj->razao_social = $registro->razao_social;
                $obj->nome_fantasia = $registro->nome_fantasia;
                $items[] = $obj;
            }
            $this->view->items = $items;
        }
    }

    public function pjsalvarAction()
    {
        $this->view->id = false;
        $this->view->erro = "";
        $this->view->descricao = "";
        $dados = $this->getRequest()->getPost();
        $tb = new TbPessoaJuridica();
        if (isset($dados["cnpj"]) && $dados["cnpj"]) {
            $pessoa = $tb->getPorCNPJ($dados["cnpj"]);
            if (!$pessoa) {
                $pessoa = $tb->createRow();
                $dados = array_map("utf8_decode", $dados);
                $pessoa->setFromArray($dados);
                $errors = $pessoa->getErrors(false);
                if ($errors) {
                    $this->view->erro = implode("<br>", $errors);
                } else {
                    try {
                        $id = $pessoa->save();
                    } catch (Exception $e) {
                        die($e->getMessage());
                    }
                    if ($id) {
                        $this->view->id = $id;
                        $this->view->cnpj = Escola_Util::formatCnpj($pessoa->cnpj);
                        $this->view->sigla = $pessoa->sigla;
                        $this->view->nome_fantasia = $pessoa->nome_fantasia;
                        $this->view->descricao = Escola_Util::formatCnpj($pessoa->cnpj) . " - " . $pessoa->nome_fantasia;
                    } else {
                        $this->view->erro = "Falha ao Executar OPERAÇÃO, Chame o Administrador!";
                    }
                }
            } else {
                $this->view->erro = "Pessoa Já Cadastrada!";
            }
        } else {
            $this->view->erro = "Campo C.N.P.J. Obrigatório!";
        }
    }

    public function pessoalistarporpaginaAction()
    {
        $dados = $this->getRequest()->getPost();
        $tb = new TbPessoa();
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
                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->id_pessoa_tipo = $registro->id_pessoa_tipo;
                $obj->tipo = $registro->findParentRow("TbPessoaTipo")->toString();
                $obj->documento = $registro->mostrar_documento();
                $obj->nome = $registro->mostrar_nome();
                $items[] = $obj;
            }
            $this->view->items = $items;
        }
    }

    public function indexAction()
    {
        $session = Escola_Session::getInstance();
        if (isset($session->id_pessoa)) {
            unset($session->id_pessoa);
        }
        if (isset($session->id_pessoa_tipo)) {
            unset($session->id_pessoa_tipo);
        }

        $tb = new TbPessoa();
        $dados = $session->atualizaFiltros(array("filtro_id_pessoa_tipo", "filtro_nome", "filtro_cpf", "filtro_cnpj"));
        $dados["pagina_atual"] = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina($dados);
        $this->view->dados = $dados;
        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE PESSOAS");
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
            "controller" => "intranet",
            "action" => "index",
            "img" => "icon-reply",
            "params" => array("id" => 0)
        ));
    }

    public function editarAction()
    {
        $session = Escola_Session::getInstance();
        $id = $this->_request->getParam("id");
        $tb = new TbPessoa();
        if ($id) {
            $registro = $tb->getPorId($id);
        } else {
            $registro = $tb->createRow();
        }
        $filho = $registro->pegaPessoaFilho();
        $pt = $registro->findParentRow("TbPessoaTipo");
        if (!$filho) {
            if (!$pt) {
                if ($this->_request->getParam("jan_id_pessoa_tipo")) {
                    $pt = TbPessoaTipo::pegaPorId($this->_request->getParam("jan_id_pessoa_tipo"));
                } elseif ($this->_request->getParam("id_pessoa_tipo")) {
                    $pt = TbPessoaTipo::pegaPorId($this->_request->getParam("id_pessoa_tipo"));
                } elseif ($session->id_pessoa_tipo) {
                    $pt = TbPessoaTipo::pegaPorId($session->id_pessoa_tipo);
                }
            }
            if ($pt) {
                $session->id_pessoa_tipo = $pt->getId();
                if ($pt->pf()) {
                    $tb = new TbPessoaFisica();
                } elseif ($pt->pj()) {
                    $tb = new TbPessoaJuridica();
                }
            }
            $filho = $tb->createRow();
        }
        $dados = $dados_telefone_fixo = $dados_telefone_celular = array();
        $pessoa_motorista = false;

        $pessoa = $filho->pega_pessoa();

        $tb_telefone = new TbTelefone();
        $tb_telefone_tipo = new TbTelefoneTipo();

        $fixo = $tb_telefone->createRow();
        $telefone_tipo = $tb_telefone_tipo->getPorChave("F");
        if ($telefone_tipo) {
            $fixo->id_telefone_tipo = $telefone_tipo->getId();
        }
        if ($pessoa->getId()) {
            //definindo telefone fixo
            $rs_fixos = $tb_telefone->listar(array("id_pessoa" => $pessoa->getId(), "telefone_tipo" => "F"));
            if ($rs_fixos && count($rs_fixos)) {
                $fixo = $rs_fixos->current();
            }
        }

        //definindo telefone celular
        $celular = $tb_telefone->createRow();
        $telefone_tipo = $tb_telefone_tipo->getPorChave("C");
        if ($telefone_tipo) {
            $celular->id_telefone_tipo = $telefone_tipo->getId();
        }
        if ($pessoa->getId()) {
            $rs_celular = $tb_telefone->listar(array("id_pessoa" => $pessoa->getId(), "telefone_tipo" => "C"));
            if ($rs_celular && count($rs_celular)) {
                $celular = $rs_celular->current();
            }
        }

        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $filho->setFromArray($dados);

            if ($pt->pf()) {
                $pessoa_motorista = $filho->pegaPessoaMotorista();
                if (!$pessoa_motorista) {
                    $tb = new TbPessoaMotorista();
                    $pessoa_motorista = $tb->createRow();
                    $pessoa_motorista->id_pessoa_fisica = $filho->getId();
                }
                $pessoa_motorista->setFromArray($dados);
            }
            //montando array de dados de telefone - fixo
            if (isset($dados["telefone_fixo"])) {
                $telefone_fixo_partes = explode(")", $dados["telefone_fixo"]);
                if (count($telefone_fixo_partes) == 2) {
                    foreach ($telefone_fixo_partes as $key => $value) {
                        $telefone_fixo_partes[$key] = Escola_Util::limpaNumero($value);
                    }
                    $dados_telefone_fixo = array("ddd" => $telefone_fixo_partes[0], "numero" => $telefone_fixo_partes[1]);
                }
            }
            $fixo->setFromArray($dados_telefone_fixo);
            //montando array de dados de telefone - celular
            if (isset($dados["telefone_celular"])) {
                $telefone_fixo_partes = explode(")", $dados["telefone_celular"]);
                if (count($telefone_fixo_partes) == 2) {
                    foreach ($telefone_fixo_partes as $key => $value) {
                        $telefone_fixo_partes[$key] = Escola_Util::limpaNumero($value);
                    }
                    $dados_telefone_celular = array("ddd" => $telefone_fixo_partes[0], "numero" => $telefone_fixo_partes[1]);
                }
            }
            $celular->setFromArray($dados_telefone_celular);

            if (isset($dados["flag"]) && ($dados["flag"] == "salvar")) {
                $errors = $filho->getErrors(false);
                if ($errors) {
                    $this->view->actionErrors = $errors;
                    $registro = $filho->pega_pessoa();
                } else {
                    $this->_flashMessage("REGISTRO DE PESSOA SALVO COM SUCESSO!", "Messages");
                    $filho->save();
                    $pessoa = $filho->getPessoa();
                    $pt = $pessoa->findParentRow("TbPessoaTipo");
                    if ($pt && $filho->getId()) {
                        $fone_erros = $fixo->getErrors();
                        if (!$fone_erros) {
                            $fixo->save();
                            $pessoa->addTelefone($fixo);
                        }
                        $fone_erros = $celular->getErrors();
                        if (!$fone_erros) {
                            $celular->save();
                            $pessoa->addTelefone($celular);
                        }
                        if ($pt->pf()) {
                            if ((!isset($dados["possui_cnh"]) || ($dados["possui_cnh"] == "N")) && $pessoa_motorista->getId()) {
                                $pessoa_motorista->delete();
                            } elseif (isset($dados["possui_cnh"]) && ($dados["possui_cnh"] == "S")) {
                                if (isset($dados["pessoa_motorista"]) && is_array($dados["pessoa_motorista"]) && count($dados["pessoa_motorista"])) {
                                    $pessoa_motorista->setFromArray($dados["pessoa_motorista"]);
                                    $pessoa_motorista->id_pessoa_fisica = $filho->getId();
                                    $errors = $pessoa_motorista->getErrors();
                                    if (!$errors) {
                                        $pessoa_motorista->save();
                                    } else {
                                        $this->addErro("ERRO AO CADASTRAR CARTEIRA DE MOTORISTA");
                                        foreach ($errors as $erro) {
                                            $this->addErro($erro);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
            } else {
                $errors = array();
                if (!isset($dados["jan_id_pessoa_tipo"]) || !$dados["jan_id_pessoa_tipo"]) {
                    $errors[] = "CAMPO TIPO DE PESSOA OBRIGATï¿½RIO!";
                }
                $pt = TbPessoaTipo::pegaPorId($dados["jan_id_pessoa_tipo"]);
                if ($pt->pf()) {
                    if (!isset($dados["jan_cpf"]) || !$dados["jan_cpf"]) {
                        $errors[] = "CAMPO C.P.F. OBRIGATï¿½RIO!";
                    } elseif (!Escola_Util::validaCPF($dados["jan_cpf"])) {
                        $errors[] = "CAMPO C.P.F. INVÁLIDO!";
                    }
                } elseif ($pt->pj()) {
                    if (!isset($dados["jan_cnpj"]) || !$dados["jan_cnpj"]) {
                        $errors[] = "CAMPO C.N.P.J. OBRIGATï¿½RIO!";
                    }
                }
                if (count($errors)) {
                    foreach ($errors as $erro) {
                        $this->_flashMessage($erro);
                    }
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
                if ($pt->pf()) {
                    if (isset($dados["jan_cpf"])) {
                        $dados["cpf"] = $dados["jan_cpf"];
                        $session->cpf = $dados["cpf"];
                    } elseif ($session->cpf) {
                        $dados["cpf"] = $session->cpf;
                    }
                } elseif ($pt->pj()) {
                    if (isset($dados["jan_cnpj"])) {
                        $dados["cnpj"] = $dados["jan_cnpj"];
                        $session->cnpj = $dados["cnpj"];
                    } elseif ($session->cnpj) {
                        $dados["cnpj"] = $session->cnpj;
                    }
                }
                $registro->id_pessoa_tipo = $dados["jan_id_pessoa_tipo"];
                $session->id_pessoa_tipo = $registro->id_pessoa_tipo;
            }
        } else {
            if ($pt->pf() && $session->cpf) {
                $dados["cpf"] = $session->cpf;
            } elseif ($pt->pj() && $session->cnpj) {
                $dados["cnpj"] = $session->cnpj;
            }
        }
        $filho->setFromArray($dados);
        if (!$registro->getId()) {
            $registro = $filho->getPessoa();
        }

        if ($pt->pf() && (!$pessoa_motorista || !$pessoa_motorista->getId())) {
            $pessoa_motorista = $filho->pegaPessoaMotorista();
            if (!$pessoa_motorista) {
                $tb = new TbPessoaMotorista();
                $pessoa_motorista = $tb->createRow();
                $pessoa_motorista->id_pessoa_fisica = $filho->getId();
            }
            $pessoa_motorista->setFromArray($dados);
        }
        $this->view->registro = $registro;
        $this->view->filho = $filho;
        $this->view->endereco = $registro->getEndereco();
        $this->view->pessoa_tipo = $registro->findParentRow("TbPessoaTipo");
        $this->view->pessoa_motorista = $pessoa_motorista;
        $this->view->possui_cnh = false;

        $this->view->fixo = $fixo;
        $this->view->celular = $celular;

        if ($pessoa_motorista && $this->view->pessoa_motorista->getId()) {
            $this->view->possui_cnh = true;
        } elseif ($this->_request->getPost("possui_cnh") == "S") {
            $this->view->possui_cnh = true;
        }
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("CADASTRO DE PESSOAS - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE PESSOAS - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbPessoa();
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
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVï¿½LIDA!");
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
            $tb = new TbPessoa();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $tb = new TbTransportePessoa();
                $this->view->registro = $registro;
                $this->view->transporte_pessoa = $tb->listar(array("id_pessoa" => $registro->getId()));
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR SERVIï¿½O");
                $button->addFromArray(array(
                    "titulo" => "Alterar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editar",
                    "img" => "icon-cog",
                    "params" => array("id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Excluir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "excluir",
                    "img" => "icon-trash",
                    "class" => "link_excluir",
                    "params" => array("id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Telefones",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "telefone",
                    "img" => "icon-phone",
                    "params" => array("id_pessoa" => $id, "id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Informaï¿½ï¿½es Bancï¿½rias",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "ib",
                    "img" => "icon-money",
                    "params" => array("id_pessoa" => $id, "id" => 0)
                ));
                $button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVï¿½LIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function telefoneAction()
    {
        $session = Escola_Session::getInstance();
        $id_pessoa = false;
        if (isset($session->id_pessoa) && $session->id_pessoa) {
            $id_pessoa = $session->id_pessoa;
        } elseif ($this->_request->getParam("id_pessoa")) {
            $id_pessoa = $this->_request->getParam("id_pessoa");
        }
        if ($id_pessoa) {
            $session->id_pessoa = $id_pessoa;
            $pessoa = TbPessoa::pegaPorId($id_pessoa);
            if ($pessoa) {
                $tb = new TbPessoaRef();
                $dados = array();
                $dados["pagina_atual"] = $this->_getParam("page");
                $dados["id_pessoa"] = $pessoa->getId();
                $dados["tipo"] = "T";
                $this->view->registros = $tb->listar_por_pagina($dados);
                $this->view->pessoa = $pessoa;
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE PESSOA > TELEFONE");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editartelefone",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0, "id_pessoa" => $pessoa->getId())
                ));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA PESSOA INFORMADA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA PESSOA INFORMADA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewtelefoneAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbPessoaRef();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->telefone = $registro->getObjeto();
                $this->view->pessoa = $registro->findParentRow("TbPessoa");
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR PESSOA > TELEFONE");
                $button->addFromArray(array(
                    "titulo" => "Alterar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editartelefone",
                    "img" => "icon-cog",
                    "params" => array("id_pessoa" => $registro->id_pessoa, "id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Excluir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "excluirtelefone",
                    "img" => "icon-trash",
                    "class" => "link_excluir",
                    "params" => array("id_pessoa" => $registro->id_pessoa, "id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "telefone",
                    "img" => "icon-reply",
                    "params" => array("id_pessoa" => $registro->id_pessoa, "id" => $id)
                ));
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVï¿½LIDA!");
                $this->_redirect($this->_request->getControllerName() . "/telefone/id/0/id_pessoa/{$registro->id_pessoa}");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editartelefoneAction()
    {
        $id_pessoa = 0;
        $id = $this->_request->getParam("id");
        $registro = TbPessoaRef::pegaPorId($id);
        if ($registro) {
            if (!$registro->telefone()) {
                $this->_flashMessage("REGISTRO DE TELEFONE INVÁLIDO!");
                $this->_redirect($this->_request->getControllerName() . "/telefone/id/0/id_pessoa/{$registro->id_pessoa}");
            }
            $pessoa = $registro->findParentRow("TbPessoa");
            $telefone = $registro->getObjeto();
        } else {
            $tb = new TbTelefone();
            $telefone = $tb->createRow();
            $session = Escola_Session::getInstance();
            if (isset($session->id_pessoa) && $session->id_pessoa) {
                $id_pessoa = $session->id_pessoa;
            } elseif ($this->_request->getParam("id_pessoa")) {
                $id_pessoa = $this->_request->getParam("id_pessoa");
            }
            $pessoa = TbPessoa::pegaPorId($id_pessoa);
            if (!$pessoa) {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        }
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $telefone->setFromArray($dados);
            $errors = $telefone->getErrors();
            if ($errors) {
                $this->view->actionErrors = $errors;
            } else {
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $id = $telefone->save();
                if ($id) {
                    $pessoa->addTelefone($telefone);
                }
                $this->_redirect($this->_request->getControllerName() . "/telefone/id/0/id_pessoa/{$pessoa->getId()}");
            }
        }
        $this->view->registro = $registro;
        $this->view->pessoa = $pessoa;
        $this->view->telefone = $telefone;
        $button = Escola_Button::getInstance();
        if ($this->view->telefone->getId()) {
            $button->setTitulo("CADASTRO DE PESSOA > TELEFONE - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE PESSOA > TELEFONE - INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "telefone",
            "img" => "icon-reply",
            "params" => array("id_pessoa" => $id_pessoa, "id" => $id)
        ));
    }

    public function excluirtelefoneAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbPessoaRef();
            $registro = $tb->getPorId($id);
            if ($registro && $registro->telefone()) {
                $telefone = $registro->getObjeto();
                $errors = $telefone->getDeleteErrors();
                if (!$errors) {
                    $telefone->delete();
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                } else {
                    foreach ($errors as $erro) {
                        $this->_flashMessage($erro);
                    }
                }
                $this->_redirect($this->_request->getControllerName() . "/telefone/id/0/id_pessoa/{$registro->id_pessoa}");
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVï¿½LIDA!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }

    public function ibAction()
    {
        $session = Escola_Session::getInstance();
        $id_pessoa = false;
        if (isset($session->id_pessoa) && $session->id_pessoa) {
            $id_pessoa = $session->id_pessoa;
        } elseif ($this->_request->getParam("id_pessoa")) {
            $id_pessoa = $this->_request->getParam("id_pessoa");
        }
        if ($id_pessoa) {
            $session->id_pessoa = $id_pessoa;
            $pessoa = TbPessoa::pegaPorId($id_pessoa);
            if ($pessoa) {
                $tb = new TbInfoBancariaRef();
                $dados = array();
                $dados["pagina_atual"] = $this->_getParam("page");
                $dados["chave"] = $pessoa->getId();
                $dados["tipo"] = "P";
                $this->view->registros = $tb->listar_por_pagina($dados);
                $this->view->pessoa = $pessoa;
                $button = Escola_Button::getInstance();
                $button->setTitulo("CADASTRO DE PESSOA > INFORMAï¿½ï¿½ES BANCï¿½RIAS");
                $button->addFromArray(array(
                    "titulo" => "Adicionar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarib",
                    "img" => "icon-plus-sign",
                    "params" => array("id" => 0)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "index",
                    "img" => "icon-reply",
                    "params" => array("id" => 0, "id_pessoa" => $pessoa->getId())
                ));
            } else {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA PESSOA INFORMADA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUMA PESSOA INFORMADA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewibAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbInfoBancariaRef();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->ib = $registro->findParentRow("TbInfoBancaria");
                $this->view->pessoa = $registro->getObjeto();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR PESSOA > INFORMAÇÃO BANCï¿½RIA");
                $button->addFromArray(array(
                    "titulo" => "Alterar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "editarib",
                    "img" => "icon-cog",
                    "params" => array("id_pessoa" => $registro->chave, "id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Excluir",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "excluirib",
                    "img" => "icon-trash",
                    "class" => "link_excluir",
                    "params" => array("id_pessoa" => $registro->chave, "id" => $id)
                ));
                $button->addFromArray(array(
                    "titulo" => "Voltar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "ib",
                    "img" => "icon-reply",
                    "params" => array("id_pessoa" => $registro->chave, "id" => $id)
                ));
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVï¿½LIDA!");
                $this->_redirect($this->_request->getControllerName() . "/ib/id/0/id_pessoa/{$registro->chave}");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function editaribAction()
    {
        $id_pessoa = 0;
        $id = $this->_request->getParam("id");
        $registro = TbInfoBancariaRef::pegaPorId($id);
        if ($registro) {
            if (!$registro->pessoa()) {
                $this->_flashMessage("REGISTRO DE INFORMAï¿½ï¿½ES BANCï¿½RIAS INVÁLIDO!");
                $this->_redirect($this->_request->getControllerName() . "/ib/id/0/id_pessoa/{$registro->id_pessoa}");
            }
            $ib = $registro->findParentRow("TbInfoBancaria");
            $pessoa = $registro->getObjeto();
        } else {
            $tb = new TbInfoBancaria();
            $ib = $tb->createRow();
            $session = Escola_Session::getInstance();
            if (isset($session->id_pessoa) && $session->id_pessoa) {
                $id_pessoa = $session->id_pessoa;
            } elseif ($this->_request->getParam("id_pessoa")) {
                $id_pessoa = $this->_request->getParam("id_pessoa");
            }
            $pessoa = TbPessoa::pegaPorId($id_pessoa);
            if (!$pessoa) {
                $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        }
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $ib->setFromArray($dados);
            $errors = $ib->getErrors();
            if ($errors) {
                $this->view->actionErrors = $errors;
            } else {
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $id = $ib->save();
                if ($id) {
                    $pessoa->add_info_bancaria($ib);
                }
                $this->_redirect($this->_request->getControllerName() . "/ib/id/0/id_pessoa/{$pessoa->getId()}");
            }
        }
        $this->view->registro = $registro;
        $this->view->pessoa = $pessoa;
        $this->view->ib = $ib;
        $button = Escola_Button::getInstance();
        if ($this->view->ib->getId()) {
            $button->setTitulo("CADASTRO DE PESSOA > INFORMAï¿½ï¿½ES BANCï¿½RIAS - ALTERAR");
        } else {
            $button->setTitulo("CADASTRO DE PESSOA > INFORMAï¿½ï¿½ES BANCï¿½RIAS- INSERIR");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "ib",
            "img" => "icon-reply",
            "params" => array("id_pessoa" => $id_pessoa, "id" => $id)
        ));
    }

    public function excluiribAction()
    {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbInfoBancariaRef();
            $registro = $tb->getPorId($id);
            if ($registro && $registro->pessoa()) {
                $id_pessoa = $registro->chave;
                $ib = $registro->findParentRow("TbInfoBancaria");
                $errors = $ib->getDeleteErrors();
                if (!$errors) {
                    $ib->delete();
                    $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
                } else {
                    foreach ($errors as $erro) {
                        $this->_flashMessage($erro);
                    }
                }
                $this->_redirect($this->_request->getControllerName() . "/ib/id/0/id_pessoa/{$id_pessoa}");
            } else {
                $this->_flashMessage("INFORMAÇÃO RECEBIDA INVï¿½LIDA!");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }

    public function fotoAction()
    {
        $pessoa = TbPessoa::pegaPorId($this->_request->getParam("id"));
        if (!$pessoa) {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
            return;
        }

        $pt = $pessoa->findParentRow("TbPessoaTipo");
        if (!($pt && $pt->pf())) {
            $this->_flashMessage("TIPO DE PESSOA INVÁLIDO!");
            $this->_redirect($this->_request->getControllerName() . "/index");
            return;
        }

        $pf = $pessoa->pegaPessoaFilho();
        if (!$pf) {
            $this->_flashMessage("DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
            return;
        }

        $this->view->pessoa = $pessoa;
        $this->view->pf = $pf;
        $this->view->foto = $pessoa->getFoto();
        $button = Escola_Button::getInstance();
        $button->setTitulo("PESSOA > FOTOGRAFIA");
        $button->addScript("Capturar Foto", "capturar()", "icon-camera-retro");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "index",
            "img" => "icon-reply",
            "params" => array("id" => "0")
        ));
    }

    public function limpafotoAction()
    {
        $id_pessoa = $this->_request->getParam("id");
        if ($id_pessoa) {
            $pessoa = TbPessoa::pegaPorId($id_pessoa);
            if ($pessoa) {
                $pessoa->limparFoto();
                $foto = $pessoa->getFoto();
                if ($foto) {
                    $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                } else {
                    $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
                }
                $this->_redirect($this->_request->getControllerName() . "/foto/id/{$pessoa->getId()}");
            } else {
                $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function setfotoAction()
    {
        $id_pessoa = $this->_request->getParam("id");
        try {

            if (!$id_pessoa) {
                $ex = new IntranetException("Nenhuma INFORMAÇÃO Recebida!");
                $ex->setCaminho($this->_request->getControllerName() . "/index");
                throw $ex;
            }

            $pessoa = TbPessoa::pegaPorId($id_pessoa);
            if (!$pessoa) {
                $ex = new IntranetException("Nenhuma INFORMAÇÃO Recebida!!");
                $ex->setCaminho($this->_request->getControllerName() . "/index");
                throw $ex;
            }

            $file = Escola_Util::getUploadedFile("arquivo");
            if (!($file && is_array($file) && isset($file["size"]) && $file["size"])) {
                $ex = new IntranetException("Nenhum Arquivo Recebido!");
                $ex->setCaminho($this->_request->getControllerName() . "/foto/id/{$pessoa->getId()}");
                throw $ex;
            }

            $tb = new TbArquivo();
            $arquivo = $tb->createRow();
            $arquivo->setFromArray(array("legenda" => "Foto", "arquivo" => $file));
            if (!$arquivo->eImagem()) {
                $ex = new IntranetException("Arquivo não é uma Imagem!");
                $ex->setCaminho($this->_request->getControllerName() . "/foto/id/{$pessoa->getId()}");
                throw $ex;
            }

            $errors = $arquivo->getErrors();
            if ($errors) {
                foreach ($errors as $erro) {
                    $this->addErro($erro);
                }
                $this->_redirect($this->_request->getControllerName() . "/foto/id/{$pessoa->getId()}");
            }

            $arquivo->save();
            if (!$arquivo->getId()) {
                $ex = new IntranetException("Falha ao Executar OPERAÇÃO, Chame o Administrador!");
                $ex->setCaminho($this->_request->getControllerName() . "/foto/id/{$pessoa->getId()}");
                throw $ex;
            }

            if (!$pessoa->addFoto($arquivo)) {
                $ex = new IntranetException("Falha ao Executar OPERAÇÃO, Chame o Administrador!!");
                $ex->setCaminho($this->_request->getControllerName() . "/foto/id/{$pessoa->getId()}");
                throw $ex;
            }

            $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
            $this->_redirect($this->_request->getControllerName() . "/foto/id/{$pessoa->getId()}");
        } catch (IntranetException $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/" . $ex->getCaminho());
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function webcamAction()
    {

        if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            exit;
        }

        $folder = ROOT_DIR . "/application/file/";
        $filename = md5($_SERVER['REMOTE_ADDR'] . rand()) . '.jpg';

        $original = $folder . $filename;

        $input = file_get_contents('php://input');

        if (md5($input) == '7d4df9cc423720b7f1f3d672b89362be') {
            exit;
        }

        $result = file_put_contents($original, $input);

        if (!$result) {
            echo '{
                "error"		: 1,
                "message"	: "Erro ao guardar a imagem. Certifique-se que as permissÃµes das pastas estÃ£o definidas para 777."
            }';
            exit;
        }

        $info = getimagesize($original);
        if ($info['mime'] != 'image/jpeg') {
            unlink($original);
            exit;
        }

        $id = $this->_request->getParam("id");
        if ($id) {
            $pessoa = TbPessoa::pegaPorId($id);
            if ($pessoa) {
                $array_arquivo = array();
                $array_arquivo["tmp_name"] = $original;
                $array_arquivo["size"] = filesize($original);
                $array_arquivo["type"] = $info["mime"];
                $tb = new TbArquivo();
                $arquivo = $tb->createRow();
                $arquivo->setFromArray(array(
                    "legenda" => "FOTO PESSOA",
                    "arquivo" => $array_arquivo
                ));
                $arquivo->save();

                $pessoa->addFoto($arquivo);
            }
        }

        unlink($original);

        echo '{"status":1,"message":"Success!","filename":"' . $filename . '"}';
        /*
          rename($original,'uploads/original/'.$filename);
          $original = 'uploads/original/'.$filename;


          $origImage	= imagecreatefromjpeg($original);
          $newImage	= imagecreatetruecolor(154,110);
          imagecopyresampled($newImage,$origImage,0,0,0,0,154,110,520,370);

          imagejpeg($newImage,'uploads/thumbs/'.$filename);

          echo '{"status":1,"message":"Success!","filename":"'.$filename.'"}';
         */
    }
}
