<?php

class Escola_Relatorio_Unimulher extends Escola_Relatorio
{

    public function __construct()
    {
        parent::__construct("relatorio_carteira_unimulher");
        $this->SetTopMargin(5);
        $this->SetAutoPageBreak(40);
    }

    public function header()
    { }

    public function Footer()
    { }

    public function toPDF($linhas)
    {
        if ($linhas) {
            $contador = 0;
            foreach ($linhas as $linha) {
                $txt_rg = $txt_matricula = $txt_curso = $txt_nome = $txt_emissao = $txt_validade = $txt_endereco = "";
                if (isset($linha["identidade"])) {
                    $txt_rg = $linha["identidade"];
                }
                if (isset($linha["numero_matricula"])) {
                    $txt_matricula = $linha["numero_matricula"];
                }
                $txt_curso = "Universidade da Mulher - UNIMULHER";
                if (isset($linha["nome_completo"])) {
                    $txt_nome = $linha["nome_completo"];
                }

                $txt_emissao = "23/02/2015";
                $txt_validade = "30/06/2016";

                if (isset($linha["nome_completo"])) {
                    $txt_nome = $linha["nome_completo"];
                }

                $endereco = array();
                if (isset($linha["endereco"])) {
                    $endereco[] = $linha["endereco"];
                }
                if (isset($linha["bairro"])) {
                    $txt = "Bairro: " . $linha["bairro"];
                    if (isset($linha["cidade_uf"])) {
                        $txt .= " - " . $linha["cidade_uf"];
                    }
                    $endereco[] = $txt;
                }
                if (isset($linha["cep"])) {
                    $endereco[] = "CEP: " . $linha["cep"];
                }
                if (isset($linha["fone_celular"])) {
                    $endereco[] = "Celular: " . $linha["fone_celular"];
                }
                if (isset($linha["e_mail"]) && trim($linha["e_mail"])) {
                    $endereco[] = "E-mail: " . $linha["e_mail"];
                }

                $txt_endereco = implode("<br />", $endereco);

                if (!$contador) {
                    ob_start();
                    $this->AddPage();
                    $this->css();
                }
                ?>
                <table class="tabela" cellpadding="2mm">
                    <tr>
                        <td style="width: 94mm; height: 60mm;" class="td_tabela">
                            <table cellpadding="2px">
                                <tr>
                                    <td style="border:1px solid #000; width: 25mm; height: 30mm;"></td>
                                    <td style="width: 65mm">
                                        <table>
                                            <tr>
                                                <td rowspan="3" width="12mm">
                                                    <img src="img_unifap.png" alt="" width="12mm" height="15mm" />
                                                </td>
                                                <td align="center" class="mini_top" width="52mm">UNIVERSIDADE FEDERAL DO AMAPÁ</td>
                                            </tr>
                                            <tr>
                                                <td align="center" class="mini_top">PRÓ-REITORIA DE EXTENSÃO E AÇÕES COMUNITÁRIAS</td>
                                            </tr>
                                            <tr>
                                                <td align="center" class="topo_carteira">IDENTIDADE ESTUDANTIL</td>
                                            </tr>
                                        </table>
                                        <table>
                                            <tr>
                                                <td style="line-height: 2px"></td>
                                            </tr>
                                        </table>
                                        <table cellpadding="1.2mm">
                                            <tr>
                                                <td align="left" width="20mm">C. Identidade:</td>
                                                <td style="border:1px solid #000;font-weight: bold;" width="72mm" width="43mm"><?php echo $txt_rg; ?></td>
                                            </tr>
                                        </table>
                                        <table>
                                            <tr>
                                                <td style="line-height: 2px"></td>
                                            </tr>
                                        </table>
                                        <table cellpadding="1.2mm">
                                            <tr>
                                                <td align="left" width="20mm">Matrícula:</td>
                                                <td style="border:1px solid #000;font-weight: bold;" width="72mm" width="43mm"><?php echo $txt_matricula; ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table>
                                <tr>
                                    <td height="0.5px"></td>
                                </tr>
                            </table>
                            <table cellpadding="1.5mm">
                                <tr>
                                    <td align="left" width="16mm">Curso:</td>
                                    <td style="border:1px solid #000; font-size: 10pt; font-weight: bold;" width="72mm"><?php echo $txt_curso; ?></td>
                                </tr>
                            </table>
                            <table>
                                <tr>
                                    <td height="0.5px"></td>
                                </tr>
                            </table>
                            <table cellpadding="1.5mm">
                                <tr>
                                    <td align="left" width="16mm">Nome do Aluno:</td>
                                    <td style="border:1px solid #000;" width="72mm"><?php echo $txt_nome; ?></td>
                                </tr>
                            </table>

                        </td>
                        <td style="width: 1mm; height: 60mm;" class="td_tabela"></td>
                        <td style="width: 94mm; height: 60mm;" class="td_tabela">
                            <table>
                                <tr>
                                    <td>
                                        <table cellpadding="4px">
                                            <tr>
                                                <td width="45mm">Emissão:</td>
                                                <td width="20mm">Validade:</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="unimulher">UNIMULHER</td>
                                </tr>
                            </table>
                            <table cellpadding="4px">
                                <tr>
                                    <td width="40px"></td>
                                    <td style="border:1px solid #000; font-size: 12pt;" width="30mm;" align="center"><?php echo $txt_emissao; ?></td>
                                    <td width="40px"></td>
                                    <td style="border:1px solid #000; font-size: 12pt;" width="30mm" align="center"><?php echo $txt_validade; ?></td>
                                </tr>
                            </table>
                            <table cellpadding="4px">
                                <tr>
                                    <td>Endereço:</td>
                                </tr>
                            </table>
                            <table cellpadding="4px">
                                <tr>
                                    <td width="40px"></td>
                                    <td style="border:1px solid #000;" width="71mm" height="25mm"><?php echo $txt_endereco; ?></td>
                                </tr>
                            </table>
                            <table>
                                <tr>
                                    <Td></Td>
                                </tr>
                            </table>
                            <table>
                                <tr>
                                    <td width="8mm"></td>
                                    <td width="74mm" style="border-bottom: 1px solid #000;"></td>
                                </tr>
                                <tr>
                                    <td width="8mm"></td>
                                    <td width="74mm" align="center" style="font-weight:bold">Prof. Dr. Eliane Superti</td>
                                </tr>
                                <tr>
                                    <td width="8mm"></td>
                                    <td width="74mm" align="center" style="font-weight:bold">REITORA DA UNIVERSIDADE FEDERAL DO AMAPÁ</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td class="separador"></td>
                    </tr>
                </table>
        <?php
                        $contador++;
                        if ($contador >= 4) {
                            $html = ob_get_contents();
                            ob_end_clean();
                            $this->writeHTML($html, true, false, true, false, '');

                            $contador = 0;
                        }
                    }
                    if ($contador) {
                        $html = ob_get_contents();
                        ob_end_clean();
                        $this->writeHTML($html, true, false, true, false, '');
                    }
                    $this->lastPage();
                    $this->salvar();
                }
                return false;
            }

            public function css()
            {
                ?>
        <style type="text/css">
            body,
            td {
                font-size: 8pt;
                font-family: Times new Roman;
            }

            .tabela {
                border: 2px double #000;
            }

            td.td_tabela {
                border: 1px solid #000;
            }

            td.separador {
                height: 0px;
                margin-top: 5px;
                margin-bottom: 5px;
            }

            td.mini_top {
                font-size: 7pt;
            }

            td.topo_carteira {
                font-size: 9pt;
                font-weight: bold;
            }

            .unimulher {
                font-size: 10pt;
                font-weight: bold;
                text-align: right;
                font-family: "Arial Black";
                font-style: italic;
                text-decoration: underline;
            }
        </style>
<?php
    }
}
