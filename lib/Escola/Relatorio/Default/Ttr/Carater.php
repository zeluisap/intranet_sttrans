<?php
class Escola_Relatorio_Default_Ttr_Carater extends Escola_Relatorio_Default
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
        $worksheet = $workbook->addWorksheet("relatorio_carater");
        $worksheet->setLandscape();
        $worksheet->centerHorizontally();
        $worksheet->setMarginLeft(1.0);
        $worksheet->setMarginRight(1.0);
        $worksheet->setMarginTop(1.0);
        $worksheet->setMarginBottom(1.0);
        $worksheet->setColumn(0, 0, 10);
        $worksheet->setColumn(1, 1, 60);
        $worksheet->setColumn(2, 2, 20);
        $worksheet->setColumn(3, 3, 15);
        $worksheet->setColumn(4, 4, 15);
        $worksheet->setColumn(5, 5, 15);
        $worksheet->setColumn(6, 6, 35);
        $worksheet->setColumn(7, 7, 15);
        $worksheet->setColumn(8, 8, 20);
        $worksheet->setColumn(9, 9, 20);
        $worksheet->setColumn(10, 10, 20);

        $linha = $coluna = 0;
        $worksheet->writeString($linha, $coluna++, utf8_decode("Código"), $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, "Nome", $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, "Decreto", $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, "Dt Decreto", $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, "Dt Cadastro", $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, "Placa", $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, "Marca/Modelo", $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, "Ano", $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, utf8_decode("Licença"), $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, utf8_decode("Dt Licença"), $estilo_cabecalho);
        $worksheet->writeString($linha, $coluna++, "Vencimento", $estilo_cabecalho);

        if ($stmt && $stmt->rowCount()) {
            $linha = 1;
            $coluna = 0;
            $breaks = array();
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $transporte = TbTransporte::pegaPorId($obj->id_transporte);
                $proprietario = $transporte->pegaProprietario();
                $proprietario_txt = "--";
                if ($proprietario) {
                    $proprietario_txt = $proprietario->toString();
                }
                $tv = TbTransporteVeiculo::pegaPorId($obj->id_transporte_veiculo);
                $veiculo_txt = $data_cadastro = "--";
                if ($tv) {
                    $data_cadastro = Escola_Util::formatData($tv->data_cadastro);
                    $veiculo = $tv->findParentRow("TbVeiculo");
                    if ($veiculo) {
                        $veiculo_txt = $veiculo->placa;
                        $fabricante = $veiculo->findParentRow("TbFabricante");
                        $modelo = array();
                        if ($fabricante) {
                            $modelo[] = $fabricante->descricao;
                        }
                        if ($veiculo->modelo) {
                            $modelo[] = $veiculo->modelo;
                        }
                        if (count($modelo)) {
                            $txt_modelo = implode(" / ", $modelo);
                        }
                        $ano = $veiculo->ano_fabricacao;
                    }
                }
                $licenca_numero = $licenca_data = $licenca_validade = "--";
                $licencas = $tv->pegaLicencaAtiva();
                if ($licencas) {
                    $licenca = $licencas->current();
                    $licenca_numero = $licenca->mostrar_numero();
                    $licenca_data = Escola_Util::formatData($licenca->data_inicio);
                    $licenca_validade = Escola_Util::formatData($licenca->data_validade);
                }
                $coluna = 0;
                $worksheet->writeString($linha, $coluna++, $transporte->codigo, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $proprietario_txt, $estilo_normal);
                $worksheet->writeString($linha, $coluna++, $obj->decreto, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, Escola_Util::formatData($obj->concessao_data), $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $data_cadastro, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $veiculo_txt, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $txt_modelo, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $ano, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $licenca_numero, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $licenca_data, $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $licenca_validade, $estilo_normal_centro);
                $linha++;
            }
        }
        $worksheet->setHPagebreaks($breaks);
        $workbook->send("relatorio_carater.xls");
        $workbook->close();
        die();
    }

    public function pegaStatement()
    {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("c" => "concessao"), array("c.id_concessao", "c.decreto", "c.concessao_data"));
        $sql->join(array("ct" => "concessao_tipo"), "c.id_concessao_tipo = ct.id_concessao_tipo", array("ct.id_concessao_tipo", "concessao_tipo_descricao" => "ct.descricao"));
        $sql->join(array("t" => "transporte"), "c.id_concessao = t.id_concessao", array("t.id_transporte"));
        $sql->Join(array("tv" => "transporte_veiculo"), "t.id_transporte = tv.id_transporte", array("tv.id_transporte_veiculo"));
        $sql->join(array("v" => "veiculo"), "tv.id_veiculo = v.id_veiculo", array("v.id_veiculo"));
        $sql->Join(array("tvs" => "transporte_veiculo_status"), "tv.id_transporte_veiculo_status = tvs.id_transporte_veiculo_status", array("tvs.id_transporte_veiculo_status"));
        $sql->where("tvs.chave = 'A'");
        $sql->where("t.id_transporte_grupo = {$this->dados["id_transporte_grupo"]}");
        //        $sql->order("ct.id_concessao_tipo");
        $sql->order("t.codigo");
        $sql->order("v.placa");
        $sql->order("v.chassi");
        return $db->query($sql);
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
                    <th>Nome</th>
                    <th>Decreto</th>
                    <th>Dt Decreto</th>
                    <th>Dt Cadastro</th>
                    <th>Placa</th>
                    <th>Marca/Modelo</th>
                    <th>Ano</th>
                    <th>Licença</th>
                    <th>Dt Licença</th>
                    <th>Validade</th>
                </tr>
            </thead>
            <tbody>
                <?php
                        if ($stmt && $stmt->rowCount()) {
                            $id_concessao_tipo = 0;
                            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {

                                $transporte = TbTransporte::pegaPorId($obj->id_transporte);
                                $proprietario = $transporte->pegaProprietario();
                                $proprietario_txt = "";
                                if ($proprietario) {
                                    $proprietario_txt = $proprietario->toString();
                                }
                                $tv = TbTransporteVeiculo::pegaPorId($obj->id_transporte_veiculo);
                                $veiculo_txt = $data_cadastro = $txt_modelo = "";
                                if ($tv) {
                                    $data_cadastro = Escola_Util::formatData($tv->data_cadastro);
                                    $veiculo = $tv->findParentRow("TbVeiculo");
                                    if ($veiculo) {
                                        $veiculo_txt = $veiculo->placa;
                                        $fabricante = $veiculo->findParentRow("TbFabricante");
                                        $modelo = array();
                                        if ($fabricante) {
                                            $modelo[] = $fabricante->descricao;
                                        }
                                        if ($veiculo->modelo) {
                                            $modelo[] = $veiculo->modelo;
                                        }
                                        if (count($modelo)) {
                                            $txt_modelo = implode(" / ", $modelo);
                                        }
                                        $ano = $veiculo->ano_fabricacao;
                                    }
                                }
                                $licenca_numero = $licenca_data = $licenca_validade = "";
                                $licencas = $tv->pegaLicencaAtiva();
                                if ($licencas) {
                                    $licenca = $licencas->current();
                                    $licenca_numero = $licenca->mostrar_numero();
                                    $licenca_data = Escola_Util::formatData($licenca->data_inicio);
                                    $licenca_validade = Escola_Util::formatData($licenca->data_validade);
                                }
                                ?>
                        <tr>
                            <td>
                                <div class="text-center"><?php echo $transporte->codigo; ?></div>
                            </td>
                            <td><?php echo $proprietario_txt; ?></td>
                            <td>
                                <div class="text-center"><?php echo $obj->decreto; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo Escola_Util::formatData($obj->concessao_data); ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $data_cadastro; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $veiculo_txt; ?></div>
                            </td>
                            <td>
                                <div class="text-center"></div><?php echo $txt_modelo; ?>
                            </td>
                            <td>
                                <div class="text-center"></div><?php echo $ano; ?>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $licenca_numero; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $licenca_data; ?></div>
                            </td>
                            <td>
                                <div class="text-center"><?php echo $licenca_validade; ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td>
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
