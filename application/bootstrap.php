<?php

class Bootstrap
{

    public function __construct($configSection)
    {
        //ini_set("display_errors", false);
        $rootDir = dirname(dirname(__FILE__));
        define("ROOT_DIR", $rootDir);
        set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . "/library/" .
            PATH_SEPARATOR . ROOT_DIR . "/application/models/Entidade/" .
            PATH_SEPARATOR . ROOT_DIR . "/application/models/Tabela/" .
            PATH_SEPARATOR . ROOT_DIR . "/application/forms/" .
            PATH_SEPARATOR . ROOT_DIR . "/lib/" .
            PATH_SEPARATOR . ROOT_DIR . "/lib/PHPExcel/Classes/");
        require_once('tcpdf/config/lang/eng.php');
        require_once('tcpdf/tcpdf.php');
        include("webimage/WideImage.php");
        include("Zend/Loader/Autoloader.php");
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);

        //Load Configuration
        Zend_Registry::set("configSection", $configSection);
        //		$config = new Zend_Config_Ini(ROOT_DIR . "/application/config/config_funpea.ini", $configSection);
        $config = new Zend_Config_Ini(ROOT_DIR . "/application/config/config_sttrans.ini", $configSection);
        Zend_Registry::set("config", $config);

        date_default_timezone_set($config->date_default_timezone);
        $locale = new Zend_Locale("pt");
        Zend_Registry::set("Zend_Locale", $locale);

        //database
        $config_db = $config->db;
        $config_dblog = $config->db;
        if (isset($config->dblog)) {
            $config_dblog = $config->dblog;
        }

        /**
         * banco da versão anterior do sistema
         * para validações e migrações
         * remover após utilização
         */
        if (isset($config->dblegado)) {
            $config_dblegado = $config->dblegado;
            $dblegado = Zend_Db::factory($config_dblegado);
            Zend_Registry::set("dblegado", $dblegado);
        }

        $db = Zend_Db::factory($config_db);
        $dblog = Zend_Db::factory($config_dblog);

        if (isset($config->dbemtu)) {
            $dbemtu = Zend_Db::factory($config->dbemtu);
            Zend_Registry::set("dbemtu", $dbemtu);
        }

        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set("db", $db);
        Zend_Registry::set("dblog", $dblog);

        if (isset($config->dbfirebird->params->dbname) && file_exists($config->dbfirebird->params->dbname)) {
            try {
                $host = $config->dbfirebird->params->host . ':' . $config->dbfirebird->params->dbname;
                $dbibase = ibase_connect($host, $config->dbfirebird->params->username, $config->dbfirebird->params->password);
                Zend_Registry::set("dbibase", $dbibase);
            } catch (Exception $e) {
            }
        }
        $authDetails = array(
            'username' => $config->mail->username,
            'password' => $config->mail->password
        );
        $transport = new Zend_Mail_Transport_Smtp($config->mail->server, $authDetails);
        Zend_Mail::setDefaultTransport($transport);

        Zend_Json::$useBuiltinEncoderDecoder = true;
        /*
          $adapter = new Zend_File_Transfer_Adapter_Http();
          $adapter->setDestination($config->pasta_fotos);
         */
        Zend_Session::setOptions(array("throw_startup_exceptions" => FALSE));
    }

    public function configureFrontController()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setControllerDirectory(ROOT_DIR . "/application/controllers/");
        $frontController->registerPlugin(new Escola_Controller_Plugin_ViewSetup());
        $frontController->registerPlugin(new Escola_Controller_Plugin_ActionSetup(), 98);
        Zend_Controller_Action_HelperBroker::addPath(ROOT_DIR . "/application/controllers/helpers");
    }

    public function runApp()
    {
        $config = Zend_Registry::get("config");
        $this->configureFrontController();
        Zend_Layout::startMvc(array('layoutPath' => ROOT_DIR . "/application/views/layouts/"));
        $frontController = Zend_Controller_Front::getInstance();
        if (isset($config->frontController->baseurl)) {
            $frontController->setBaseUrl($config->frontController->baseurl);
        }
        $frontController->dispatch();
    }
}
