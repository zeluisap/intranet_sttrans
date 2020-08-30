<?php
class Escola_Relatorio_Default_Ttr_Placas extends Escola_Relatorio_Default
{

    public function validarEmitir()
    {
        $errors = array();
        if (!isset($this->dados["id_transporte_grupo"]) || !$this->dados["id_transporte_grupo"]) {
            $errors[] = "GRUPO DE TRANSPORTE NÃO INFORMADO!";
        }
        if (isset($this->dados["id_transporte_grupo"]) && $this->dados["id_transporte_grupo"]) {
            $tg = TbTransporteGrupo::pegaPorId($this->dados["id_transporte_grupo"]);
            if (!$tg) {
                $errors[] = "GRUPO DE TRANSPORTE NÃO INFORMADO!";
            }
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }

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
        $worksheet = $workbook->addWorksheet("relatorio_placas_retidas");
        $worksheet->setLandscape();
        $worksheet->centerHorizontally();
        $worksheet->setMarginLeft(1.0);
        $worksheet->setMarginRight(1.0);
        $worksheet->setMarginTop(1.0);
        $worksheet->setMarginBottom(1.0);
        $worksheet->setColumn(0, 0, 10);
        $worksheet->setColumn(1, 1, 20);
        $worksheet->setColumn(2, 2, 10);
        $worksheet->setColumn(3, 3, 50);
        $worksheet->setColumn(4, 4, 10);
        $worksheet->setColumn(5, 5, 15);
        $worksheet->setColumn(6, 6, 25);
        $worksheet->setColumn(7, 7, 20);
        $worksheet->setColumn(8, 8, 25);
        $worksheet->setColumn(9, 9, 20);
        if ($stmt && count($stmt)) {
            $coluna = $linha = 0;
            $worksheet->writeString($linha, $coluna++, "Placa", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Grupo", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Código", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Proprietário", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Ano", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Dt Baixa", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Chassi", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Fabricante", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Modelo", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Cor", $estilo_cabecalho);
            $linha++;
            foreach ($stmt as $obj) {
                $tb = $obj;
                $tv = $tb->findParentRow("TbTransporteVeiculo");
                $veiculo = $tv->findParentRow("TbVeiculo");
                $transporte = $tv->findParentRow("TbTransporte");
                $proprietario = $transporte->pegaProprietario();
                $proprietario_txt = "";
                if ($proprietario) {
                    $proprietario_txt = $proprietario->toString();
                }
                $coluna = 0;
                $worksheet->writeString($linha, $coluna++, $veiculo->placa, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $transporte->findParentRow("TbTransporteGrupo")->toString(), $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $transporte->codigo, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $proprietario_txt, $estilo_normal);
                $worksheet->writeString($linha, $coluna++, $veiculo->ano_fabricacao, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, Escola_Util::formatData($tb->baixa_data), $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $veiculo->chassi, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $veiculo->findParentRow("TbFabricante")->toString(), $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $veiculo->modelo, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $veiculo->findParentRow("TbCor")->toString(), $estilo_normal_centro);
                $linha++;
            }
        }
        $workbook->send("relatorio_placas_retidas.xls");
        $workbook->close();
        die();
    }

    public function pegaStatement()
    {
        $tb = new TbTransporteVeiculoBaixa();
        $sql = $tb->select();
        $sql->from(array("tb" => "transporte_veiculo_baixa"));
        $sql->join(array("tv" => "transporte_veiculo"), "tb.id_transporte_veiculo = tv.id_transporte_veiculo", array());
        $sql->join(array("bm" => "baixa_motivo"), "tb.id_baixa_motivo = bm.id_baixa_motivo", array());
        $sql->join(array("t" => "transporte"), "tv.id_transporte = t.id_transporte", array());
        $sql->where("bm.chave = 'PR'");
        $sql->where("t.id_transporte_grupo = {$this->dados["id_transporte_grupo"]}");
        $sql->order("t.id_transporte_grupo");
        $sql->order("t.codigo");
        return $tb->fetchAll($sql);
    }

    public function toHTML()
    {
        $stmt = $this->pegaStatement();
        ob_start();
        ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Grupo</th>
                    <th>Código</th>
                    <th>Proprietário</th>
                    <th>Ano</th>
                    <th>Dt Baixa</th>
                    <th>Chassi</th>
                    <th>Fabricante</th>
                    <th>Modelo</th>
                    <th>Cor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                        if ($stmt && count($stmt)) {
                            foreach ($stmt as $obj) {
                                $tb = $obj;
                                $tv = $tb->findParentRow("TbTransporteVeiculo");
                                $transporte = $tv->findParentRow("TbTransporte");
                                $veiculo = $tv->findParentRow("TbVeiculo");
                                $proprietario = $transporte->pegaProprietario();
                                $proprietario_txt = "";
                                if ($proprietario) {
                                    $proprietario_txt = $proprietario->toString();
                                }
                                ?>
                        <tr>
                            <td>
                                <div class="text-center"><?php echo $veiculo->placa; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $transporte->findParentRow("TbTransporteGrupo")->toString(); ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $transporte->codigo; ?></div>
                            </td>
                            <td><?php echo $proprietario_txt; ?></td>
                            <td>
                                <div class="text-center"><?php echo $veiculo->ano_fabricacao; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo Escola_Util::formatData($tb->baixa_data); ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $veiculo->chassi; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $veiculo->findParentRow("TbFabricante")->toString(); ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $veiculo->modelo; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $veiculo->findParentRow("TbCor")->toString(); ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="10">
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
