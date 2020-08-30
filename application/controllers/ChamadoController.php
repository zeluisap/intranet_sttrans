<?php

class ChamadoController extends Escola_Controller_Logado {

    public function filtroAction() {
        $session = Escola_Session::getInstance();
        $filtros = array("filtro_tipo", "filtro_id_chamado_status", "filtro_descricao_problema", "filtro_nome", "filtro_setor", "filtro_id_chamado_tipo");
        $session->atualizaFiltros($filtros);
        $this->_redirect("chamado/index");
    }

    public function indexAction() {
        try {
            $page = $this->_getParam("page");
            $session = Escola_Session::getInstance();
            $filtros = array("filtro_tipo", "filtro_id_chamado_status", "filtro_descricao_problema", "filtro_nome", "filtro_setor", "filtro_id_chamado_tipo");
            $this->view->dados = $session->atualizaFiltros($filtros);
            $flag = false;
            foreach ($this->view->dados as $valor) {
                if ($valor) {
                    $flag = true;
                    break;
                }
            }
            $tb = new TbFuncionario();
            $this->view->funcionario = $tb->pegaLogado();
            $cts = false;
            if ($this->view->funcionario) {
                $lotacao = $this->view->funcionario->pegaLotacaoAtual();
                if ($lotacao) {
                    $setor = $lotacao->findParentRow("TbSetor");
                    if ($setor) {
                        $tb = new TbChamadoTipo();
                        $cts = $tb->pegaPorSetor($setor);
                    }
                }
            }
            if (!$flag) {
                if ($cts) {
                    $this->view->dados["filtro_tipo"] = "cx_p";
                } else {
                    $this->view->dados["filtro_tipo"] = "meus";
                }
            }
            $dados = $this->view->dados;
            $dados["pagina_atual"] = $page;
            $tb = new TbChamado();
            $this->view->registros = $tb->listarPorPagina($dados);
            $this->view->cts = $cts;
            $button = Escola_Button::getInstance();
            $button->setTitulo("CHAMADOS");
            $button->addFromArray(array("titulo" => "Novo",
                "controller" => $this->_request->getControllerName(),
                "action" => "editar",
                "img" => "icon-plus-sign",
                "params" => array("id" => 0)));
            /*
              $button->addFromArray(array("titulo" => "Pesquisar",
              "onclick" => "pesquisar()",
              "img" => "zoom.png",
              "params" => array("id" => 0)));
             */
            $button->addFromArray(array("titulo" => "Voltar",
                "controller" => "intranet",
                "action" => "index",
                "img" => "icon-reply",
                "params" => array("id" => 0)));
        } catch (Exception $ex) {
            $this->addErro($ex->getMessage());
            $this->_redirect("index");
        }
    }

    public function editarAction() {
        $tb = new TbChamadoTipo();
        $cts = $tb->listar();
        if (!$cts) {
            $this->_flashMessage("NENHUM TIPO DE CHAMADO CADASTRADO!");
            $this->_redirect($this->_request->getControllerName() . "/index");
            die();
        }
        $id = $this->_request->getParam("id");
        $tb = new TbChamado();
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
                $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                $registro->save();
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        }
        $this->view->registro = $registro;
        $button = Escola_Button::getInstance();
        if ($this->view->registro->getId()) {
            $button->setTitulo("ALTERAR CHAMADO");
        } else {
            $button->setTitulo("NOVO CHAMADO");
        }
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbChamado();
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

