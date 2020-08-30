<?php

class Escola_Relatorio_Servico_APVPV extends Escola_Relatorio_Servico {

    public function __construct() {
        parent::__construct();
        $this->setFilename("autorizacao_propaganda_volante");
        $this->SetTopMargin(10);
    }

    public function header() {
        
    }

    public function toPDF() {
        if (!$this->registro) {
            return false;
        }
        $transporte = $this->registro->findParentRow("TbTransporte");
        if (!$transporte) {
            return false;
        }
        $this->setFilename($this->getFilename() . "_" . $this->registro->ano_referencia . "_" . Escola_Util::zero($this->registro->codigo, 4));
        $veiculo = $transporte->pegaVeiculo();
        $proprietario = $transporte->pegaProprietario();
        $stg = $registro->findParentRow("TbServicoTransporteGrupo");
        $servico = $stg->findParentRow("TbServico");
        $tg = $stg->findParentRow("TbTransporteGrupo");
        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        $arquivo = false;
        if ($sistema) {
            $pj = $sistema->findParentRow("TbPessoaJuridica");
            $pessoa = $pj->pega_pessoa();
            $arquivo = $pessoa->getFoto();
        }
        $this->AddPage();
        ob_start();
        $this->css();
        ?>
        <table border="1">
            <tr>
                <td>
                    <table cellpadding="3">
                        <tr>
                            <td align="center" width="100px" rowspan="3"><img src="<?php echo $arquivo->pegaNomeCompleto(); ?>" alt="" /></td>
                            <td align="center" class="titulo_servico titulo_servico_mini" width="500px">ESTADO DO AMAPÁ</td>
                        </tr>
                        <tr>
                            <td align="center" class="titulo_servico titulo_servico_mini">PREFEITURA MUNICIPAL DE SANTANA</td>
                        </tr>
                        <tr>
                            <td align="center" class="titulo_servico titulo_servico_mini"><?php echo $pj->razao_social; ?></td>
                        </tr>
                        <tr>
                            <td align="center" class="titulo_servico titulo_servico_mini">DIRETORIA DE TRANSPORTES</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table border="1">
            <tr><td align="center" colspan="2" class="titulo_servico">AUTORIZAÇÃO No. <?php echo $registro->mostrar_numero(); ?></td></tr>
        </table>
        <br />
        <table cellpadding="5" border="1">
            <tr><td colspan="4">PARA PROPAGANDA VOLANTE EM VEÍCULO AUTOMOTOR DE ACORDO COM O CÓDIGO DE TRÂNSITO BRASILEIRO - ART.24-I E LEI COMPLEMENTAR MUNICIPAL 004/2010.</td></tr>
            <?php
            if ($veiculo) {
                $marca_modelo = "--";
                $txt = array();
                $fab = $veiculo->findParentRow("TbFabricante");
                if ($fab) {
                    $txt[] = $fab->descricao;
                }
                if ($veiculo->modelo) {
                    $txt[] = $veiculo->modelo;
                }
                if (count($txt)) {
                    $marca_modelo = implode("/", $txt);
                }
                ?>
                <tr>
                    <td colspan="3">
                        <table cellspacing="3">
                            <tr>
                                <td class="campo_legenda">Marca/Modelo</td>
                            </tr>
                            <tr>
                                <td class="campo_valor"><?php echo $marca_modelo; ?></td>
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
                </tr>
                <?php
                $especie_tipo = "--";
                $txt = array();
                $cat = $veiculo->findParentRow("TbVeiculoCategoria");
                if ($cat) {
                    $txt[] = $cat->descricao;
                }
                $tipo = $veiculo->findParentRow("TbVeiculoTipo");
                if ($tipo) {
                    $txt[] = $tipo->descricao;
                }
                if (count($txt)) {
                    $especie_tipo = implode("/", $txt);
                }
                ?>
                <tr>
                    <td colspan="2">
                        <table cellspacing="3">
                            <tr>
                                <td class="campo_legenda">Espécie/Tipo</td>
                            </tr>
                            <tr>
                                <td class="campo_valor"><?php echo $especie_tipo; ?></td>
                            </tr>
                        </table>
                    </td>            
                    <td>
                        <table cellspacing="3">
                            <tr>
                                <td class="campo_legenda">Chassi</td>
                            </tr>
                            <tr>
                                <td class="campo_valor"><?php echo $veiculo->chassi; ?></td>
                            </tr>
                        </table>
                    </td>            
                    <td>
                        <table cellspacing="3">
                            <tr>
                                <td class="campo_legenda">Renavan</td>
                            </tr>
                            <tr>
                                <td class="campo_valor"><?php echo $veiculo->renavan; ?></td>
                            </tr>
                        </table>
                    </td>            
                </tr>
                <?php
                $ano_fab_modelo = "--";
                $txt = array();
                if ($veiculo->ano_fabricacao) {
                    $txt[] = $veiculo->ano_fabricacao;
                }
                if ($veiculo->ano_modelo) {
                    $txt[] = $veiculo->ano_modelo;
                }
                if (count($txt)) {
                    $ano_fab_modelo = implode("/", $txt);
                }
                ?>
                <tr>
                    <td>
                        <table cellspacing="3">
                            <tr>
                                <td class="campo_legenda">Ano Fab./Modelo</td>
                            </tr>
                            <tr>
                                <td class="campo_valor"><?php echo $ano_fab_modelo; ?></td>
                            </tr>
                        </table>
                    </td>
                    <?php
                    $cor = $veiculo->findParentRow("TbCor");
                    ?>
                    <td>
                        <table cellspacing="3">
                            <tr>
                                <td class="campo_legenda">Cor</td>
                            </tr>
                            <tr>
                                <td class="campo_valor"><?php echo ($cor) ? $cor->descricao : ""; ?></td>
                            </tr>
                        </table>
                    </td>
                    <?php
                    $tara = "--";
                    if ($veiculo->tara) {
                        $tara = Escola_Util::number_format($veiculo->tara) . " Kg";
                    }
                    ?>
                    <td>
                        <table cellspacing="3">
                            <tr>
                                <td class="campo_legenda">Tara</td>
                            </tr>
                            <tr>
                                <td class="campo_valor"><?php echo $tara; ?></td>
                            </tr>
                        </table>
                    </td>
                    <?php
                    $lotacao = "--";
                    if ($veiculo->lotacao) {
                        $lotacao = $veiculo->lotacao . " Pessoa(s)";
                    }
                    ?>
                    <td>
                        <table cellspacing="3">
                            <tr>
                                <td class="campo_legenda">Lotação</td>
                            </tr>
                            <tr>
                                <td class="campo_valor"><?php echo $lotacao; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php } ?>
            <?php
            $txt_proprietario = "--";
            if ($proprietario) {
                $txt_proprietario = $proprietario->toString();
            }
            ?>
            <tr>
                <td colspan="4">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Proprietário</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $txt_proprietario; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php
            $txt_motorista = "--";
            $motoristas = $transporte->listar_motorista();
            if ($motoristas) {
                $txt_motorista = $motoristas->current()->toString();
            }
            ?>
            <tr>
                <td colspan="4">
                    <table cellspacing="3">
                        <tr>
                            <td class="campo_legenda">Motorista</td>
                        </tr>
                        <tr>
                            <td class="campo_valor"><?php echo $txt_motorista; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <table cellpadding="4">
                        <tr>
                            <td align="left" class="negrito campo_valor italico">Data de Emissão: <?php echo Escola_Util::formatData($this->registro->data_inicio); ?></td>
                            <td align="left" class="negrito campo_valor italico">Data de Validade: <?php echo Escola_Util::formatData($this->registro->data_validade); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table border="1">
            <tr>
                <td>
                    <table cellspacing="5">
                        <tr><td>Obs.: É obrigatória a apresentação desta, quando solicitada pelos Agentes da <?php echo $pj->sigla; ?> e demais autoridades de trânsito.</td></tr>
                        <tr><td></td></tr>
                    </table>
                    <table>
                        <tr><td colspan="2"></td></tr>
                        <tr><td colspan="2"></td></tr>
                        <tr><td colspan="2"></td></tr>
                        <tr><td colspan="2"></td></tr>
                        <tr><td align="center"><?php echo $pj->sigla; ?></td><td align="center"><?php echo $pj->sigla; ?></td></tr>
                        <tr><td colspan="2"></td></tr>
                    </table>
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

    public function css() {
        ?>
        <style type="text/css">
            .tabela {
                border: 2px solid #000;
            }
            .titulo_servico {
                font-family: "Times New Roman";
                font-size: 20pt;
                font-style: italic;
            }
            .titulo_servico_mini {
                font-family: "Arial";
                font-style: normal;
                font-weight: bold;
                font-size: 12pt;
            }
            .negrito {
                font-weight: bold;
            }
            .rr {
                background-color: #ccc;
            }
            .campo_legenda {
                font-size: 10pt;
            }
            .campo_valor {
                font-size: 12pt;
            }
            .italico {
                font-style: italic;
            }
        </style>
        <?php
    }

}