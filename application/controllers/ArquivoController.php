<?php

class ArquivoController extends Escola_Controller_Default {

    public function preDispatch() {
        parent::preDispatch();

        $this->carregar([
            "transporte", "motorista"
        ]);

    }

    public function showAction() {
        $result = false;
        $arquivo = TbArquivo::pegaPorId($this->_request->getParam("id"));
        $this->view->arquivo = false;
        if ($arquivo) {
            $this->view->arquivo = $arquivo->getWideImage();
            if ($this->_request->getParam("width")) {
                if ($this->_request->getParam("height")) {
                    $mode = $this->_request->getParam("mode");
                    if (!$mode) {
                        $mode = "inside";
                    }
                    $this->view->arquivo = $this->view->arquivo->resize($this->_request->getParam("width"), null, $mode);
                    $this->view->arquivo = $this->view->arquivo->crop(0, 0, $this->_request->getParam("width"), $this->_request->getParam("height"));
                } else {
                    $this->view->arquivo = $this->view->arquivo->resize($this->_request->getParam("width"));
                }
            }
            header("Content-type: image/jpeg");
            if ($this->view->arquivo) {
                echo $this->view->arquivo->asString("jpg", 80);
            } else {
                echo "falha ao executar operaï¿½ï¿½o!";
            }
        }
        die();
    }

