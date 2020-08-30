<?php

class Escola_Relatorio_Default_Ttr_Placas_Pdf extends Escola_Relatorio {

    public function __construct() {
        parent::__construct("relatorio");
        $this->SetTopMargin(30);
    }

    public function set_dados($dados) {
        parent::set_dados($dados);
        if (isset($dados["filename"])) {
            $this->setFilename($dados["filename"]);
        }
    }

    public function header() {
        parent::header();
        ob_start();
        $this->css();
        ?>
        <table>
            <tr>
                <td align="center" class="titulo-secundario"><?php echo $this->relatorio->descricao; ?></td>
            </tr>
        </table>
        <br />
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');
    }

    public function imprimir() {
        $stmt = $this->dados["statement"];
        $this->AddPage("L");
        ob_start();
        $this->css();
        ?>
        <style type="text/css">
            body, td, th {
                font-size: 8pt;
            }
        </style>
        <table border="1" cellpadding="4">
            <?php
            if ($stmt && count($stmt)) {
                ?>
                <tr>
                    <th align="center" width="70px" bgcolor="#ccc">Placa</th>
                    <th width="70px" align="center" bgcolor="#ccc">Grupo</th>
                    <th width="70px" align="center" bgcolor="#ccc">Código</th>
                    <th align="center" width="220px" bgcolor="#ccc">Proprietário</th>
                    <th width="60px" align="center" bgcolor="#ccc">Ano</th>
                    <th width="80px" align="center" bgcolor="#ccc">Dt Baixa</th>
                    <th align="center" width="150px" bgcolor="#ccc">Chassi</th>
                    <th align="center" width="90px" bgcolor="#ccc">Fabricante</th>
                    <th align="center" width="90px" bgcolor="#ccc">Modelo</th>
                    <th align="center" width="90px" bgcolor="#ccc">Cor</th>
                </tr>
                <?php
                foreach ($stmt as $obj) {
                    $placa = $grupo = $codigo = $proprietario = $ano = $chassi = $fabricante = $modelo = $cor = "--";
                    $tb = $obj;
                    $tv = $obj->findParentRow("TbTransporteVeiculo");
                    if ($tv) {
                        $transporte = $tv->findParentRow("TbTransporte");
                        if ($transporte) {
                            $codigo = $transporte->codigo;
                            $pro = $transporte->pegaProprietario();
                            if ($pro) {
                                $proprietario = $pro->toString();
                            }
                            $tg = $transporte->findParentRow("TbTransporteGrupo");
                            if ($tg) {
                                $grupo = $tg->toString();
                            }
                        }
                        $veiculo = $tv->findParentRow("TbVeiculo");
                        if ($veiculo) {
                            $placa = $veiculo->placa;
                            $ano = $veiculo->ano_fabricacao;
                            $chassi = $veiculo->chassi;
                            $fab = $veiculo->findParentRow("TbFabricante");
                            if ($fab) {
                                $fabricante = $fab->toString();
                            }
                            $modelo = $veiculo->modelo;
                            $c = $veiculo->findParentRow("TbCor");
                            if ($c) {
                                $cor = $c->toString();
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td align="center"><div class="text-center"><?php echo $placa; ?></div></td>
                        <td align="center"><div class="text-center"><?php echo $grupo; ?></div></td>
                        <td align="center"><div class="text-center"><?php echo $codigo; ?></div></td>
                        <td><?php echo $proprietario; ?></td>
                        <td align="center"><div class="text-center"><?php echo $ano; ?></div></td>
                        <td align="center"><div class="text-center"><?php echo Escola_Util::formatData($tb->baixa_data); ?></div></td>
                        <td align="center"><div class="text-center"><?php echo $chassi; ?></div></td>
                        <td align="center"><div class="text-center"><?php echo $fabricante; ?></div></td>
                        <td align="center"><div class="text-center"><?php echo $modelo; ?></div></td>
                        <td align="center"><div class="text-center"><?php echo $cor; ?></div></td>
                    </tr>
                <?php } ?>
        <?php } else { ?>
                <tr>
                    <td><div class="text-center">NENHUM REGISTRO LOCALIZADO!</div></td>
                </tr>
        <?php } ?>
        </table>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');

        $this->lastPage();
        $this->download();
    }

}