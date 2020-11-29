<?php

namespace Escola\Servico;

use Boleto;
use Escola_Exception;
use Escola_Util;
use Escola_XmlUtil;
use Exception;
use SimpleXMLElement;
use TbBoleto;

class BoletoRegistro
{
    // public static $BB_AUTH_URL = "https://oauth.hm.bb.com.br/oauth/token";
    // public static $BB_REGISTRA_URL = "https://cobranca.homologa.bb.com.br:7101/registrarBoleto";
    // public static $BB_CLIENT_ID = "eyJpZCI6IjgwNDNiNTMtZjQ5Mi00YyIsImNvZGlnb1B1YmxpY2Fkb3IiOjEwOSwiY29kaWdvU29mdHdhcmUiOjEsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxfQ";
    // public static $BB_CLIENT_SECRET = "eyJpZCI6IjBjZDFlMGQtN2UyNC00MGQyLWI0YSIsImNvZGlnb1B1YmxpY2Fkb3IiOjEwOSwiY29kaWdvU29mdHdhcmUiOjEsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxLCJzZXF1ZW5jaWFsQ3JlZGVuY2lhbCI6MX0";

    public static $BB_AUTH_URL = "https://oauth.bb.com.br/oauth/token";
    public static $BB_REGISTRA_URL = "https://cobranca.bb.com.br:7101/registrarBoleto";
    public static $BB_CLIENT_ID = "eyJpZCI6IjMyOGI3MDQtNmIiLCJjb2RpZ29QdWJsaWNhZG9yIjowLCJjb2RpZ29Tb2Z0d2FyZSI6MTE1MzYsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxfQ";
    public static $BB_CLIENT_SECRET = "eyJpZCI6ImQzYjMyZjMtMmVhIiwiY29kaWdvUHVibGljYWRvciI6MCwiY29kaWdvU29mdHdhcmUiOjExNTM2LCJzZXF1ZW5jaWFsSW5zdGFsYWNhbyI6MSwic2VxdWVuY2lhbENyZWRlbmNpYWwiOjEsImFtYmllbnRlIjoicHJvZHVjYW8iLCJpYXQiOjE1OTY1NDY4NjU1OTJ9";

