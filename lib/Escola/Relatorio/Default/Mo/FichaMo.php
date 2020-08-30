<?php
class Escola_Relatorio_Default_Mo_FichaMo extends Escola_Relatorio_Default
{

    protected $motorista;

    public function set_motorista($motorista)
    {
        $this->motorista = $motorista;
    }

    public function set_dados($dados)
    {
        if (isset($dados["motorista"]) && $dados["motorista"]) {
            $this->set_motorista($dados["motorista"]);
        }
        parent::set_dados($dados);
    }

    public function validarEmitir()
    {
        $errors = array();
        if (!$this->motorista) {
            $errors[] = "NENHUM MOTORISTA INFORMADO!";
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }

    public function toXLS()
    { }

    public function toPDF()
    {
        $pdf_class_name = get_class($this) . "_Pdf";
        $zla = Zend_Loader_Autoloader::getInstance();
        if ($zla->autoload($pdf_class_name)) {
            $obj = new $pdf_class_name;
            $filter = new Zend_Filter_CharConverter();
            $filename = $filter->filter($this->relatorio->descricao);
            $filter = new Zend_Filter_StringToLower();
            $filename = $filter->filter($filename);
            $filename = str_replace(" ", "_", $filename);
            $obj->set_dados(array("filename" => "relatorio_" . $filename));
            $obj->set_relatorio($this->relatorio);
            $obj->set_motorista($this->motorista);
            $obj->imprimir();
        }
    }

    public function toHTML()
    {
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
        ?>
        <style type="text/css">
            div.rotulo {
                font-size: 9pt;
            }

            div.info {
                font-size: 12pt;
                font-weight: bold;
                text-indent: 30px;
            }
        </style>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="4">FICHA DE CADASTRO - MOTORISTA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="rotulo">Grupo de Transporte:</div>
                        <div class="info"><?php echo $txt_gt; ?></div>
                    </td>
                    <td>
                        <div class="rotulo">Matrícula:</div>
                        <div class="info"><?php echo $txt_matricula; ?></div>
                    </td>
                    <td colspan="3">
                        <div class="rotulo">Nome:</div>
                        <div class="info"><?php echo $txt_nome; ?></div>
                    </td>
                </tr>
                <tr>
                    <td rowspan="2" colspan="2">
                        <div class="rotulo">Endereço:</div>
                        <div class="info"><?php echo $txt_endereco; ?></div>
                    </td>
                    <td>
                        <div class="rotulo">Bairro:</div>
                        <div class="info"><?php echo $txt_bairro; ?></div>
                    </td>
                    <td>
                        <div class="rotulo">CEP:</div>
                        <div class="info"><?php echo $txt_cep; ?></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="rotulo">Celular:</div>
                        <div class="info"><?php echo $txt_celular; ?></div>
                    </td>
                    <td>
                        <div class="rotulo">Fone Residencial:</div>
                        <div class="info"><?php echo $txt_fone; ?></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="rotulo">C.P.F.:</div>
                        <div class="info"><?php echo $txt_cpf; ?></div>
                    </td>
                    <td colspan="2">
                        <div class="rotulo">R.G.:</div>
                        <div class="info"><?php echo $txt_rg; ?></div>
                    </td>
                    <td>
                        <div class="rotulo">Data Cadastro:</div>
                        <div class="info"><?php echo $txt_data_cadastro; ?></div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="3">CARTEIRA NACIONAL DE HABILITAÇÃO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="rotulo">Categoria:</div>
                        <div class="info"><?php echo $txt_cnh_categoria; ?></div>
                    </td>
                    <td>
                        <div class="rotulo">Número:</div>
                        <div class="info"><?php echo $txt_cnh_numero; ?></div>
                    </td>
                    <td>
                        <div class="rotulo">Validade:</div>
                        <div class="info"><?php echo $txt_cnh_validade; ?></div>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php if ($tps && count($tps)) { ?>
            <table class="table table-bordered">
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
                            <td><?php echo $txt_codigo; ?></td>
                            <td><?php echo $txt_tipo; ?></td>
                            <td><?php echo $txt_proprietario; ?></td>
                            <td><?php echo $txt_status; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
