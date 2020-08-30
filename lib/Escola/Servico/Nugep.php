<?php

namespace Escola\Servico;

use Escola_Util;
use Escola_XmlUtil;
use Exception;
use GuzzleHttp\Exception\RequestException;

class Nugep extends WsEnvio
{

    protected function arrayToXml($array)
    {

        $recurso = $this->getNomeRecurso();
        if (!$recurso) {
            throw new Exception("Nome do recurso não identificado.");
        }

        $array = [
            "x:Envelope" => [

                "attrs" => [
                    "xmlns:x" => "http://schemas.xmlsoap.org/soap/envelope/",
                    "xmlns:ws" => "http://ws.bnpr.cnj.jus.br/"
                ],

                "valor" => [
                    "x:Header" => "",
                    "x:Body" => [
                        "ws:{$recurso}" => $array
                    ]

                ]
            ]
        ];

        $root = Escola_XmlUtil::arrayToXml($array);

        $object = new \simpleXMLElement($root);

        return $object->asXML();
    }

    protected function enviarRecurso($array)
    {

        $xml = $this->arrayToXml($array);

        $client = new \GuzzleHttp\Client();

        try {

            $response = $client->post(Configuracao::$SOAP_NUGEP_SERVER, [
                "auth" => [
                    Configuracao::$SOAP_NUGEP_USERNAME,
                    Configuracao::$SOAP_NUGEP_PASSWORD
                ],
                "headers" => [
                    "Content-Type" => "application/xml"
                ],
                "body" => $xml
            ]);

            $result = $this->getResponseObject($response);

            $sucesso = json_decode(Escola_Util::valorOuNulo($result, "sucesso"));
            $mensagem = Escola_Util::valorOuNulo($result, "mensagem");
            if (!$sucesso) {
                throw new Exception("Falha ao enviar recurso, erro: {$mensagem}.");
            }

            return [
                "sucesso" => $sucesso,
                "mensagem" => $mensagem
            ];
        } catch (RequestException $ex) {
            throw new Exception($this->getErrorMessage($ex));
        }
    }
}
