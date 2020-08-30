<?php
class Escola_Controller_Default extends Escola_Controller {
	
    public function preDispatch() {
        $view = $this->view;
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/bootstrap.min.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/bootstrap-responsive.min.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/theme.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/font-awesome.min.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/table-responsive.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/site.css");
		//$view->headScript()->appendFile($view->baseUrl() . "/js/" . $this->_helper->layout->getLayout() . "/interface.js");
		$view->headScript()->appendFile($view->baseUrl() . "/js/bootstrap.min.js");
        parent::preDispatch();
    }
	
}