<?php

class ConfigController extends Escola_Controller_Logado {

    public function indexAction() {
        $tb = new TbSistema();
        $registro = $tb->pegaSistema();
        $pf = false;
        if ($registro) {
            $pf = $registro->findParentRow("TbPessoaJuridica");
        }
        if ($this->_request->isPost()) {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            try {
                $dados = $this->_request->getPost();
                $tmp_arquivo = Escola_Util::getUploadedFile("cliente_arquivo");
                $tb = new TbArquivoTipo();
                $at = $tb->getPorMimeType($tmp_arquivo["type"]);
                if (!$tmp_arquivo || ($at && $at->eImagem())) {
                    $registro->setFromArray($dados["sistema"]);
                    $errors = $registro->getErrors();
                    if (!$errors) {
                        $registro->save();
                        $dados["cliente"]["nome_fantasia"] = $dados["cliente"]["razao_social"];
                        $pf->setFromArray($dados["cliente"]);
                        $errors = $pf->getErrors();
                        if (!$errors) {
                            $pf->save();
                            $pessoa = $pf->findParentRow("TbPessoa");
                            var_dump($pessoa);
                            if ($tmp_arquivo) {
                                $arquivo = $pessoa->getFoto();
                                var_dump($arquivo); die();
                                if (!$arquivo) {
                                    $tb = new TbArquivo();
                                    $arquivo = $tb->createRow();
                                }
                                $arquivo->setFromArray(array("legenda" => "Logomarca", "arquivo" => $tmp_arquivo));
                                $id = $arquivo->save();
                                if ($id) {
                                    $pessoa->addFoto($arquivo);
                                }
                            }
                            $smtp = TbSmtp::getSmtp();
                            $smtp->setFromArray($dados["smtp"]);
                            $errors = $smtp->getErrors();
                            if (!$errors) {
                                $smtp->save();
                            } else {
                                $this->view->actionErrors = $errors;
                            }
                            $db->commit();
                            $this->view->actionMessages[] = "OPERAï¿½ï¿½O EFETUADA COM SUCESSO!";
                        } else {
                            $this->view->actionErrors = $errors;
                        }
                    } else {
                        $this->view->actionErrors = $errors;
                    }
                } else {
                    $this->view->actionErrors[] = "FALHA AO EXECUTAR OPERAï¿½ï¿½O, SOMENTE ARQUIVO DE IMAGEM.";
                }
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
        $this->view->registro = $registro;
        $this->view->pf = $pf;
        $this->view->smtp = TbSmtp::getSmtp();
        $button = Escola_Button::getInstance();
        $button->setTitulo("CONFIGURAï¿½ï¿½ES GERAIS");
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", "intranet", "index", "icon-remove-circle");
    }

}