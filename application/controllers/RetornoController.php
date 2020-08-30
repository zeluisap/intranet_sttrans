<?php

class RetornoController extends Escola_Controller_Logado {

    public function indexAction() {
        $session = Escola_Session::getInstance();
        if (isset($session->id_retorno)) {
            unset($session->id_retorno);
        }
        $tb = new TbRetorno();
        $page = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina(array("pagina_atual" => $page));
        $button = Escola_Button::getInstance();
        $button->setTitulo("ARQUIVOS DE RETORNO");
        $button->addFromArray(array("titulo" => "Importar Arquivo",
            "controller" => $this->_request->getControllerName(),
            "action" => "arquivo",
            "img" => "icon-plus-sign",
            "params" => array("id" => 0)));
        $button->addFromArray(array("titulo" => "Voltar",
            "controller" => "intranet",
            "action" => "index",
            "img" => "icon-reply",
            "params" => array("id" => 0)));
    }

    public function arquivoAction() {
        if ($this->_request->isPost()) {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            try {
                $file = Escola_Util::getUploadedFile("arquivo");
                if (!($file && isset($file["size"]) && $file["size"])) {
                    throw new Exception("Falha Nenhum Arquivo Enviado!");
                }

                $dados = $this->_request->getPost();
                $rt = false;
                if (isset($dados["id_retorno_tipo"]) && $dados["id_retorno_tipo"]) {
                    $rt = TbRetornoTipo::pegaPorId($dados["id_retorno_tipo"]);
                }
                if (!$rt) {
                    throw new Exception("Falha, Nenhum Tipo de Retorno Definido!");
                }

                $tb = new TbRetorno();
                $retorno = $tb->createRow();
                $retorno->id_retorno_tipo = $rt->getId();
                $retorno->save();
                $tb = new TbArquivo();
                $arquivo = $tb->createRow();
                $arquivo->setFromArray(array("arquivo" => $file, "legenda" => "Arquivo de Retorno."));
                if (!$arquivo->eTexto()) {
                    throw new Exception("Falha, Somente Arquivo de Texto!");
                }

                $errors = $arquivo->getErrors();
                if ($errors) {
                    $this->view->actionErrors = $errors;
                    throw new Exception("");
                }

                $arquivo->save();
                if (!($arquivo->getId() && $retorno->getId())) {
                    throw new Exception("Falha ao Executar Operação!");
                }

                $tb = new TbArquivoRef();
                $ar = $tb->createRow();
                $ar->setFromArray(array("tipo" => "R", "chave" => $retorno->getId(), "id_arquivo" => $arquivo->getId()));
                $errors = $ar->getErrors();
                if ($errors) {
                    $this->view->actionErrors = $errors;
                    throw new Exception("");
                }

                $ar->save();

                $relatorio = $retorno->processar();

                if ($relatorio->errors) {
                    $this->_flashMessage(implode("<br>", $relatorio->errors), "Errors");
                }

                if ($relatorio->sucesso) {
                    $this->_flashMessage("{$relatorio->sucesso} Registro(s) Processado(s)!", "Messages");
                }

                $db->commit();

                $this->_redirect($this->_request->getControllerName() . "/index");
                die();
            } catch (Exception $ex) {
                $db->rollBack();

                if ($ex->getMessage()) {
                    $this->_flashMessage($ex->getMessage());
                }
            }
        }
        $tb = new TbRetornoTipo();
        $rts = $tb->listar();
        if (!$rts) {
            $this->_flashMessage("NENHUM TIPO DE ARQUIVO DE RETORNO DISPONÍVEL!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
        $this->view->rts = $rts;
        $button = Escola_Button::getInstance();
        $button->setTitulo("IMPORTAR ARQUIVO DE RETORNO");
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }

    public function excluirAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbRetorno();
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
        $session = Escola_Session::getInstance();
        if (isset($session->id_retorno)) {
            $id = $session->id_retorno;
        } else {
            $id = $this->_request->getParam("id");
        }
        if ($id) {
            $tb = new TbRetorno();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $session->id_retorno = $id;
                $this->view->registro = $registro;
                $tb = new TbRetornoItem();
                $registros = $tb->listar_por_pagina(array("filtro_id_retorno" => $registro->getId()));
                $this->view->registros = false;
                if ($registros && count($registros)) {
                    $this->view->registros = $registros;
                }
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR RETORNO");
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

    public function viewboletoAction() {
        $id = $this->_request->getParam("id_retorno_item");
        if (!$id) {
            $this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
            $this->_redirect($this->_request->getControllerName() . "/view");
            die();
        }
        $tb = new TbRetornoItem();
        $registro = $tb->getPorId($id);
        if (!$registro) {
            $this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
            $this->_redirect($this->_request->getControllerName() . "/view");
            die();
        }
        $this->view->registro = $registro;
        $this->view->items = false;
        $boleto = $registro->findParentRow("TbBoleto");
        $this->view->boleto = false;
        if ($boleto) {
            $this->view->boleto = $boleto;
            $this->view->items = $boleto->pegaItems();
        }
        $button = Escola_Button::getInstance();
        $button->setTitulo("VISUALIZAR BOLETO");
        $button->addFromArray(array("titulo" => "Voltar",
            "controller" => $this->_request->getControllerName(),
            "action" => "view",
            "img" => "icon-reply",
            "params" => array("id" => 0)));
    }

}