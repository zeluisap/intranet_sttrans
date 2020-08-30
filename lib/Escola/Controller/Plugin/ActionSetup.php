<?php
class Escola_Controller_Plugin_ActionSetup extends Zend_Controller_Plugin_Abstract {
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		$front = Zend_Controller_Front::getInstance();
		if (!$front->hasPlugin('Zend_Controller_Plugin_ActionStack')) {
			$actionStack = new Zend_Controller_Plugin_ActionStack();
			$front->registerPlugin($actionStack, 97);
		} else {
			$actionStack = $front->getPlugin('Zend_Controller_Plugin_ActionStack');
		}
		$buttonAction = clone($request);
		$buttonAction->setActionName("button");
		$buttonAction->setControllerName("layout");
		$actionStack->pushStack($buttonAction);		
	}
}