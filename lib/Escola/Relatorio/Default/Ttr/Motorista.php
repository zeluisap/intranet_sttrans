<?php
class Escola_Relatorio_Default_Ttr_Motorista extends Escola_Relatorio_Default
{

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
            $obj->set_dados(array("filename" => "relatorio_" . $filename, "statement" => $this->pegaStatement()));
            $obj->set_relatorio($this->relatorio);
            $obj->imprimir();
        }
    }

    public function toXLS()
    {
        $stmt = $this->pegaStatement();
        $workbook = new Spreadsheet_Excel_Writer();
        $estilo_normal = $workbook->addFormat(array("size" => 9, "Border" => 1, "VAlign" => "vcenter"));
        $estilo_normal_centro = $workbook->addFormat(array("size" => 9, "Border" => 1, "Align" => "center", "VAlign" => "vcenter"));
        $estilo_cabecalho = $workbook->addFormat(array("size" => 11, "Border" => 2, "Align" => "center", "Bold" => 1, "VAlign" => "vcenter"));
        $worksheet = $workbook->addWorksheet("relatorio_motorista");
        $worksheet->setLandscape();
        $worksheet->centerHorizontally();
        $worksheet->setMarginLeft(1.0);
        $worksheet->setMarginRight(1.0);
        $worksheet->setMarginTop(1.0);
        $worksheet->setMarginBottom(1.0);
        $worksheet->setColumn(0, 0, 10);
        $worksheet->setColumn(1, 1, 20);
        $worksheet->setColumn(2, 2, 50);
        $worksheet->setColumn(3, 3, 20);
        $worksheet->setColumn(4, 4, 20);
        $worksheet->setColumn(5, 5, 20);
        $worksheet->setColumn(6, 6, 20);
        if ($stmt && count($stmt)) {
            $coluna = $linha = 0;
            $breaks = array();
            $id_transporte_grupo = 0;
            foreach ($stmt as $motorista) {
                if ($id_transporte_grupo != $motorista->id_transporte_grupo) {
                    if ($id_transporte_grupo) {
                        $breaks[] = $linha;
                    }
                    $tg = $motorista->findParentRow("TbTransporteGrupo");
                    $worksheet->writeString($linha, 0, $tg->toString(), $estilo_cabecalho);
                    $worksheet->mergeCells($linha, 0, $linha, 6);
                    $id_transporte_grupo = $motorista->id_transporte_grupo;
                    $linha++;
                    $coluna = 0;
                    $worksheet->writeString($linha, $coluna++, "Matrícula", $estilo_cabecalho);
                    $worksheet->writeString($linha, $coluna++, "C.P.F.", $estilo_cabecalho);
                    $worksheet->writeString($linha, $coluna++, "Nome", $estilo_cabecalho);
                    $worksheet->writeString($linha, $coluna++, "Data Cadastro", $estilo_cabecalho);
                    $worksheet->writeString($linha, $coluna++, "CNH Número", $estilo_cabecalho);
                    $worksheet->writeString($linha, $coluna++, "CNH Validade", $estilo_cabecalho);
                    $worksheet->writeString($linha, $coluna++, "CNH Categoria", $estilo_cabecalho);
                    $worksheet->writeString($linha, $coluna++, "Carteira Número", $estilo_cabecalho);
                    $worksheet->writeString($linha, $coluna++, "Carteira Validade", $estilo_cabecalho);
                    $linha++;
                }

                $cpf = $nome = $data_cadastro = $cnh_numero = $cnh_validade = $cnh_categoria = $carteira_numero = $carteira_validade = "";
                $pm = $motorista->findParentRow("TbPessoaMotorista");
                if ($pm) {
                    $cnh_numero = $pm->cnh_numero;
                    $cnh_validade = Escola_Util::formatData($pm->cnh_validade);
                    $cat = $pm->findParentRow("TbCnhCategoria");
                    if ($cat) {
                        $cnh_categoria = $cat->codigo;
                    }
                    $pf = $pm->findParentRow("TbPessoaFisica");
                    if ($pf) {
                        $cpf = Escola_Util::formatCpf($pf->cpf);
                        $nome = $pf->nome;
                    }
                }
                $data_cadastro = Escola_Util::formatData($motorista->data_cadastro);
                $ss_carteira = $motorista->pegaCarteiraAtiva();
                if ($ss_carteira) {
                    $carteira_numero = $ss_carteira->mostrar_numero();
                    $carteira_validade = Escola_Util::formatData($ss_carteira->data_validade);
                }

                $coluna = 0;
                $worksheet->writeString($linha, $coluna++, $motorista->matricula, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $cpf, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $nome, $estilo_normal);
                $worksheet->writeString($linha, $coluna++, $data_cadastro, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $cnh_numero, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $cnh_validade, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $cnh_categoria, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $carteira_numero, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $carteira_validade, $estilo_normal_centro);
                $linha++;
            }
        }
        $worksheet->setHPagebreaks($breaks);
        $workbook->send("relatorio_motorista.xls");
        $workbook->close();
        die();
    }

    public function pegaStatement()
    {
        $tb = new TbMotorista();
        return $tb->listar($this->dados);
    }

    public function toHTML()
    {
        $stmt = $this->pegaStatement();
        ob_start();
        ?>
        <table class="table table-striped table-bordered">
            <?php
                    if ($stmt && count($stmt)) {
                        $id_transporte_grupo = 0;
                        foreach ($stmt as $motorista) {
                            if ($id_transporte_grupo != $motorista->id_transporte_grupo) {
                                $tg = $motorista->findParentRow("TbTransporteGrupo");
                                if ($id_transporte_grupo) {
                                    ?>
                            </body>
                        <?php } ?>
                        <thead>
                            <tr>
                                <th colspan="9"><?php echo $tg->toString(); ?></th>
                            </tr>
                            <tr>
                                <th>Matrícula</th>
                                <th>C.P.F.</th>
                                <th>Nome</th>
                                <th>Data Cadastro</th>
                                <th>CNH Número</th>
                                <th>CNH Validade</th>
                                <th>CNH Categoria</th>
                                <th>Carteira Número</th>
                                <th>Carteira Validade</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                                            $id_transporte_grupo = $motorista->id_transporte_grupo;
                                        }
                                        $cpf = $nome = $data_cadastro = $cnh_numero = $cnh_validade = $cnh_categoria = $carteira_numero = $carteira_validade = "";
                                        $pm = $motorista->findParentRow("TbPessoaMotorista");
                                        if ($pm) {
                                            $cnh_numero = $pm->cnh_numero;
                                            $cnh_validade = Escola_Util::formatData($pm->cnh_validade);
                                            $cat = $pm->findParentRow("TbCnhCategoria");
                                            if ($cat) {
                                                $cnh_categoria = $cat->codigo;
                                            }
                                            $pf = $pm->findParentRow("TbPessoaFisica");
                                            if ($pf) {
                                                $cpf = Escola_Util::formatCpf($pf->cpf);
                                                $nome = $pf->nome;
                                            }
                                        }
                                        $data_cadastro = Escola_Util::formatData($motorista->data_cadastro);
                                        $ss_carteira = $motorista->pegaCarteiraAtiva();
                                        if ($ss_carteira) {
                                            $carteira_numero = $ss_carteira->mostrar_numero();
                                            $carteira_validade = Escola_Util::formatData($ss_carteira->data_validade);
                                        }
                                        ?>
                        <tr>
                            <td>
                                <div class="text-center"><?php echo $motorista->matricula; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $cpf; ?></div>
                            </td>
                            <td>
                                <div class="text-left"><?php echo $nome; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $data_cadastro; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $cnh_numero; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $cnh_validade; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $cnh_categoria; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $carteira_numero; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $carteira_validade; ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                        <tbody>
                            <tr>
                                <td colspan="5">
                                    <div class="text-center">NENHUM REGISTRO LOCALIZADO!</div>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
        </table>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
