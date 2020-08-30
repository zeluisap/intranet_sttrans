<?php

include "bootstrap.php";
$configSection = getenv('TESTE_CONFIG') ? getenv('TESTE_CONFIG') : "general";
$bootstrap = new Bootstrap($configSection);

function getController()
{
    $c = "Desenvolvimento";
    $valor = Escola_Util::getParametro("-c");
    if (!$valor) {
        return $c;
    }

    $valor = explode("_", $valor);
    $valor = array_map("ucfirst", $valor);
    $valor = implode("", $valor);

    return $c . "_" . $valor;
}

function carregaConfiguracao()
{
    global $argv;

    $config = new stdClass();
    $config->controller = getController();
    $config->processo = Escola_Util::getParametro("-p");

    if (!$config->processo) {
        $config->processo = Escola_Util::getParametro("-c");
    }

    $config->filename = Escola_Util::getParametro("-f");

    return $config;
}

$config = carregaConfiguracao();

try {
    $controller_name = "{$config->controller}Controller";

    $controller_filename = str_replace("_", "/", $controller_name);
    include_once "controllers/{$controller_filename}.php";

    $processo = $config->processo;

    if (empty($processo)) {
        throw new Exception("Falha! Nenhum Processo Informado!");
    }

    $controller = new $controller_name(new Zend_Controller_Request_Simple(), new Zend_Controller_Response_Cli());
    if (!method_exists($controller, $processo)) {
        throw new Exception("Falha! Processo Nao Existe!");
    }

    $controller->$processo($filename);

    echo PHP_EOL . PHP_EOL;
} catch (Exception $ex) {
    $erro = new stdClass();
    $erro->erro = $ex->getMessage();
    print_r($erro);
    die();
}
