<?php
class LayoutController extends Zend_Controller_Action {
	public function buttonAction() {
		$button = Escola_Button::getInstance();
		$this->view->botao = $button;
		$this->_helper->viewRenderer->setResponseSegment("button");
	}

	public function menuAction() {
		$menu = new Escola_Menu();
		$this->view->menu_p = $menu;
		$this->_helper->viewRenderer->setResponseSegment("menu");
	}
}