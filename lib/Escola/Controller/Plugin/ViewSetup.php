<?php

class Escola_Controller_Plugin_ViewSetup extends Zend_Controller_Plugin_Abstract {

    /**
     * @var Zend_View
     */
    protected $_view;

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->init();
        $view = $viewRenderer->view;
        $this->_view = $view;
        $view->originalModule = $request->getModuleName();
        $view->originalController = $request->getControllerName();
        $view->originalAction = $request->getActionName();
        $view->doctype("XHTML1_STRICT");
        $prefix = "Escola_View_Helper";
        $dir = dirname(__FILE__) . "/../../View/Helper";
        $view->addHelperPath($dir, $prefix);
        $view->headMeta()->setName("Content-Type", "text/html;charset=utf-8");
        $view->headLink()->appendStylesheet($view->baseUrl() . "/css/ui/jquery.ui.all.css");

        $view->headScript()->appendFile($view->baseUrl() . "/js/jquery-1.8.3.js");
        $view->headLink()->appendStylesheet($view->baseUrl() . "/js/fancybox/jquery.fancybox.css");
        $view->headScript()->appendFile($view->baseUrl() . "/js/fancybox/jquery.fancybox.pack.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/jquery.maskedinput.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/jquery.price_format.js");
        $view->headScript()->appendFile($view->baseUrl() . "/ckeditor/ckeditor.js");

        $view->headScript()->appendFile($view->baseUrl() . "/js/ui/jquery.ui.core.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/ui/jquery.ui.widget.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/ui/jquery.ui.datepicker.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/ui/i18n/jquery.ui.datepicker-fr.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/ui/i18n/jquery.ui.datepicker-pt.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/ui/i18n/jquery.ui.datepicker-pt-BR.js");

        $view->headScript()->appendFile($view->baseUrl() . "/js/jshashtable.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/jquery.numberformatter.min.js");
        $view->headScript()->appendFile($view->baseUrl() . "/js/webcam/webcam.js");

        $view->headScript()->appendFile("https://cdn.jsdelivr.net/npm/lodash@4.17.15/lodash.min.js");
        $view->headScript()->appendFile("https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.26.0/moment.min.js");
        $view->headScript()->appendFile("https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.26.0/moment-with-locales.min.js");

        //$view->headScript()->appendFile("https://api.tiles.mapbox.com/mapbox.js/v2.1.2/mapbox.js");
        //$view->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?v=3.exp");
        //$view->headScript()->appendFile($view->baseUrl() . "/js/date_input/jquery.date_input.js");
        //$view->headScript()->appendFile($view->baseUrl() . "/js/date_input/translations/jquery.date_input.pt_PT.js");
        //$view->headLink()->appendStylesheet("https://api.tiles.mapbox.com/mapbox.js/v2.1.2/mapbox.css");

        $tb_sistema = new TbSistema();
        $sistema = $tb_sistema->pegaSistema();
        if ($sistema) {
            $view->headTitle(".:: " . $sistema . " ::.");
        } else {
            $view->headTitle("Intranet");
        }
        $view->auth = Zend_Auth::getInstance();
        $tb = new TbModulo();
        $view->modulo = $view->originalController;
        $mod = $tb->getPorController($view->originalController);
        if ($mod) {
            $view->modulo = $mod->descricao;
            $tb = new TbAcao();
            $view->acao = $view->originalAction;
            $ac = $tb->getPorAction($mod, $view->originalAction);
            if ($ac) {
                $view->acao = $ac->descricao;
            }
        }
    }

}