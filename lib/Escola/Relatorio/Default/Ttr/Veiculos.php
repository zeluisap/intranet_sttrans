<?php
class Escola_Relatorio_Default_Ttr_Veiculos extends Escola_Relatorio_Default
{

    public function validarEmitir()
    {
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
        $estilo_negrito = $workbook->addFormat(array("size" => 9, "Border" => 1, "Bold" => 1, "VAlign" => "vcenter"));
        $estilo_normal_centro = $workbook->addFormat(array("size" => 9, "Border" => 1, "Align" => "center", "VAlign" => "vcenter"));
        $estilo_cabecalho = $workbook->addFormat(array("size" => 11, "Border" => 1, "Align" => "center", "Bold" => 1, "VAlign" => "vcenter"));
        $worksheet = $workbook->addWorksheet("relatorio_taxas");
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
        $resumo = $breaks = array();
        $linha = $id_tg = 0;
        if ($stmt && $stmt->rowCount()) {
            $worksheet->setMerge($linha, 0, $linha, 2);
            $worksheet->writeString($linha, 0, $this->relatorio->descricao, $estilo_negrito);
            $linha++;
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                if ($id_tg != $obj->id_transporte_grupo) {
                    $id_tg = $obj->id_transporte_grupo;
                    if (!array_key_exists($id_tg, $resumo)) {
                        $item = new stdClass();
                        $item->id_transporte_grupo = $obj->id_transporte_grupo;
                        $item->transporte_grupo_descricao = $obj->transporte_grupo_descricao;
                        $item->valor = 0;
                        $resumo[$id_tg] = $item;
                        $linha++;
                    }
                    $coluna = 0;
                    $worksheet->setMerge($linha, 0, $linha, 2);
                    $worksheet->writeString($linha, 0, "Tipo de Transporte - " . $obj->transporte_grupo_descricao, $estilo_negrito);
                    $linha++;
                }
                $coluna = 0;
                $worksheet->writeString($linha, $coluna++, Escola_Util::formatData($obj->data_pagamento), $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $obj->servico_descricao, $estilo_normal);
                $worksheet->writeString($linha, $coluna++, Escola_Util::number_format($obj->valor_servico), $estilo_normal_centro);
                $resumo[$obj->id_transporte_grupo]->valor = $resumo[$obj->id_transporte_grupo]->valor + $obj->valor_servico;
                $linha++;
            }
            $linha++;
            if (count($resumo)) {
                $worksheet->setMerge($linha, 0, $linha, 2);
                $worksheet->writeString($linha, 0, "Resumo", $estilo_cabecalho);
                $linha++;
                $worksheet->setMerge($linha, 0, $linha, 1);
                $worksheet->writeString($linha, 0, "Tipo de Veículo", $estilo_cabecalho);
                $worksheet->writeString($linha, 2, "Total", $estilo_cabecalho);
                $linha++;
                foreach ($resumo as $item_resumo) {
                    $worksheet->setMerge($linha, 0, $linha, 1);
                    $worksheet->writeString($linha, 0, $item_resumo->transporte_grupo_descricao, $estilo_normal);
                    $worksheet->writeString($linha, 2, Escola_Util::number_format($item_resumo->valor), $estilo_normal_centro);
                    $linha++;
                }
            }
        }
        $worksheet->setHPagebreaks($breaks);
        $workbook->send("relatorio_Taxas.xls");
        $workbook->close();
        die();
    }

    public function pegaStatement()
    {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("v" => "veiculo"), array("v.chassi", "v.placa", "v.modelo", "v.renavan", "v.data_aquisicao", "v.ano_fabricacao", "proprietario" => "v.proprietario_id_pessoa"));
        $sql->join(array("f" => "fabricante"), "v.id_fabricante = f.id_fabricante", array("fabricante" => "f.descricao"));
        $sql->join(array("c" => "combustivel"), "v.id_combustivel = c.id_combustivel", array("combustivel" => "c.descricao"));
        $sql->join(array("tv" => "transporte_veiculo"), "v.id_veiculo = tv.id_veiculo", array());
        $sql->join(array("tvs" => "transporte_veiculo_status"), "tv.id_transporte_veiculo_status = tvs.id_transporte_veiculo_status", array("tv_status_descricao" => "tvs.descricao"));
        $sql->join(array("ss" => "servico_solicitacao"), "tv.id_transporte_veiculo = ss.chave", array("ss.tipo", "ss.codigo", "ss.data_inicio", "ss.data_vencimento"));
        $sql->Join(array("sss" => "servico_solicitacao_status"), "ss.id_servico_solicitacao_status = sss.id_servico_solicitacao_status", array("ss_status_descricao" => "sss.descricao"));
        $sql->Join(array("t" => "transporte"), "tv.id_transporte = t.id_transporte", array());
        $sql->Join(array("tp" => "transporte_pessoa"), "tp.id_transporte = t.id_transporte", array());
        $sql->join(array("tg" => "transporte_grupo"), "t.id_transporte_grupo = tg.id_transporte_grupo", array("tg.id_transporte_grupo", "transporte_grupo_descricao" => "tg.descricao"));
        $sql->Join(array("tpt" => "transporte_pessoa_tipo"), "tpt.id_transporte_pessoa_tipo = tp.id_transporte_pessoa_tipo", array());
        $sql->Join(array("tps" => "transporte_pessoa_status"), "tps.id_transporte_pessoa_status = tp.id_transporte_pessoa_status", array());
        $sql->Join(array("p" => "pessoa"), "p.id_pessoa = tp.id_pessoa", array("p.id_pessoa"));
        $sql->Join(array("pf" => "pessoa_fisica"), "p.id_pessoa = pf.id_pessoa", array("pf.nome"));
        $sql->Join(array("pm" => "pessoa_motorista"), "pf.id_pessoa_fisica = pm.id_pessoa_fisica", array());

        $sql->where("ss.tipo = 'TV'");
        if (isset($this->dados["id_transporte_grupo"]) && $this->dados["id_transporte_grupo"]) {
            $sql->where("tg.id_transporte_grupo = {$this->dados["id_transporte_grupo"]}");
        }
        if (isset($this->dados["id_transporte_veiculo_status"]) && $this->dados["id_transporte_veiculo_status"]) {
            $sql->where("tvs.id_transporte_veiculo_status = {$this->dados["id_transporte_veiculo_status"]}");
        }
        if (isset($this->dados["id_servico_solicitacao_status"]) && $this->dados["id_servico_solicitacao_status"]) {
            $sql->where("sss.id_servico_solicitacao_status = {$this->dados["id_servico_solicitacao_status"]}");
        }
        $sql->group("v.id_veiculo");
        //die($sql);
        return $db->query($sql);
    }

    public function toHTML()
    {
        $resumo = array();
        $stmt = $this->pegaStatement();
        ob_start();
        if ($stmt && $stmt->rowCount()) {
            $id_tg = 0;

            ?>
            <table class="table table-striped table-bordered">
                <?php
                            while ($obj = $stmt->fetchObject()) {
                                if ($id_tg != $obj->id_transporte_grupo) {
                                    if ($id_tg) {
                                        ?>
                            </body>
                        <?php
                                            }
                                            $id_tg = $obj->id_transporte_grupo;

                                            ?>
                        <thead>
                            <tr>
                                <th colspan="12">Tipo de Transporte - <?php echo $obj->transporte_grupo_descricao; ?></th>
                            </tr>
                            <tr>
                                <th>Chassi</th>
                                <th>Placa</th>
                                <th>Modelo</th>
                                <th>Proprietário</th>
                                <th>Fabricante</th>
                                <th>Ano Fabricação</th>
                                <th>Renavan</th>
                                <th>Codigo da Licença</th>
                                <th>Data Inicial</th>
                                <th>Data Vencimento</th>
                                <th>Status da Licença</th>
                                <th>Status do Veiculo</th>
                            </tr>
                        </thead>
                    <?php } ?>
                    <tbody>
                        <tr>
                            <td>
                                <div class="text-center"><?php echo $obj->chassi; ?></div>
                            </td>
                            <td><?php echo $obj->placa; ?></td>
                            <td><?php echo $obj->modelo; ?></td>
                            <td><?php echo $obj->proprietario; ?></td>
                            <td><?php echo $obj->fabricante; ?></td>
                            <td><?php echo $obj->ano_fabricacao; ?></td>
                            <td><?php echo $obj->renavan; ?></td>
                            <td><?php echo $obj->codigo; ?></td>
                            <td><?php echo Escola_Util::formatData($obj->data_inicio); ?></td>
                            <td><?php echo Escola_Util::formatData($obj->data_vencimento); ?></td>
                            <td><?php echo $obj->ss_status_descricao; ?></td>
                            <td><?php echo $obj->tv_status_descricao; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
            </table>
<?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
