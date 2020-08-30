<?php

class Escola_Relatorio_Credencial_Idoso extends Escola_Relatorio_Credencial
{

    public function __construct()
    {
        parent::__construct("relatorio_credencial_idoso");
    }

    public function toPDF()
    {
        $txt_numero = $txt_data_validade = $txt_unidade_federativa = $txt_municipio = $txt_nome_beneficiario = $txt_orgao_sigla = $txt_orgao = "";
        if (!$this->credencial) {
            throw new Exception("Falha ao Executar Operação, Credencial Inválida!");
        }
        $credencial = $this->credencial;
        $numero = $credencial->mostrarNumero();
        if ($numero) {
            $txt_numero = $numero;
        }
        $txt_data_validade = Escola_Util::formatData($credencial->data_validade);

        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        if (!$sistema) {
            throw new Exception("Falha ao Executar Operação, Dados Inválidos!");
        }
        $pj = $sistema->findParentRow("TbPessoaJuridica");
        if (!$pj) {
            throw new Exception("Falha ao Executar Operação, Dados Inválidos!");
        }

        $orgao = array();
        if ($pj->razao_social) {
            $orgao[] = $pj->razao_social;
        }
        if ($pj->sigla) {
            $txt_orgao_sigla = $pj->sigla;
            $orgao[] = $pj->sigla;
        }

        if (count($orgao)) {
            $txt_orgao = implode(" - ", $orgao);
        }

        $txt_unidade_federativa = "AMAPÁ";
        $txt_municipio = "SANTANA";

        $pf = $credencial->pegaBeneficiario();
        if ($pf) {
            $txt_nome_beneficiario = $pf->toString();
        }

        $txt_emitido = $txt_data_emissao = "--";
        $txt_data_emissao = date("d/m/Y H:i:s");

        $usuario = TbUsuario::pegaLogado();
        if ($usuario) {
            $txt_emitido = $usuario->toString();
        }

        $this->AddPage();
        $this->Image(ROOT_DIR . "/public/img/idoso.png", 50, 30, 120, 80, "PNG");
        ob_start();
        $this->css();
        ?>
        <table border="1" cellspacing="10" cellpadding="5">
            <tr>
                <td width="60"><img src="<?php echo Escola_Util::getBaseUrl(); ?>/public/img/deficiente_texto_idoso.png" width="60px" height="500px" align="left" /></td>
                <td width="580">
                    <table>
                        <tr>
                            <td rowspan="3" width="60px"><img src="<?php echo Escola_Util::getBaseUrl(); ?>/public/img/brasil.gif" width="60px" align="left" /></td>
                            <td align="center" width="440px">REPÚBLICA FEDERATIVA DO BRASIL</td>
                            <td rowspan="3" width="60px"><img src="<?php echo Escola_Util::getBaseUrl(); ?>/public/img/sttrans.png" width="60px" align="left" /></td>
                        </tr>
                        <tr>
                            <td align="center">CONSELHO NACIONAL DE TRÂNSITO</td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td align="center" style="font-weight: bold; font-size: 20pt;">ESTACIONAMENTO VAGA ESPECIAL</td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td align="center">CONFORME RESOLUÇÃO No.: 303/2008 DO CONTRAN</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-weight: bold; font-size: 20pt;">NÚMERO DO REGISTRO: <?php echo $txt_numero; ?></td>
                        </tr>
                    </table>
                    <hr />
                    <table>
                        <tr>
                            <td></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td style="font-weight: bold; font-size: 15pt;">DATA DE VALIDADE: <?php echo $txt_data_validade; ?></td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; font-size: 15pt;">UNIDADE DA FEDERAÇÃO: <?php echo $txt_unidade_federativa; ?></td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; font-size: 15pt;">MUNICÍPIO: <?php echo $txt_municipio; ?></td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td>ÓRGÃO EXPEDIDOR: <?php echo $txt_orgao; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td></td>
            </tr>
        </table>
        <table border="1" cellpadding="5">
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="font-size: 13pt; font-weight: bold;">NOME DO BENEFICIÁRIO: <?php echo $txt_nome_beneficiario; ?></td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td align="center">REGRAS DE UTILIZAÇÃO</td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td class="font_10">1. A autorização concedida por meio deste cartão somente terá validade se o mesmo for apresentado no original e preencher as seguintes condições:</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10 indent_1">1.1. Estiver colocado sobre o painel do veículo, com frente voltado para cima;</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10 indent_1">1.2. For apresentado à autoridade de trânsito ou aos seus agentes, sempre que solicitado.</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10">2. Este cartão de autorização poderá ser recolhido e o ato da suspenso ou cassado, a qualquer tempo, a critério do órgão de trânsito, especialmente se verificada irregularidade em sua utilização, considerando-se como tal, dentre outros:</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10 indent_1">2.1. O empréstimo do cartão a terceiros;</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10 indent_1">2.2. O uso de cópia do cartão, efetuada por qualquer processo;</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10 indent_1">2.3. O porte do cartão com rasuras ou falsificado;</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10 indent_1">2.4. O uso do cartão em desacordo com as disposições nele contidas ou na legislação pertinente, especialmente se constatado pelo agente que o veículo por ocasião da utilização vaga especial, não serviu para o transporte do idoso:</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10 indent_1">2.5. O uso do cartão com validade vencida.</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10">3. A presente autorização somente é válida para estacionar nas vagas devidamente sinalizadas com a legenda idoso.</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10">4. Esta autorização também permite o uso em vagas de Estacionamento Rotativo Regulamentado, gratuito ou pago, sinalizadas com o Símbolo Internacional de Acesso, sendo obrigatória a utilização conjunta do Cartão do Estacionamento, bem como a obediência às suas normas de utilização.</td>
                                    </tr>
                                    <tr>
                                        <td class="font_10">5. O desrespeito ao disposto neste cartão de autorização, bem como às demais regras de trâsinto e a sinalização local, sujeitará o infrator as medidas administrativas, penalidades e pontuações previstas em lei.</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td align="center" style="font-weight: bold;">Superintendente - <?php echo $txt_orgao_sigla; ?></td>
                            <td align="center" style="font-weight: bold;">Diretor - <?php echo $txt_orgao_sigla; ?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td style="text-align: center; font-size: 12pt;">Emissão: <strong><?php echo date("d/m/Y"); ?></strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td style="font-size: 9pt;">Emitido Por: <?php echo $txt_emitido; ?>. Em: <?php echo $txt_data_emissao; ?></td>
            </tr>
        </table>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');
        $this->lastPage();
        $this->download();
    }
}
