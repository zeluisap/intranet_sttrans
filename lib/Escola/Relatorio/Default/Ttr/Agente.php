<?php
class Escola_Relatorio_Default_Ttr_Agente extends Escola_Relatorio_Default
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
        $worksheet = $workbook->addWorksheet("relatorio_agentes");
        $worksheet->setLandscape();
        $worksheet->centerHorizontally();
        $worksheet->setMarginLeft(1.0);
        $worksheet->setMarginRight(1.0);
        $worksheet->setMarginTop(1.0);
        $worksheet->setMarginBottom(1.0);
        $worksheet->setColumn(0, 0, 10);
        $worksheet->setColumn(1, 1, 20);
        $worksheet->setColumn(2, 2, 20);
        $worksheet->setColumn(3, 3, 50);
        $worksheet->setColumn(4, 4, 50);
        if ($stmt && count($stmt)) {
            $coluna = $linha = 0;
            $worksheet->writeString($linha, $coluna++, "Código", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Matrícula", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "C.P.F.", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Nome", $estilo_cabecalho);
            $worksheet->writeString($linha, $coluna++, "Cargo", $estilo_cabecalho);
            $linha++;
            foreach ($stmt as $agente) {
                $cpf = $matricula = $nome = $cargo = "";
                $funcionario = $agente->findParentRow("TbFuncionario");
                if ($funcionario) {
                    $matricula = $funcionario->matricula;
                    $pf = $funcionario->findParentRow("TbPessoaFisica");
                    if ($pf) {
                        $nome = $pf->nome;
                        $cpf = Escola_Util::formatCpf($pf->cpf);
                    }
                    $cargo = $funcionario->findParentRow("TbCargo");
                    if ($cargo) {
                        $cargo = $cargo->toString();
                    }
                }
                $coluna = 0;
                $worksheet->writeString($linha, $coluna++, $agente->codigo, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $matricula, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $cpf, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $nome, $estilo_normal);
                $worksheet->writeString($linha, $coluna++, $cargo, $estilo_normal);
                $linha++;
            }
        }
        $workbook->send("relatorio_agente.xls");
        $workbook->close();
        die();
    }

    public function pegaStatement()
    {
        $tb = new TbAgente();
        return $tb->listar();
    }

    public function toHTML()
    {
        $stmt = $this->pegaStatement();
        ob_start();
        ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Matrícula</th>
                    <th>C.P.F.</th>
                    <th>Nome</th>
                    <th>Cargo</th>
                </tr>
            </thead>
            <tbody>
                <?php
                        if ($stmt && count($stmt)) {
                            foreach ($stmt as $agente) {
                                $cpf = $matricula = $nome = $cargo = "";
                                $funcionario = $agente->findParentRow("TbFuncionario");
                                if ($funcionario) {
                                    $matricula = $funcionario->matricula;
                                    $pf = $funcionario->findParentRow("TbPessoaFisica");
                                    if ($pf) {
                                        $nome = $pf->nome;
                                        $cpf = Escola_Util::formatCpf($pf->cpf);
                                    }
                                    $cargo = $funcionario->findParentRow("TbCargo");
                                    if ($cargo) {
                                        $cargo = $cargo->toString();
                                    }
                                }
                                ?>
                        <tr>
                            <td>
                                <div class="text-center"><?php echo $agente->codigo; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $matricula; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $cpf; ?></div>
                            </td>
                            <td>
                                <div class="text-left"><?php echo $nome; ?></div>
                            </td>
                            <td>
                                <div class="text-left"><?php echo $cargo; ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
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
