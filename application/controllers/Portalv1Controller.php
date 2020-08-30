<?php
class PortalController extends Escola_Controller_Portal {
    
    public function indexAction() {
        $p_contas = $galerias = $noticias = $destaques = false;
        $tb = new TbInfoTipo();
        $it = $tb->getPorChave("N");
        if ($it) {
            $tb = new TbInfoStatus();
            $is = $tb->getPorChave("P");
            if ($is) {
                $tb = new TbInfo();
                $noticias = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                              "qtd_por_pagina" => 7,
                                              "filtro_id_info_status" => $is->getId()));
                $destaques = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                              "qtd_por_pagina" => 4,
                                              "filtro_id_info_status" => $is->getId(),
                                              "filtro_destaque" => "S"));
                $tb_it = new TbInfoTipo();
                $it = $tb_it->getPorChave("G");
                $galerias = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                              "qtd_por_pagina" => 6,
                                              "filtro_id_info_status" => $is->getId(),
                                              "order" => "rand()"));
                $tb_it = new TbInfoTipo();
                $it = $tb_it->getPorChave("P");
                $p_contas = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                              "qtd_por_pagina" => 6,
                                              "filtro_id_info_status" => $is->getId()));
            }
        }
        $this->view->noticias = $noticias;
        $this->view->destaques = $destaques;
        $this->view->galerias = $galerias;
        $this->view->p_contas = $p_contas;
    }
    
    public function infosAction() {
        $chavetipo = $this->_request->getParam("chavetipo");
        $noticias = false;
        $tb = new TbInfoTipo();
        $it = $tb->getPorChave($chavetipo);
        if ($it) {
            $this->view->infotipo = $it;
            $tb = new TbInfoStatus();
            $is = $tb->getPorChave("P");
            if ($is) {
                $tb = new TbInfo();
                $noticias = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                                       "filtro_id_info_status" => $is->getId(),
                                                       "qtd_por_pagina" => 20));
            }
            $this->view->noticias = $noticias;
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect("portal");
        }
    }
    
    public function viewinfoAction() {
        $this->view->fotos = array();
        $this->view->arquivos = array();
        $this->view->comentarios = array();
        $this->view->foto_principal = false;
        $id = $this->_getParam("id");
        $info = TbInfo::pegaPorId($id);
        if ($info) {
            $this->view->info = $info;
            $comentarios = $this->view->info->pegaTbComentario();
            if ($comentarios && count($comentarios)) {
                $this->view->comentarios = $comentarios;
            }
            if ($info->galeria()) {
                $this->view->fotos[] = $info->findParentRow("TbArquivo");
            } else {
                $this->view->foto_principal = $info->findParentRow("TbArquivo");
            }
            $anexos = $info->pegaAnexos();
            if ($anexos && count($anexos)) {
                foreach ($anexos as $anexo) {
                    $arquivo = $anexo->pegaObjeto();
                    if ($arquivo->eImagem()) {
                        $this->view->fotos[] = $arquivo;
                    } else {
                        $this->view->arquivos[] = $arquivo;
                    }
                }
            }
        } else {
        	$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
        	$this->_redirect("portal");
        }
    }
    
    public function comentarioAction() {
        $dados = $this->_request->getPost();
        $tb = new TbComentario();
        $registro = $tb->createRow();
        $registro->setFromArray($dados);
        $errors = $registro->getErrors();
        if ($errors) {
            $this->_flashMessage(implode("<br>", $errors));
        } else {
            $this->_flashMessage("COMENTÁRIO RECEBIDO COM SUCESSO!", "Messages");
            $this->_flashMessage("AGUARDE A CONFIRMAÇÃO DO SEU COMENTÁRIO!", "Messages");
            $registro->save();
        }
        $this->_redirect($this->_request->getControllerName() . "/viewinfo/id/" . $dados["id_info"]);        
    }
}