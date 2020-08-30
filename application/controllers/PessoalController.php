<?php

class PessoalController extends Escola_Controller_Logado {

    public function indexAction() {
        $registro = Escola_Acl::getInstance()->getUsuarioLogado();
        if ($registro) {
            $telefones = $registro->pega_pessoa_fisica()->pega_pessoa()->getTelefones("F");
            if ($telefones) {
                $telefone = $telefones[0]->ddd . $telefones[0]->numero;
            }
            $telefones = $registro->pega_pessoa_fisica()->pega_pessoa()->getTelefones("C");
            if ($telefones) {
                $celular = $telefones[0]->ddd . $telefones[0]->numero;
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
                        $pessoa = $registro->pega_pessoa_fisica()->pega_pessoa();
                        $fones = $pessoa->getTelefones();
                        if ($fones) {
                            foreach ($fones as $fone) {
                                $fone->delete();
                            }
                        }
                        $numeros = new Zend_Filter_Digits();
                        $tb_telefone_tipo = new TbTelefoneTipo();
                        $tb_telefone = new TbTelefone();
                        if (isset($dados["telefone"]) && $dados["telefone"]) {
                            if ($numeros->filter($dados["telefone"])) {
                                $dados["telefone"] = explode(")", $dados["telefone"]);
                                if (count($dados["telefone"]) > 1) {
                                    $tt = $tb_telefone_tipo->getPorChave("F");
                                    $flag = array("ddd" => $numeros->filter($dados["telefone"][0]),
                                        "numero" => $numeros->filter($dados["telefone"][1]),
                                        "id_telefone_tipo" => $tt->getId());
                                    $telefone = $tb_telefone->createRow();
                                    $telefone->setFromArray($flag);
                                    $telefone->save();
                                    $pessoa->addTelefone($telefone);
                                }
                            }
                        }
                        if (isset($dados["celular"]) && $dados["celular"]) {
                            if ($numeros->filter($dados["celular"])) {
                                $dados["celular"] = explode(")", $dados["celular"]);
                                if (count($dados["celular"]) > 1) {
                                    $tt = $tb_telefone_tipo->getPorChave("C");
                                    $flag = array("ddd" => $numeros->filter($dados["celular"][0]),
                                        "numero" => $numeros->filter($dados["celular"][1]),
                                        "id_telefone_tipo" => $tt->getId());
                                    $telefone = $tb_telefone->createRow();
                                    $telefone->setFromArray($flag);
                                    $telefone->save();
                                    $pessoa->addTelefone($telefone);
                                }
                            }
                        }
                    }
                    /* FOTO */
                    $upload = new Zend_File_Transfer();
                    $files = $upload->getFileInfo();
                    $upload->receive();
                    if (isset($files["arquivo_foto"]) && $files["arquivo_foto"]["size"]) {
                        $tb = new TbArquivoTipo();
                        $at = $tb->getPorMimeType($files["arquivo_foto"]["type"]);
                        if ($at && ($at->eJpeg() || $at->ePng())) {
                            $files["arquivo_foto"]["tmp_name"] = $upload->getFileName();
                            $arquivo = $pessoa->getFoto();
                            if (!$arquivo) {
                                $tb = new TbArquivo();
                                $arquivo = $tb->createRow();
                            }
                            $arquivo->setFromArray(array("legenda" => "Foto", "arquivo" => $files["arquivo_foto"]));
                            $id = $arquivo->save();
                            if ($id) {
                                $pessoa->addFoto($arquivo);
                            }
                        } else {
                            $this->_flashMessage("FOTO Nï¿½O SALVA, ARQUIVO PRECISA SER DO TIPO JPEG!");
                        }
                    }
                    $this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
                    $this->_redirect($this->_request->getControllerName() . "/index");
                }
            }
            $this->view->registro = $registro;
            $this->view->pf = $registro->pega_pessoa_fisica();
            $button = Escola_Button::getInstance();
            $button->setTitulo("USUÁRIO LOGADO - DADOS PESSOAIS");
            $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
            $button->addAction("Cancelar", "intranet", "index", "icon-remove-circle");
        } else {
            throw new Exception("FALHA AO EXECUTAR OPERAï¿½ï¿½O, NENHUM USUï¿½RIO LOGADO!");
        }
    }

}