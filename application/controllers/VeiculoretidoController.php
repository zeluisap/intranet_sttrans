<?php 
class VeiculoretidoController extends Escola_Controller_Logado {
    
    public function indexAction() {
        $tb = new TbVeiculoRetido();
        
        $sessao = Escola_Session::getInstance();
        $this->view->dados = $sessao->atualizaFiltros(array("filtro_placa", "filtro_chassi", "filtro_alfa", "filtro_codigo", "filtro_pf_nome", "filtro_data_infracao", "filtro_id_veiculo_retido_status"));
        $this->view->dados["pagina_atual"] = $this->_getParam("page");
        $this->view->registros = $tb->listar_por_pagina($this->view->dados);
        $button = Escola_Button::getInstance();
        $button->setTitulo("VEÍCULOS RETIDOS");
        $button->addFromArray(array("titulo" => "Pesquisar",
                                                "onclick" => "pesquisar()",
                                                "img" => "icon-search",
                                                "params" => array("id" => 0)));
        $button->addFromArray(array("titulo" => "Voltar",
                                                "controller" => "intranet",
                                                "action" => "index",
                                                "img" => "icon-reply",
                                                "params" => array("id" => 0)));
    }
    
    public function viewAction() {
        $id = $this->_request->getParam("id");
        if ($id) {
            $tb = new TbVeiculoRetido();
            $registro = $tb->getPorId($id);
            if ($registro) {
                $this->view->registro = $registro;
                $this->view->vrl = $registro->pegaVeiculoRetidoLiberacao();
                $button = Escola_Button::getInstance();
                $button->setTitulo("VISUALIZAR VEÍCULO RETIDO");
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
    
    public function liberarAction() {
        try {
            $id = $this->getParam("id");
            if (!$id) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
            $vr = TbVeiculoRetido::pegaPorId($id);
            if (!$vr) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
            $vr->liberar();
            $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
        } catch (Exception $ex) {
            $this->addErro($ex->getMessage());
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }
    
    public function cancelarliberarAction() {
        try {
            $id = $this->getParam("id");
            if (!$id) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
            $vr = TbVeiculoRetido::pegaPorId($id);
            if (!$vr) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
            $vr->cancelar_liberacao();
            $this->addMensagem("OPERAÇÃO EFETUADA COM SUCESSO!");
        } catch (Exception $ex) {
            $this->addErro($ex->getMessage());
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }
    
    public function emitirAction() {
        try {
            $id = $this->getParam("id");
            if (!$id) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
            $vr = TbVeiculoRetido::pegaPorId($id);
            if (!$vr) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            }
            $relatorio = new Escola_Relatorio_VeiculoRetidoLiberacao();
            $relatorio->set_veiculo_retido($vr);
            $relatorio->imprimir();
        } catch (Exception $ex) {
            $this->addErro($ex->getMessage());
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
    }
}