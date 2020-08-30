<?php
class Escola_Controller_Portal extends Escola_Controller {
	
	public function init() {
		$tb = new TbSistema;
		$sistema = $tb->pegaSistema();
		if ($sistema) {
            $ps = $sistema->findParentRow("TbPortalStatus");
            if ($ps) {
                if ($ps->manutencao()) {
                    $this->_redirect("error/manutencao");
                    die();
                } elseif ($ps->inativo()) {
                    $this->_redirect("intranet");
                    die();
                }
            }
            $layout = "layout";
            $pl = $sistema->findParentRow("TbPortalLayout");
            if ($pl) {
                $layout = $pl->chave; 
            }
		}
		$this->_helper->layout->setLayout($layout);
		parent::init();
	}
	
    public function preDispatch() {
        $view = $this->view;
        
        $view->headLink()->appendStylesheet($view->baseUrl() . "/css/bootstrap.min.css");
        $view->headLink()->appendStylesheet($view->baseUrl() . "/css/bootstrap-responsive.min.css");
        $view->headLink()->appendStylesheet($view->baseUrl() . "/css/font-awesome.min.css");
        $view->headScript()->appendFile($view->baseUrl() . "/js/bootstrap.min.js");
        
        $view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/site.css");
        $view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/swipebox.css");
        $view->headScript()->appendFile($view->baseUrl() . "/js/" . $this->_helper->layout->getLayout() . "/jquery.swipebox.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/" . $this->_helper->layout->getLayout() . "/site.js");
        
        $this->view->headTitle(".:: FUNPEA ::.", "SET");
        
        parent::preDispatch();
    }
		
}