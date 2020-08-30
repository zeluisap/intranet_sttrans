<?php

class Escola_Relatorio_Default_Mo_FichaMo_Pdf extends Escola_Relatorio
{

    protected $motorista;

    public function __construct()
    {
        parent::__construct("relatorio");
        $this->SetTopMargin(5);
    }

    public function set_dados($dados)
    {
        parent::set_dados($dados);
        if (isset($dados["filename"])) {
            $this->setFilename($dados["filename"]);
        }
    }

    public function set_motorista($motorista)
    {
        $this->motorista = $motorista;
    }

    public function header()
    { }

    public function imprimir()
    {
        $this->AddPage();
        $txt_gt = $txt_matricula = $txt_nome = $txt_endereco = $txt_bairro = $txt_cep = $txt_celular = $txt_fone = "--";
        $txt_cpf = $txt_rg = $txt_cnh_validade = $txt_cnh_categoria = $txt_cnh_numero = "--";
        $motorista = $this->motorista;
        if ($motorista) {
            $txt_data_cadastro = Escola_Util::formatData($motorista->data_cadastro);
            $gt = $motorista->findParentRow("TbTransporteGrupo");
            if ($gt) {
                $txt_gt = $gt->toString();
            }
            if ($motorista->matricula) {
                $txt_matricula = $motorista->matricula;
            }
            $pm = $motorista->findParentRow("TbPessoaMotorista");
            if ($pm) {
                if ($pm->cnh_numero) {
                    $txt_cnh_numero = $pm->cnh_numero;
                    $uf = $pm->findParentRow("TbUf");
                    if ($uf) {
                        $txt_cnh_numero .= " - " . $uf->sigla;
                    }
                }
                $cnh_cat = $pm->findParentRow("TbCnhCategoria");
                if ($cnh_cat) {
                    $txt_cnh_categoria = $cnh_cat->toString();
                }
                $txt_cnh_validade = Escola_Util::formatData($pm->cnh_validade);
                $pf = $pm->findParentRow("TbPessoaFisica");
                if ($pf) {
                    if ($pf->nome) {
                        $txt_nome = $pf->nome;
                    }
                    if ($pf->cpf) {
                        $txt_cpf = Escola_Util::formatCpf($pf->cpf);
                    }
                    $identidade = $pf->mostrar_identidade();
                    if ($identidade) {
                        $txt_rg = $identidade;
                    }
                    $pessoa = $pf->findParentRow("TbPessoa");
                    if ($pessoa) {
                        $endereco = $pessoa->getEndereco();
                        if ($endereco) {
                            $txt = $endereco->logradouro;
                            if ($endereco->numero) {
                                $txt .= ", " . $endereco->numero;
                            }
                            $bairro = $endereco->findParentRow("TbBairro");
                            if ($bairro) {
                                $txt_bairro = $bairro->toString();
                                $municipio = $bairro->findParentRow("TbMunicipio");
                                if ($municipio) {
                                    $txt .= " - " . $municipio->toString();
                                    $uf = $municipio->findParentRow("TbUf");
                                    if ($uf) {
                                        $txt .= " / " . $uf->sigla;
                                    }
                                }
                            }
                            if (Escola_Util::limpaNumero($endereco->cep)) {
                                $txt_cep = Escola_Util::formatCep($endereco->cep);
                            }
                            $txt_endereco = $txt;
                        }
                        $celular = $pessoa->mostrarTelefones("C");
                        if ($celular) {
                            $txt_celular = $celular;
                        }
                        $fixo = $pessoa->mostrarTelefones("F");
                        if ($fixo) {
                            $txt_fone = $fixo;
                        }

                        $tb = new TbTransportePessoa();
                        $tps = $tb->listar(array("id_pessoa" => $pessoa->getId()));
                    }
                }
            }
        }
        ob_start();

        $this->css();
        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        if ($sistema) {
            $arquivo = false;
            if ($sistema) {
                $pj = $sistema->findParentRow("TbPessoaJuridica");
                $pessoa = $pj->pega_pessoa();
                $arquivo = $pessoa->getFoto();
            }
            ?>
            <table>
                <tr>
                    <td rowspan="4" width="80px">
                        <?php if ($arquivo) { ?>
                            <img src="<?php echo $arquivo->pegaNomeCompleto(); ?>" width="70px" height="50px" />
                        <?php } ?>
                    </td>
                    <td><?php echo $pj->sigla; ?> - <?php echo $pj->razao_social; ?></td>
                </tr>
                <?php
                            $endereco = $pessoa->getEndereco();
                            if ($endereco) {
                                $endereco1 = $endereco2 = $endereco3 = "";
                                $endereco1 = $endereco->logradouro;
                                if (Escola_Util::limpaNumero($endereco->cep)) {
                                    $endereco2 = Escola_Util::formatCep($endereco->cep);
                                }
                                $endereco2 .= " Fone(s): " . $pessoa->mostrarTelefones();
                                if ($endereco->numero) {
                                    $endereco1 .= ", " . $endereco->numero;
                                }
                                $bairro = $endereco->findParentRow("TbBairro");
                                if ($bairro) {
                                    $endereco1 .= " - " . $bairro->descricao;
                                    $municipio = $bairro->findParentRow("TbMunicipio");
                                    if ($municipio) {
                                        $endereco2 .= " - " . $municipio->toString();
                                        $uf = $municipio->findParentRow("TbUf");
                                        if ($uf) {
                                            $endereco2 .= "/" . $uf->sigla;
                                        }
                                    }
                                }
                                $endereco3 .= "C.N.P.J.: " . $pessoa->mostrar_documento();
                                ?>
                    <tr>
                        <td><?php echo $endereco1; ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $endereco2; ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $endereco3; ?></td>
                    </tr>
                <?php }
                            ?>
            </table>
            <br />
            <table>
                <tr>
                    <td class="titulo_ficha">DIRETORIA DE TRANSPORTES E TRÂNSITO</td>
                </tr>
            </table>
            <br />
            <table>
                <tr>
                    <td class="titulo_ficha">FICHA DE CADASTRO</td>
                </tr>
            </table>
            <br />
        <?php } ?>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th colspan="4">FICHA DE CADASTRO - MOTORISTA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="300px">
                        <table>
                            <tr>
                                <td class="rotulo">Grupo de Transporte:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_gt; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td width="100px">
                        <table>
                            <tr>
                                <td class="rotulo">Matrícula</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_matricula; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td colspan="2" width="273px">
                        <table>
                            <tr>
                                <td class="rotulo">Nome:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_nome; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td rowspan="2" colspan="2">
                        <table>
                            <tr>
                                <td class="rotulo">Endereço:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_endereco; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">Bairro:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_bairro; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">CEP:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_cep; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">Celular:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_celular; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">Fone Residencial:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_fone; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">C.P.F.:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_cpf; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td class="rotulo">R.G.:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_rg; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">Data Cadastro:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_data_cadastro; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <br />
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th colspan="3">CARTEIRA NACIONAL DE HABILITAÇÃO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">Categoria:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_cnh_categoria; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">Número:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_cnh_numero; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td class="rotulo">Validade:</td>
                            </tr>
                            <tr>
                                <td class="info"><?php echo $txt_cnh_validade; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php if ($tps && count($tps)) { ?>
            <br />
            <table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th colspan="5">TRANSPORTES VINCULADOS</th>
                    </tr>
                    <tr>
                        <th>Tipo Transporte</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Proprietário</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                                foreach ($tps as $tp) {
                                    $txt_tipo_transporte = $txt_tipo = $txt_codigo = $txt_proprietario = $txt_status = "--";
                                    $t = $tp->findParentRow("TbTransporte");
                                    if ($t) {
                                        $tg = $t->findParentRow("TbTransporteGrupo");
                                        if ($tg) {
                                            $txt_tipo_transporte = $tg->toString();
                                        }
                                        if ($t->codigo) {
                                            $txt_codigo = $t->codigo;
                                        }
                                        $prop = $t->pegaProprietario();
                                        if ($prop) {
                                            $txt_proprietario = $prop->toString();
                                        }
                                    }
                                    $tpt = $tp->findParentRow("TbTransportePessoaTipo");
                                    if ($tpt) {
                                        $txt_tipo = $tpt->toString();
                                    }
                                    $stauts = $tp->findParentRow("TbTransportePessoaStatus");
                                    if ($stauts) {
                                        $txt_status = $stauts->toString();
                                    }
                                    ?>
                        <tr>
                            <td><?php echo $txt_tipo_transporte; ?></td>
                            <td align="center"><?php echo $txt_codigo; ?></td>
                            <td align="center"><?php echo $txt_tipo; ?></td>
                            <td><?php echo $txt_proprietario; ?></td>
                            <td align="center"><?php echo $txt_status; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
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
            td.rotulo {
                font-size: 9pt;
            }

            td.info {
                font-size: 10pt;
                font-weight: bold;
                text-indent: 30px;
            }

            th {
                text-align: center;
                font-weight: bold;
                font-size: 12pt;
            }

            .titulo_ficha {
                font-size: 13pt;
                font-weight: bold;
                text-align: center;
            }
        </style>
<?php
    }
}
