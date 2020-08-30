<?php

class CarteiraController extends Escola_Controller_Logado {
    
    public function indexAction(){
        $button = Escola_Button::getInstance();
		$button->setTitulo("CARTEIRA DE ESTACIONAMENTO");
		$button->addFromArray(array("titulo" => "Adicionar",
                                        "controller" => $this->_request->getControllerName(),
                                            "action" => "editar",
                                               "img" => "icon-plus-sign",
                                        "params" => array("id" => 0)));
		$button->addFromArray(array("titulo" => "Voltar",
                                        "controller" => "intranet",
                                            "action" => "index",
                                               "img" => "icon-reply",
                                            "params" => array("id" => 0)));
    }
    
    public function editarAction(){
        $button = Escola_Button::getInstance();
        $button->setTitulo("CADASTRO DE CARTEIRA DE ESTACIONAMENTO");
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addAction("Cancelar", $this->_request->getControllerName(), "index", "icon-remove-circle");
    }
}