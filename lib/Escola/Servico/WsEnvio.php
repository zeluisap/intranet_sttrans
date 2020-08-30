<?php

namespace Escola\Servico;

use Escola_Util;
use Exception;
use ReflectionClass;

class WsEnvio
{

    private $params = null;

    public static function getInstance($options)
    {
        $servico = Escola_Util::valorOuNulo($options, "servico");
        $recurso = Escola_Util::valorOuNulo($options, "recurso");
        $params = Escola_Util::valorOuNulo($options, "params");

        if (!($servico && $recurso)) {
            throw new Exception("Falha ao criar instância do serviço.");
        }

        $servico = implode("", array_map("ucfirst", explode("-", $servico)));
        $recurso = implode("", array_map("ucfirst", explode("-", $recurso)));

        $class_name = "Tucujuris\\Servico\\Ws\\{$servico}\\{$recurso}";
        if (!class_exists($class_name)) {
            throw new Exception("Falha ao criar instância do serviço.");
        }

        $instance = new $class_name();
        $instance->setParams($params);

        return $instance;
    }

    public static function rodarRecurso($options)
    {

        $instance = self::getInstance($options);
        if (!$instance) {
            throw new Exception("Falha ao criar instância do serviço.");
        }

        $metodo = Escola_Util::valorOuNulo($options, "metodo");
        if (!$metodo) {
            $metodo = "enviar";
        }

        $metodo = implode("", array_map("ucfirst", explode("-", $metodo)));
        $metodo = lcfirst($metodo);

        if (!method_exists($instance, $metodo)) {
            throw new Exception("Falha ao rodar instância do serviço.");
        }

        return $instance->$metodo();
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    protected function getNomeRecurso()
    {
        $reflect = new ReflectionClass($this);
        $class_name = $reflect->getShortName();
        if (!$class_name) {
            return null;
        }
        return lcfirst($class_name);
    }

    protected function getErrorMessage($ex)
    {
        $errorMessage = "Falha ao executar operação, entre em contato com o suporte.";

        if (!$ex->hasResponse()) {
            return $errorMessage;
        }

        $response = $ex->getResponse();
        if (!$response) {
            return $errorMessage;
        }

        $body = $response->getBody();
        if (!$body) {
            return $errorMessage;
        }

        $txt = $body->getContents();
        if (!$txt) {
            return $errorMessage;
        }

        $object = new \simpleXMLElement($txt);

        $path = $object->xpath("//faultstring");
        if (!count($path)) {
            return $errorMessage;
        }

        $path = $path[0];

        if (!isset($path[0])) {
            return $errorMessage;
        }

        $error = $path[0];
        if (!$error) {
            return $errorMessage;
        }

        throw new Exception($error);
    }

    public function getResponseObject($response)
    {
        $body = $response->getBody();
        if (!$body) {
            return null;
        }

        $txt = $body->getContents();
        if (!$txt) {
            return null;
        }

        $txt = utf8_encode($txt);

        $mensagem = $this->getMensagem($txt);
        if (!$mensagem) {
            $mensagem = "Sem mensagem de resposta.";
        }

        return [
            "sucesso" => $this->getSucesso($txt),
            "mensagem" => $mensagem
        ];
    }

    public function getSucesso($txt)
    {
        return $this->procurarResponse($txt, "resposta/sucesso");
    }

    public function getMensagem($txt)
    {
        return $this->procurarResponse($txt, "resposta/mensagem");
    }

    public function procurarResponse($txt, $path)
    {

        $object = new \simpleXMLElement($txt);

        $paths = $object->xpath("//{$path}");
        if (!count($paths)) {
            return null;
        }

        $path = $paths[0];
        if (!isset($path[0])) {
            return null;
        }

        $valor = (string)$path[0];

        return $valor;
    }
}
