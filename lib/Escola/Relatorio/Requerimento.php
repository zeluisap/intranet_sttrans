<?php

class Escola_Relatorio_Requerimento extends Escola_Relatorio
{

    public function __construct()
    {
        $filename = "relatorio_requerimento";
        parent::__construct($filename);
        $this->SetTopMargin(45);
    }

    public function imprimir($requerimento)
    {

        $txt_nome = $txt_cpf_cnpj = $txt_rg = $txt_orgao_emissor_uf = $txt_endereco = $txt_complemento = $txt_numero = $txt_bairro = $txt_cep = $txt_cidade = $txt_telefone = "--";

        $txt_cidade_uf = "Santana - AP";
        $txt_data_dia = date("d");
        $txt_data_mes = Escola_Util::pegaMes(date("n"));
        $txt_data_ano = date("Y");

        $txt_requerimento_numero = $requerimento->mostrarNumero();

        $req = $requerimento->toArray();

        $txt_nome = Escola_Util::valorOuCoalesce($req, "pessoa->nome", "--");

        $cpf_cnpj = Escola_Util::valorOuNulo($req, "pessoa->cpf_cnpj");
        if ($cpf_cnpj) {
            $txt_cpf_cnpj = Escola_Util::formatCpfCnpj($cpf_cnpj) ?? '--';
        }
        $txt_rg = Escola_Util::valorOuCoalesce($req, "pessoa->identidade_numero", "--");

        $orgao_expedidor = Escola_Util::valorOuNulo($req, "pessoa->identidade_orgao_expedidor");
        if ($orgao_expedidor) {
            $txt = [$orgao_expedidor];
            $identidade_uf = Escola_Util::valorOuNulo($req, "pessoa->identidade_uf->sigla");
            if ($identidade_uf) {
                $txt[] = $identidade_uf;
            }
            $txt_orgao_emissor_uf = implode("/", $txt);
        }

        $txt_endereco = Escola_Util::valorOuCoalesce($req, "pessoa->endereco->logradouro", "--");
        $txt_numero = Escola_Util::valorOuCoalesce($req, "pessoa->endereco->numero", "--");
        $txt_complemento = Escola_Util::valorOuCoalesce($req, "pessoa->endereco->complemento", "--");
        $txt_bairro = Escola_Util::valorOuCoalesce($req, "pessoa->endereco->bairro->descricao", "--");
        $txt_cidade = Escola_Util::valorOuCoalesce($req, "pessoa->endereco->bairro->municipio->descricao", "--");

        $cep = Escola_Util::valorOuNulo($req, "pessoa->endereco->cep");
        if ($cep) {
            $txt_cep = Escola_Util::formatCep($cep);
        }

        $txt = [];
        if ($telefone_fixo = Escola_Util::valorOuNulo($req, "pessoa->telefone_fixo")) {
            $txt[] = $telefone_fixo;
        }
        if ($telefone_celular = Escola_Util::valorOuNulo($req, "pessoa->telefone_celular")) {
            $txt[] = $telefone_celular;
        }
        if (count($txt)) {
            $txt_telefone = implode(", ", $txt);
        }

        $itens = Escola_Util::valorOuNulo($req, "itens");
        if (!$itens) {
            throw new Escola_Exception("Falha ao carregar ítens para imprimir.");
        }

        $txt_criacao_data = "___/___/_______";
        $data_criacao = Escola_Util::valorOuNulo($req, "data_criacao");
        if ($data_criacao) {
            $txt_criacao_data = Escola_Util::formatData($data_criacao);
        }
        $txt_criacao_hora = Escola_Util::valorOuCoalesce($req, "hora_criacao", "___:___");

        $this->addPage();
        $this->css();
?>
        <div class="centro subtitulo">Ao Ilmo. Senhor Superintendente de Transporte e Trânsito de Santana - STTRANS</div>
        <div class="centro titulo">REQUERIMENTO: <?= $txt_requerimento_numero ?></div>
        <table border="1">
            <tr>
                <td colspan="3">
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">NOME:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="97%"><?= $txt_nome ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">CNPJ/CPF:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="92%"><?= $txt_cpf_cnpj ?></td>
                        </tr>
                    </table>

                </td>
                <td>
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">RG:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="92%"><?= $txt_rg ?></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">ÓRGÃO EMISSOR/UF:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="92%"><?= $txt_orgao_emissor_uf ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">ENDEREÇO:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="97%"><?= $txt_endereco ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">COMPLEMENTO:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="97%"><?= $txt_complemento ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">NÚMERO:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="92%"><?= $txt_numero ?></td>
                        </tr>
                    </table>
                </td>
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">BAIRRO:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="96%"><?= $txt_bairro ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">CEP:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="92%"><?= $txt_cep ?></td>
                        </tr>
                    </table>
                </td>
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">CIDADE:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="96%"><?= $txt_cidade ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <table>
                        <tr>
                            <td class="table_label" colspan="2">TELEFONE(S) PARA CONTATO:</td>
                        </tr>
                        <tr>
                            <td width="15px"></td>
                            <td width="97%"><?= $txt_telefone ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="paragrafo">Venho respeitosamente solicitar a Vossa Senhoria que autorize setor competente fornecer o (s) documento (s) abaixo discriminado (s), comprometendo-me cumprir todas as exigências na forma da Lei.</div>

        <div class="centro titulo">SOLICITAÇÃO</div>

        <ul>
            <?php foreach ($itens as $item) {
                $txt_descricao = Escola_Util::valorOuNulo($item, "descricao");
                $txt_obs = Escola_Util::valorOuNulo($item, "obs");
            ?>
                <li>
                    <table>
                        <tr>
                            <td><?= $txt_descricao ?></td>
                        </tr>
                        <?php if ($txt_obs) : ?>
                            <tr>
                                <td class="texto-menor"><?= $txt_obs ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </li>
            <?php } ?>
        </ul>

        <table>
            <tr>
                <td width="30%">
                    <table cellpadding="3px">
                        <tr>
                            <td>Criado em:</td>
                        </tr>
                        <tr>
                            <td>Data: <?= $txt_criacao_data ?></td>
                        </tr>
                        <tr>
                            <td>Hora: <?= $txt_criacao_hora ?></td>
                        </tr>
                    </table>
                </td>
                <td width="70%">
                    <div></div>
                    <table>
                        <tr>
                            <td class="centro">____________________________________________</td>
                        </tr>
                        <tr>
                            <td class="centro"><?= $txt_nome ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td width="40%">
                    <div></div>
                    <table>
                        <tr>
                            <td class="centro">______________________________</td>
                        </tr>
                        <tr>
                            <td class="centro">Servidor STTRANS</td>
                        </tr>
                    </table>
                </td>
                <td width="60%" class="direita">
                    <div></div>
                    <?= $txt_cidade_uf ?>, <?= $txt_data_dia ?> de <?= $txt_data_mes ?> de <?= $txt_data_ano ?>.
                </td>
            </tr>
        </table>

    <?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');

        $this->lastPage();
        $this->download();
    }

    public function css()
    {
    ?>
        <style type="text/css">
            div,
            td {
                font-size: 10pt;
            }

            div.direita,
            .direita {
                text-align: right;
            }

            div.centro,
            .centro {
                text-align: center;
            }

            div.negrito {
                font-weight: bold;
            }

            div.titulo {
                font-size: 14pt;
                text-decoration: underline;
                font-weight: bold;
            }

            .table_label {
                font-weight: bold;
            }

            .paragrafo {
                text-indent: 15px;
                text-align: justify;
            }

            .texto-menor {
                text-align: justify;
                font-size: 9pt;
                color: #404040;
            }
        </style>
<?php
    }
}