    private $auth = null;
    private $client = null;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            "verify" => false
        ]);
    }

    public static function registrarTodos()
    {
        $obj = new BoletoRegistro();
        return $obj->registrarTodosBoletos();
    }

    public static function registrarBoleto($boleto = null)
    {

        try {
            $obj = new BoletoRegistro();
            $response = $obj->registrar($boleto);

            $codigoRetornoPrograma = Escola_Util::valorOuNulo($response, "codigoRetornoPrograma");
            if (!$codigoRetornoPrograma) {
                // throw new Escola_Exception("Não foi possível gerar o boleto.");
            }

            if ($codigoRetornoPrograma > 0) {
                if ($codigoRetornoPrograma == 92) { //BOLETO JÁ REGISTRADO
                    return $response;
                }

                $textoMensagemErro = Escola_Util::valorOuNulo($response, "textoMensagemErro");
                if (!$textoMensagemErro) {
                    throw new Escola_Exception("Não foi possível gerar o boleto.");
                }

                throw new Escola_Exception($textoMensagemErro);
            }

            return $response;
        } catch (Exception $ex) {
            throw $ex;
            throw new Escola_Exception("Falha ao tentar registrar o boleto, tente novamente.");
        }
    }

    public function registrarTodosBoletos()
    {
        $boleto = TbBoleto::pegaPorId(6743);
        return $this->registrarBoleto($boleto);
    }

    public function getAuth()
    {

        $auth_base_64 = base64_encode(self::$BB_CLIENT_ID . ":" . self::$BB_CLIENT_SECRET);

        $response = $this->client->post(self::$BB_AUTH_URL, [
            "headers" => [
                "Content-Type" => "application/x-www-form-urlencoded",
                "Authorization" => "Basic " . $auth_base_64
            ],
            'form_params' => [
                "grant_type" => "client_credentials",
                "scope" => "cobranca.registro-boletos"
            ]
        ]);

        $conteudo = $response->getBody()->getContents();

        $auth = json_decode($conteudo);

        $this->auth = $auth;
    }

    public function registrar($boleto)
    {

        if (!$boleto) {
            throw new Escola_Exception("Nenhum boleto informado!");
        }

        $dados = $this->extrair($boleto);
        // var_dump($dados);
        // die();

        $fields = [];
        foreach ($dados as $chave => $valor) {
            $fields["sch:" . $chave] = $valor;
        }

        $xml_array = [
            "soapenv:Envelope" => [

                "attrs" => [
                    "xmlns:soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
                    "xmlns:sch" => "http://www.tibco.com/schemas/bws_registro_cbr/Recursos/XSD/Schema.xsd"
                ],

                "valor" => [
                    "soapenv:Header" => "",
                    "soapenv:Body" => [
                        "sch:requisicao" => $fields
                    ]

                ]
            ]
        ];

        $xml = Escola_XmlUtil::arrayToXml($xml_array);

        $this->getAuth();

        $authorization = "Bearer " . $this->auth->access_token;

        $response = $this->client->post(self::$BB_REGISTRA_URL, [

            "headers" => [
                "Content-Type" => "text/xml;charset=UTF-8",
                "Authorization" => $authorization,
                "SOAPAction" => "registrarBoleto"
            ],

            "body" => $xml
        ]);

        $conteudo = $response->getBody()->getContents();

        $xml = new SimpleXMLElement($conteudo);
        $xml->registerXPathNamespace('ns0', 'http://www.tibco.com/schemas/bws_registro_cbr/Recursos/XSD/Schema.xsd');

        $array = [];

        foreach (self::getFieldResponse() as $field) {
            $valor = $this->xmlProcurar($xml, "ns0:" . $field);
            if (!$valor) {
                continue;
            }
            $array[$field] = $valor;
        }

        return $array;
    }


    public function xmlProcurar($xml, $path)
    {

        $paths = $xml->xpath("//{$path}");
        if (!($paths && count($paths))) {
            return null;
        }

        $path = $paths[0];
        if (!isset($path[0])) {
            return null;
        }

        $valor = (string)$path[0];

        return trim($valor);
    }

    private static function getFieldResponse()
    {
        return [
            "siglaSistemaMensagem",
            "codigoRetornoPrograma",
            "nomeProgramaErro",
            "textoMensagemErro",
            "numeroPosicaoErroPrograma",
            "codigoTipoRetornoPrograma",
            "textoNumeroTituloCobrancaBb",
            "numeroCarteiraCobranca",
            "numeroVariacaoCarteiraCobranca",
            "codigoPrefixoDependenciaBeneficiario",
            "numeroContaCorrenteBeneficiario",
            "codigoCliente",
            "linhaDigitavel",
            "codigoBarraNumerico",
            "codigoTipoEnderecoBeneficiario",
            "nomeLogradouroBeneficiario",
            "nomeBairroBeneficiario",
            "nomeMunicipioBeneficiario",
            "codigoMunicipioBeneficiario",
            "siglaUfBeneficiario",
            "codigoCepBeneficiario",
            "indicadorComprovacaoBeneficiario",
            "numeroContratoCobranca",
        ];
    }

    private static function getFieldRequest()
    {
        return [
            "numeroConvenio" => "123456",
            "numeroCarteira" => "17",
            "numeroVariacaoCarteira" => "19",
            "codigoModalidadeTitulo" => "1",
            "dataEmissaoTitulo" => "01.03.2020",
            "dataVencimentoTitulo" => "21.11.2020",
            "valorOriginalTitulo" => "0",
            "codigoTipoDesconto" => "0",
            "dataDescontoTitulo" => "",
            "percentualDescontoTitulo" => "",
            "valorDescontoTitulo" => "",
            "valorAbatimentoTitulo" => "",
            "quantidadeDiaProtesto" => "0",
            "codigoTipoJuroMora" => "0",
            "percentualJuroMoraTitulo" => "",
            "valorJuroMoraTitulo" => "",
            "codigoTipoMulta" => "0",
            "dataMultaTitulo" => "",
            "percentualMultaTitulo" => "",
            "valorMultaTitulo" => "",
            "codigoAceiteTitulo" => "N",
            "codigoTipoTitulo" => "2",
            "textoDescricaoTipoTitulo" => "DUPLICATA",
            "indicadorPermissaoRecebimentoParcial" => "N",
            "textoNumeroTituloBeneficiario" => "987654321987654",
            "codigoTipoContaCaucao" => "0",
            "textoNumeroTituloCliente" => "00026254440000000102",
            "textoMensagemBloquetoOcorrencia" => "Pagamento disponível ate a data de vencimento",
            "codigoTipoInscricaoPagador" => "2",
            "numeroInscricaoPagador" => "00000000000191",
            "nomePagador" => "MERCADO TESTE",
            "textoEnderecoPagador" => "RUA SEM NOME",
            "numeroCepPagador" => "12345678",
            "nomeMunicipioPagador" => "BRASILIA",
            "nomeBairroPagador" => "SIA",
            "siglaUfPagador" => "DF",
            "textoNumeroTelefonePagador" => "",
            "codigoTipoInscricaoAvalista" => "",
            "numeroInscricaoAvalista" => "",
            "nomeAvalistaTitulo" => "",
            "codigoChaveUsuario" => "1",
            "codigoTipoCanalSolicitacao" => "5",
        ];
    }

    private function extrair(Boleto $boleto = null)
    {
        if (!$boleto) {
            throw new Escola_Exception("Falha ao extrair dados do boleto.");
        }

        if (is_array($boleto)) {
            return array_merge(self::getFieldRequest(), $boleto);
        }

        $bancoConvenio = $boleto->getBancoConvenio();
        if (!$bancoConvenio) {
            throw new Escola_Exception("Boleto não vinculado a um convênio.");
        }

        $numeroConvenio = $bancoConvenio->convenio;
        if (!$numeroConvenio) {
            throw new Escola_Exception("Número do convênio não disponível.");
        }

        try {
            $dt = new \DateTime($boleto->data_criacao);
            $dataEmissaoTitulo = $dt->format("d.m.Y");
        } catch (Exception $ex) {
            throw new Escola_Exception("Falha ao obter data de emissão do título.");
        }

        try {
            $dt = new \DateTime($boleto->data_vencimento);
            $dataVencimentoTitulo = $dt->format("d.m.Y");
        } catch (Exception $ex) {
            throw new Escola_Exception("Falha ao obter data de vencimento do título.");
        }

        $valor = $boleto->pegaValor();

        $nossoNumero = $boleto->pegaNossoNumero();

        $nossoNumero = str_pad($nossoNumero, 20, "0", STR_PAD_LEFT);

        $pessoa = $boleto->pegaPessoa();
        if (!$pessoa) {
            throw new Escola_Exception("Pagador não identificado.");
        }

        if ($pessoa->pf()) {
            $codigoTipoInscricaoPagador = "1";
        } elseif ($pessoa->pj()) {
            $codigoTipoInscricaoPagador = "2";
        } else {
            throw new Escola_Exception("Falha ao identificar o tipo do pagador.");
        }

        $numeroInscricaoPagador = Escola_Util::limpaNumero($pessoa->mostrar_documento());
        // $filho = $pessoa->pegaPessoaFilho();
        $nomePagador = $pessoa->mostrar_nome();

        $endereco = $pessoa->getEndereco();
        if (!$endereco) {
            throw new Escola_Exception("Endereço do pagador não disponível.");
        }

        $endereco = $endereco->toArray();

        $textoEnderecoPagador = Escola_Util::valorOuNulo($endereco, "logradouro") . ", N.: " . Escola_Util::valorOuNulo($endereco, "numero");
        $numeroCepPagador = Escola_Util::valorOuNulo($endereco, "cep");
        $numeroCepPagador = Escola_Util::valorOuNulo($endereco, "cep");

        $nomeMunicipioPagador = Escola_Util::valorOuNulo($endereco, "bairro->municipio->descricao");
        $nomeBairroPagador = Escola_Util::tamanhoMenorOuCorta(Escola_Util::valorOuNulo($endereco, "bairro->descricao"), 20);

        $siglaUfPagador = Escola_Util::valorOuNulo($endereco, "bairro->municipio->uf->sigla");

        $boleto_id = $boleto->getId();

        // $textoNumeroTelefonePagador = $pessoa->mostrarTelefones();

        $dados = [
            "numeroConvenio" => $numeroConvenio,
            "numeroCarteira" => "17",
            "numeroVariacaoCarteira" => "27",
            "dataEmissaoTitulo" => $dataEmissaoTitulo,
            "dataVencimentoTitulo" => $dataVencimentoTitulo,
            "valorOriginalTitulo" => $valor,
            "codigoTipoJuroMora" => "2",
            "percentualJuroMoraTitulo" => "1",
            "textoNumeroTituloBeneficiario" => $boleto_id,
            "textoNumeroTituloCliente" => $nossoNumero,
            "codigoTipoInscricaoPagador" => $codigoTipoInscricaoPagador,
            "numeroInscricaoPagador" => $numeroInscricaoPagador,
            "nomePagador" => $nomePagador,
            "textoEnderecoPagador" => $textoEnderecoPagador,
            "numeroCepPagador" => $numeroCepPagador,
            "nomeMunicipioPagador" => $nomeMunicipioPagador,
            "nomeBairroPagador" => $nomeBairroPagador,
            "siglaUfPagador" => $siglaUfPagador,
            // "textoNumeroTelefonePagador" => $textoNumeroTelefonePagador,
        ];

        return array_merge($this->getFieldRequest(), $dados);
    }
}
