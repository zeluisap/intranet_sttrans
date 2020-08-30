<?php
class Escola_Controller_Portal1 extends Escola_Controller {
	
	public function init() {
        $this->_helper->layout->setLayout("portal1");
		parent::init();
	}
	
    public function preDispatch() {
        $view = $this->view;
        $view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/geral.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/menu.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/screen.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/jcarousel.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/sliderman.css");
		$view->headLink()->appendStylesheet($view->baseUrl() . "/css/" . $this->_helper->layout->getLayout() . "/site.css");
		
		$view->headScript()->appendFile($view->baseUrl() . "/js/" . $this->_helper->layout->getLayout() . "/jquery_slide.js");
		$view->headScript()->appendFile($view->baseUrl() . "/js/" . $this->_helper->layout->getLayout() . "/easySlider1.7.js");
		$view->headScript()->appendFile($view->baseUrl() . "/js/" . $this->_helper->layout->getLayout() . "/jquery.jcarousel.min.js");
		$view->headScript()->appendFile($view->baseUrl() . "/js/" . $this->_helper->layout->getLayout() . "/sliderman.1.3.7.js");
		$this->view->headTitle(".:: Portal Prefeitura de Santana - AP ::.", "SET");
        parent::preDispatch();
    }
		
}