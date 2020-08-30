<?php
class IndexController extends Escola_Controller_Portal {
    
    public function indexAction() {
        $this->_redirect("portal/index");
        die();
        $p_contas = $galerias = $destaques = false;
        $noticias = array("principal" => false, "secundarias" => array(), "outras" => array());
        $tb = new TbInfoTipo();
        $it = $tb->getPorChave("N");
        if ($it) {
            $tb = new TbInfoStatus();
            $is = $tb->getPorChave("P");
            if ($is) {
                $tb = new TbInfo();
                $nots = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                              "qtd_por_pagina" => 10,
                                              "filtro_id_info_status" => $is->getId()));
                $this->view->noticias = $nots;
                $infos = $tb->listarPorPagina(array("qtd_por_pagina" => 3,
                                              "filtro_id_info_status" => $is->getId(),
                							  "filtro_id_arquivo" => true)); 
                if ($infos) {
                	$contador = 0;
                	foreach ($infos as $not) {
                		$contador++;
                		if ($contador == 1) {
                			$noticias["principal"] = $not;
                		} elseif (($contador == 2) || ($contador == 3)) {
                			$noticias["secundarias"][] = $not;
                		}
                	}
                }
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
            }
        }
        $this->view->infos = $noticias;
        $this->view->destaques = $destaques;
        $this->view->galerias = $galerias;
    }
    
    public function viewAction() {
        
    }
    
    public function viewallAction() {
        
    }
}