    public function viewAction() {

        try {

            $result = false;
            $arquivo = TbArquivo::pegaPorId($this->_request->getParam("id"));
            if (!$arquivo) {
                throw new Escola_Exception("Arquivo não localizado!");
            }
            
            $arquivo_tipo = $arquivo->findParentRow("TbArquivoTipo");
            $filename = $arquivo->pegaNomeCompleto();
            $operacao = "attachment";

            if ($arquivo_tipo->eImagem()) {
                $operacao = "inline";
            }

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!$ext) {
                $ext = $arquivo_tipo->extensao;
            }

            $basename = pathinfo($filename, PATHINFO_FILENAME);

            $novo_nome = $arquivo->legenda;
            if (!$novo_nome) {
                $novo_nome = $basename;
            }

            $filter = new Zend_Filter_CharConverter();
            $filename_new = str_replace(" ", "_", Escola_Util::minuscula($filter->filter($novo_nome)));
            $filename_new .= "_" . date("YmdHis") . "." . Escola_Util::pegaExtensao($filename);

            header("Content-Type: " . $arquivo_tipo->mime_type);
            header("Content-Disposition: {$operacao}; filename=" . $filename_new);

            $f = fopen($filename, "r");
            $buffer = fread($f, filesize($filename));
            fclose($f);
            echo $buffer;

            die();
    
        } catch (Exception $ex) {
            $this->view->actionErrors[] = $ex->getMessage();
            $this->_redirect($this->_request->getControllerName() . "/index");
        }

    }

    public function indexAction() {

        try {

            $this->removeGrupoValor("ano");

            $tb_doc = new TbDocumento();

            $this->view->documentos_por_ano = $tb_doc->getEstatisticaPorAno([
                "tipo" => $this->entidade,
                "chave" => $this->entidade_id
            ]);

            $button = Escola_Button::getInstance();
            $button->setTitulo("ARQUIVOS");

            if ($this->view->documentos_por_ano && count($this->view->documentos_por_ano)) {
                $button->addFromArray(array(
                    "titulo" => "Baixar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "baixar",
                    "img" => "icon-download",
                    "params" => array("id_documento_ref" => 0),
                ));
            }

            $controller_voltar = "intranet";
            if ($this->entidade) {
                $controller_voltar = $this->entidade;
            }

            $button->addFromArray(array(
                "titulo" => "Adicionar",
                "controller" => $this->_request->getControllerName(),
                "action" => "editar",
                "img" => "icon-plus-sign",
                "params" => array("id_documento_ref" => 0),
            ));

            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $controller_voltar,
                "action" => "index",
                "img" => "icon-reply",
                "params" => array("id" => 0),
            ));

        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect("/intranet/index");
        }

    }

    public function arquivoAction()
    {
        try {

            if ($ano = $this->_request->getParam("ano")) {
                $this->addGrupoValor("ano", "Ano", $ano);
            }

            $ano = $this->getHeaderGrupoValor("ano");

            $tb = new TbDocumentoRef();
            $this->view->registros = $tb->listar_por_pagina([
                "tipo" => $this->entidade, 
                "chave" => $this->entidade_id,
                "ano" => $ano
            ]);

            $button = Escola_Button::getInstance();
            $button->setTitulo("ARQUIVOS");

            if ($this->view->registros && count($this->view->registros)) {
                $button->addFromArray(array(
                    "titulo" => "Baixar",
                    "controller" => $this->_request->getControllerName(),
                    "action" => "baixar",
                    "img" => "icon-download",
                    "params" => array("id_documento_ref" => 0),
                ));
            }

            $button->addFromArray(array(
                "titulo" => "Adicionar",
                "controller" => $this->_request->getControllerName(),
                "action" => "editar",
                "img" => "icon-plus-sign",
                "params" => array("id_documento_ref" => 0),
            ));

            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => "index",
                "img" => "icon-reply",
                "params" => array("id_documento_ref" => 0),
            ));

        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }

    public function viewarquivoAction()
    {
        try {

            $id = $this->_request->getParam("id_documento_ref");
            if (!$id) {
                throw new Escola_Exception("NENHUMA INFORMAÇÃO RECEBIDA!");
            }

            $tb = new TbDocumentoRef();
            $registro = $tb->getPorId($id);
            if (!$registro) {
                throw new Escola_Exception("INFORMAÇÃO RECEBIDA INVÁLIDA!!");
            }

            $this->view->registro = $registro;
            $this->view->documento = $registro->findParentRow("TbDocumento");
            $this->view->arquivo = $this->view->documento->pega_arquivo();

            $voltar_action = "index";
            if ($this->getHeaderGrupoValor("ano")) {
                $voltar_action = "arquivo";
            }

            $button = Escola_Button::getInstance();
            $button->setTitulo("VISUALIZAÇÃO DO ARQUIVO");
            $button->addFromArray(array(
                "titulo" => "Voltar",
                "controller" => $this->_request->getControllerName(),
                "action" => $voltar_action,
                "img" => "icon-reply",
                "params" => array("id" => $this->_getParam("id")),
            ));


        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/index");
        }

    }

    public function excluirAction()
    {
        try {

            $id = $this->_request->getParam("id_documento_ref");
            if (!$id) {
                throw new Escola_Exception("NENHUMA INFORMAÇÃO RECEBIDA!");
            }

            $tb = new TbDocumentoRef();
            $registro = $tb->getPorId($id);
            if (!$registro) {
                throw new Escola_Exception("INFORMAÇÃO RECEBIDA INVÁLIDA!");
            }
            
            $registro->delete();
            
            $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
        }
        
        $this->_redirect($this->_request->getControllerName() . "/arquivo");
    }

    public function editarAction()
    {
        try {

            $grupo_ano = $this->getHeaderGrupoValor("ano");

            $this->view->grupo_ano = $grupo_ano;
            $this->view->ano = $grupo_ano;

            $id = $this->_request->getParam("id_documento_ref");
            $registro = TbDocumentoRef::pegaPorId($id);

            if (!$registro) {
                $tb = new TbDocumentoRef();
                $registro = $tb->createRow();
            }

            $this->view->registro = $registro;

            $documento = $registro->findParentRow("TbDocumento");

            $tb = new TbDocumentoTipoTarget();
            $tb_dt = new TbDocumentoTipo();

            $this->view->dtts = $tb->listar();
            $this->view->dts = $tb_dt->listar();

            $this->view->documento = false;
            $this->view->arquivo = false;
            $this->view->dados = array();
            $this->view->operacao = "";

            if ($documento) {

                $this->view->ano = $documento->ano;

                $this->view->documento = $documento;
                $this->view->arquivo = $documento->pega_arquivo();
                $this->view->dados["id_documento_tipo_target"] = $documento->findParentRow("TbDocumentoTipo")->findParentRow("TbDocumentoTipoTarget")->getId();
                $this->view->dados["ano"] = $documento->ano;

                if ($documento->eAdministrativo()) {
                    $this->view->operacao = "set_documento";
                }
            }

            if (!$this->view->ano) {
                $this->view->ano = date("Y");
            }
            
            $this->view->documento = $documento;

            if ($novo_doc = $this->editarPost()) {
                $this->view->documento = $novo_doc;
            }


            $cancelar_action = "index";
            if ($this->getHeaderGrupoValor("ano")) {
                $cancelar_action = "arquivo";
            }

            $button = Escola_Button::getInstance();
            $button->setTitulo("CADASTRO DE ARQUIVO");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addFromArray(array(
                "titulo" => "Cancelar",
                "controller" => $this->_request->getControllerName(),
                "action" => $cancelar_action,
                "img" => "icon-remove-circle",
                "params" => array("id" => $this->_getParam("id")),
            ));
        } catch (Escola_Exception $ex) {
            $this->view->actionErrors[] = $ex->getMessage();
        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName());
        }

    }

    private function editarPost()
    {
        if (!$this->_request->isPost()) {
            return null;
        }

        $dados = $this->_request->getPost();
        $this->view->dados = $dados;

        if (isset($dados["id_documento"]) && $dados["id_documento"]) {
            $documento = TbDocumento::pegaPorId($dados["id_documento"]);
        }

        if (isset($dados["operacao"]) && ($dados["operacao"] == "set_documento")) {

            $entidade = $this->entidade;
            $metodo = "add" . ucfirst($entidade);
            $documento->$metodo($this->$entidade);

            $this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
            $this->_redirect($this->_request->getControllerName() . "/arquivo");
            return $documento;

        }

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
            return $documento;
        }

        $id = $documento->save();
        if ($id) {
            $entidade = $this->entidade;
            $metodo = "add" . ucfirst($entidade);
            $documento->$metodo($this->$entidade);
        }

        $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");

        $action = "index";
        if ($this->getHeaderGrupoValor("ano")) {
            $action = "arquivo";
        }
        $this->_redirect($this->_request->getControllerName() . "/" . $action);

    }

    public function baixarAction()
    {

        try {

            $filenameList = [];
            $entidade = $this->entidade;
            $filenameList[] = $this->$entidade->toString();

            $params = [
                "tipo" => $this->entidade, 
                "chave" => $this->entidade_id,
            ];

            if ($ano = $this->getHeaderGrupoValor("ano")) {
                $params["ano"] = $ano;
                $filenameList[] = $ano;
            }
        
            $tb = new TbDocumentoRef();
            $registros = $tb->listar($params);

            if (!($registros && count($registros))) {
                throw new Escola_Exception("NENHUM ARQUIVO DISPONÍVEL!");
            }

            $filenameList = array_map("Escola_Util::textoParaFieldName", $filenameList);

            $path_tmp = ROOT_DIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ["application", "file", "tmp"]) . DIRECTORY_SEPARATOR . implode("_", $filenameList) . DIRECTORY_SEPARATOR;
            if (!file_exists($path_tmp)) {
                $flag = mkdir($path_tmp);
                if (!$flag) {
                    throw new Escola_Exception("FALHA AO EXECUTAR OPERAÇÃO, IMPOSSÍVEL CRIAR PASTA TEMPORÁRIA!");
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
                if (!$doc) {
                    continue;
                }

                $arquivo = $doc->pega_arquivo();
                if (!($arquivo && $arquivo->existe())) {
                    continue;
                }

                $at = $arquivo->findParentRow("TbArquivoTipo");
                if (!$at) {
                    continue;
                }

                $nome_completo = $arquivo->pegaNomeCompleto();
                $filter = new Zend_Filter_CharConverter();

                $filename = Escola_Util::textoParaFieldName([
                    $arquivo->nome_fisico,
                    $doc->resumo
                ]);

                $filename_new = $path_tmp . $filename . "." . $at->extensao;
                $flag = copy($nome_completo, $filename_new);
                if ($flag) {
                    $arquivos[] = $filename_new;
                }

            }

            $zip = new Zend_Filter_Compress_Zip();
            $filename = Escola_Util::textoParaFieldName(implode(DIRECTORY_SEPARATOR, $filenameList));

            $zip->setArchive($path_tmp . "{$filename}.zip");

            $arquivoZipado = $zip->compress($path_tmp);
            if (!($arquivoZipado && file_exists($arquivoZipado))) {
                throw new Escola_Exception("FALHA AO GERAR ARQUIVO ZIP!");
            }

            header("Content-Type: " . mime_content_type($arquivoZipado));
            header("Content-Disposition: attachment; filename={$filename}.zip");
            $f = fopen($arquivoZipado, "r");
            $buffer = fread($f, filesize($arquivoZipado));
            fclose($f);
            echo $buffer;
            die();

        } catch (Exception $ex) {
            $this->_flashMessage($ex->getMessage());
            $this->_redirect($this->_request->getControllerName() . "/arquivo");

        }
    
    }

}