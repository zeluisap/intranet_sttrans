<?php

class Escola_Relatorio_Default_Tra_Ficha extends Escola_Relatorio
{

    protected $transporte;

    public function set_transporte(Transporte $transporte)
    {
        parent::set_transporte($transporte);
        $this->setFilename("ficha_cadastro_" . $this->transporte->codigo);
    }

    public function get_transporte()
    {
        return $this->transporte;
    }

    public function __construct()
    {
        parent::__construct("ficha_cadastro");
    }

    public function toPDF()
    {
        if (!$this->transporte->getId()) {
            return false;
        }

        if (!$this->proprietario) {
            throw new Escola_Exception("Nenhum Proprietario Localizado!");
        }

        $txt_pessoa_tipo = $txt_proprietario_nome = "--";

        $pt = $this->proprietario_pessoa->getTipo();
        if ($pt) {
            $txt_pessoa_tipo = $pt->descricao;
        }

        $txt_proprietario_nome = $this->proprietario_pessoa->mostrar_nome();

        ob_start();
        $this->AddPage();
        $this->css();
        ?>
        <br />
        <table>
            <tr>
                <td class="titulo_diretoria">DIRETORIA DE TRANSPORTES E TRÂNSITO</td>
            </tr>
        </table>
        <br />
        <table>
            <tr>
                <td class="titulo_ficha">FICHA DE CADASTRO</td>
            </tr>
        </table>
        <br />
        <table border="1">
            <tr>
                <td>
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Número do Veículo</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $this->transporte->mostrar_codigo(); ?></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Pessoa</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?= $txt_pessoa_tipo  ?></td>
                        </tr>
                    </table>
                </td>
                <td colspan="3">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Nome</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?= $txt_proprietario_nome ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php
                    $endereco1 = $endereco2 = $bairro = $cep = $municipio = $uf = "";
                    if ($this->proprietario_pessoa) {
                        $proprietario_pessoa = $this->proprietario_pessoa;
                        $endereco = $proprietario_pessoa->getEndereco();
                        if ($endereco) {
                            $endereco1 = $endereco->logradouro;
                            if ($endereco->numero) {
                                $endereco1 .= ", " . $endereco->numero;
                            }
                            $bairro = $endereco->findParentRow("TbBairro");
                            if ($bairro) {
                                $municipio = $bairro->findParentRow("TbMunicipio");
                                if ($municipio) {
                                    $endereco2 = $municipio->toString();
                                    $uf = $municipio->findParentRow("TbUf");
                                    if ($uf) {
                                        $endereco2 .= " / " . $uf->sigla;
                                    }
                                }
                            }
                            if (Escola_Util::limpaNumero($endereco->cep)) {
                                $cep = Escola_Util::formatCep($endereco->cep);
                            }
                        }
                    }
                    ?>
            <tr>
                <td rowspan="2" colspan="3">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Endereço</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $endereco1; ?> - <?php echo $endereco2; ?></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Bairro</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo ($bairro) ? $bairro->descricao : ""; ?></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">CEP</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $cep; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php
                    $celular = $fixo = "";
                    if ($proprietario_pessoa) {
                        $celular = $proprietario_pessoa->mostrarTelefones("C");
                        $fixo = $proprietario_pessoa->mostrarTelefones("F");
                    }
                    ?>
            <tr>
                <td>
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Fone Celular</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $celular; ?></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Fone Residencial</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $fixo; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">C.P.F./C.N.P.J.</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo ($proprietario_pessoa) ? $proprietario_pessoa->mostrar_documento() : ""; ?></td>
                        </tr>
                    </table>
                </td>
                <?php
                        $ci = "";
                        if ($pt && $pt->pf()) {
                            $filho = $proprietario_pessoa->pegaPessoaFilho();
                            if ($filho) {
                                $ci = $filho->mostrar_identidade();
                            }
                        }
                        ?>
                <td colspan="3">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">R.G.</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $ci; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php
                    $concessao_numero = $concessao_data = $decreto = $carater = $validade = "";
                    $concessao = $this->transporte->findParentRow("TbConcessao");
                    if ($concessao) {
                        $concessao_numero = $concessao->numero;
                        $concessao_data = Escola_Util::formatData($concessao->concessao_data);
                        $decreto = $concessao->decreto;
                        $concessao_tipo = $concessao->findParentRow("TbConcessaoTipo");
                        if ($concessao_tipo) {
                            $carater = $concessao_tipo->toString();
                        }
                        $concessao_validade = $concessao->findParentRow("TbConcessaoValidade");
                        if ($concessao_validade) {
                            $validade = $concessao_validade->toString();
                        }
                    }
                    ?>
            <tr>
                <td>
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Número Concessão</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $concessao_numero; ?></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Data Concessão</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $concessao_data; ?></td>
                        </tr>
                    </table>
                </td>
                <td colspan="3">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Decreto</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $decreto; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Caráter</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $carater; ?></td>
                        </tr>
                    </table>
                </td>
                <td colspan="3">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Validade</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $validade; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php
                $tb = new TbTransporteVeiculo();
                $tvs = $tb->listar(array("id_transporte" => $this->transporte->getId()));
                if ($tvs) {
                    ?>
            <br />
            <table>
                <tr>
                    <td class="negrito font_10">HISTÓRICO DE VEÍCULOS</td>
                </tr>
            </table>
            <?php
                        foreach ($tvs as $tv) {
                            $marca = $cor = $combustivel = "";
                            $veiculo = $tv->findParentRow("TbVeiculo");
                            if ($veiculo) {
                                $obj = $veiculo->findParentRow("TbFabricante");
                                if ($obj) {
                                    $marca = $obj->toString();
                                }
                                $obj = $veiculo->findParentRow("TbCor");
                                if ($obj) {
                                    $cor = $obj->toString();
                                }
                                $obj = $veiculo->findParentRow("TbCombustivel");
                                if ($obj) {
                                    $combustivel = $obj->toString();
                                }
                            }
                            $status = $tv->findParentRow("TbTransporteVeiculoStatus");
                            $data_baixa = "";
                            $baixa = $tv->pegaBaixa();
                            if ($baixa) {
                                $data_baixa = Escola_Util::formatData($baixa->baixa_data);
                            }
                            ?>
                <br />
                <table border="1">
                    <?php
                                    //verificar se o nome do proprietario do veiculo eh diferente do transporte
                                    //somente transportes ativos
                                    if ($tv->ativo()) {
                                        $proprietario_veiculo = $veiculo->getProprietario();
                                        if ($proprietario_veiculo) {
                                            if ($proprietario_veiculo->getId() != $proprietario_pessoa->getId()) {
                                                //mostrar proprietario veiculo
                                                $txt_proprietario = $proprietario_veiculo->toString();
                                                ?>
                                <tr>
                                    <td colspan="6">
                                        <table cellspacing="3">
                                            <tr>
                                                <td class="campo_legenda">Proprietário:</td>
                                            </tr>
                                            <tr>
                                                <td class="campo_valor"><?php echo $txt_proprietario; ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                    <?php
                                            }
                                        }
                                    }
                                    ?>
                    <tr>
                        <td>
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Situação</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $status->toString(); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Placa</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $veiculo->placa; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="2">
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Modelo</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $veiculo->modelo; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="2">
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Fabricante</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $marca; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Ano Modelo</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $veiculo->ano_modelo; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Ano Fabricação</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $veiculo->ano_fabricacao; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="2">
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Cor</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $cor; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="2">
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Chassi</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $veiculo->chassi; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Combustivel</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $combustivel; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Processo</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $tv->processo; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Data Processo</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo Escola_Util::formatData($tv->processo_data); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Data Cadastro</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo Escola_Util::formatData($tv->data_cadastro); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table cellspacing="3">
                                <tr>
                                    <td class="campo_legenda">Data Baixa</td>
                                </tr>
                                <tr>
                                    <td class="campo_valor"><?php echo $data_baixa; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
        <?php
                    }
                }
                ?>
        <p></p>
        <table>
            <tr>
                <td align="center"><?php echo $pj->sigla; ?></td>
            </tr>
        </table>
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
            $this->lastPage();
            $this->download();
            // $this->show();
        }

        public function css()
        {
            ?>
        <style type="text/css">
            body,
            td {
                font-size: 8pt;
            }

            .negrito {
                font-weight: bold;
            }

            .font_10 {
                font-size: 10pt;
            }

            .titulo_diretoria {
                font-weight: bold;
                font-size: 10pt;
                text-align: center;
            }

            .titulo_ficha {
                font-weight: bold;
                font-size: 13pt;
                text-align: center;
            }

            .campo_legenda {
                font-size: 7pt;
            }

            .campo_valor {
                font-size: 10pt;
                text-indent: 10px;
            }
        </style>
<?php
    }
}