    public function viewAction() {
        $tb = new TbChamado();
        $registro = $tb->getPorId($this->_request->getParam("id"));
        if ($registro->getId()) {
            $this->view->registro = $registro;
            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAR CHAMADO");
            $button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function atendimentoAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $registro = TbChamado::pegaPorId($id);
            if ($this->_request->isPost()) {
                $tb = new TbFuncionario();
                $funcionario = $tb->pegaLogado();
                $dados = $this->_request->getPost();
                $dados["id_chamado"] = $registro->getId();
                $tb = new TbChamadoOcorrenciaTipo();
                $cot = $tb->getPorChave("A");
                if ($cot) {
                    $dados["id_chamado_ocorrencia_tipo"] = $cot->getId();
                }
                if ($funcionario) {
                    $dados["id_funcionario"] = $funcionario->getId();
                    $lotacao = $funcionario->pegaLotacaoAtual();
                    if ($lotacao) {
                        $dados["id_setor"] = $lotacao->id_setor;
                    }
                }
                $tb = new TbChamadoOcorrencia();
                $atendimento = $tb->createRow();
                $atendimento->setFromArray($dados);
                $errors = $atendimento->getErrors();
                if ($errors) {
                    $this->view->actionErrors = $errors;
                } else {
                    $tb = new TbChamadoStatus();
                    $cs = $tb->getPorChave("A");
                    if ($cs) {
                        $registro->id_chamado_status = $cs->getId();
                        $registro->save();
                        $atendimento->save();
                        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    }
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
            }
            $this->view->registro = $registro;
            $button = Escola_Button::getInstance();
            $button->setTitulo("ADICIONAR ATENDIMENTO");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function confirmacaoAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $registro = TbChamado::pegaPorId($id);
            if ($this->_request->isPost()) {
                $finalizado = false;
                $tb = new TbFuncionario();
                $funcionario = $tb->pegaLogado();
                $dados = $this->_request->getPost();
                $errors = array();
                if (!isset($dados["finaliza"]) || !$dados["finaliza"]) {
                    $errors[] = "CAMPO ATENDIDO COM SUCESSO? É OBRIGATÓRIO!";
                }
                if (isset($dados["finaliza"]) && ($dados["finaliza"] == "S") && !$dados["finaliza"]) {
                    $errors[] = "CAMPO NOTA OBRIGATÓRIO PARA CONFIRMAÇÃO DE CHAMADO!!";
                }
                if (isset($dados["finaliza"]) && ($dados["finaliza"] == "N") && !$dados["observacoes"]) {
                    $errors[] = "CAMPO OBSERVAÇÕES OBRIGATÓRIO PARA CHAMADOS NÃO FINALIZADOS!!";
                }
                if ($errors && count($errors)) {
                    $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
                if (isset($dados["finaliza"]) && ($dados["finaliza"] == "S")) {
                    $finalizado = true;
                }
                $dados["id_chamado"] = $registro->getId();
                $tb = new TbChamadoOcorrenciaTipo();
                if ($finalizado) {
                    $cot = $tb->getPorChave("F");
                } else {
                    $cot = $tb->getPorChave("T");
                }
                if ($cot) {
                    $dados["id_chamado_ocorrencia_tipo"] = $cot->getId();
                }
                if ($funcionario) {
                    $dados["id_funcionario"] = $funcionario->getId();
                    $lotacao = $funcionario->pegaLotacaoAtual();
                    if ($lotacao) {
                        $dados["id_setor"] = $lotacao->id_setor;
                    }
                }
                $tb = new TbChamadoOcorrencia();
                $atendimento = $tb->createRow();
                $atendimento->setFromArray($dados);
                $errors = $atendimento->getErrors();
                if ($errors) {
                    $this->view->actionErrors = $errors;
                } else {
                    $tb = new TbChamadoStatus();
                    if ($finalizado) {
                        $cs = $tb->getPorChave("F");
                        if (isset($dados["nota"])) {
                            $registro->nota = $dados["nota"];
                        }
                    } else {
                        $cs = $tb->getPorChave("E");
                    }
                    if ($cs) {
                        $registro->id_chamado_status = $cs->getId();
                        $registro->save();
                        $atendimento->save();
                        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    }
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
            }
            $this->view->registro = $registro;
            $button = Escola_Button::getInstance();
            $button->setTitulo("ADICIONAR ATENDIMENTO");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
        } else {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

}