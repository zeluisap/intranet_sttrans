<?php

class Escola_Relatorio_Default_Ttr_Taxassacado extends Escola_Relatorio_Default
{

    public function validarEmitir()
    {
        $data_inicio = $data_fim = false;
        $errors = array();
        if (!isset($this->dados["data_inicio"]) || !$this->dados["data_inicio"]) {
            $errors[] = "CAMPO DATA INICIAL NÃO INFORMADO!";
        } elseif (Escola_Util::validaData($this->dados["data_inicio"])) {
            $errors[] = "CAMPO DATA INICIAL INVÁLIDO!";
        } else {
            $data_inicio = new Zend_Date(Escola_Util::montaData($this->dados["data_inicio"]));
        }
        if (!isset($this->dados["data_fim"]) || !$this->dados["data_fim"]) {
            $errors[] = "CAMPO DATA FINAL NÃO INFORMADO!";
        } elseif (Escola_Util::validaData($this->dados["data_fim"])) {
            $errors[] = "CAMPO DATA FINAL INVÁLIDO!";
        } else {
            $data_fim = new Zend_Date(Escola_Util::montaData($this->dados["data_fim"]));
        }
        if ($data_inicio && $data_fim && ($data_inicio->isLater($data_fim))) {
            $errors[] = "DATA INICIAL POSTERIOR A DATA FINAL!";
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
        $estilo_negrito = $workbook->addFormat(array("size" => 9, "Border" => 1, "Bold" => 1, "VAlign" => "vcenter"));
        $estilo_normal_centro = $workbook->addFormat(array("size" => 9, "Border" => 1, "Align" => "center", "VAlign" => "vcenter"));
        $estilo_cabecalho = $workbook->addFormat(array("size" => 11, "Border" => 1, "Align" => "center", "Bold" => 1, "VAlign" => "vcenter"));
        $worksheet = $workbook->addWorksheet("relatorio_taxassacado");
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
                $pessoa = TbPessoa::pegaPorId($obj->id_pessoa);
                $filho = $pessoa->pegaPessoaFilho();
                $worksheet->writeString($linha, $coluna++, Escola_Util::formatData($obj->data_pagamento), $estilo_normal_centro);
                $worksheet->writeString($linha, $coluna++, $filho->mostrar_nome(), $estilo_normal);
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
        $workbook->send("relatorio_Taxassacado.xls");
        $workbook->close();
        die();
    }

    public function pegaStatement()
    {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("svs" => "servico_solicitacao"), array());
        $sql->join(array("stg" => "servico_transporte_grupo"), "svs.id_servico_transporte_grupo = stg.id_servico_transporte_grupo", array());
        $sql->join(array("tg" => "transporte_grupo"), "stg.id_transporte_grupo = tg.id_transporte_grupo", array("tg.id_transporte_grupo", "transporte_grupo_descricao" => "tg.descricao"));
        $sql->join(array("s" => "servico"), "stg.id_servico = s.id_servico", array());
        $sql->Join(array("st" => "servico_tipo"), "s.id_servico_tipo = st.id_servico_tipo", array());
        $sql->join(array("v" => "valor"), "svs.id_valor = v.id_valor", array("valor_servico" => "sum(v.valor)"));
        $sql->Join(array("sss" => "servico_solicitacao_status"), "sss.id_servico_solicitacao_status = svs.id_servico_solicitacao_status", array());
        $sql->Join(array("ssp" => "servico_solicitacao_pagamento"), "svs.id_servico_solicitacao = ssp.id_servico_solicitacao", array("ssp.data_pagamento"));
        $sql->Join(array("ssps" => "servico_solicitacao_pagamento_status"), "ssps.id_servico_solicitacao_pagamento_status = ssp.id_servico_solicitacao_pagamento_status", array());

        $sql->Join(array("t" => "transporte"), "t.id_transporte = svs.id_transporte", array());
        $sql->Join(array("tp" => "transporte_pessoa"), "tp.id_transporte = t.id_transporte", array());
        $sql->Join(array("tpt" => "transporte_pessoa_tipo"), "tpt.id_transporte_pessoa_tipo = tp.id_transporte_pessoa_tipo", array());
        $sql->Join(array("tps" => "transporte_pessoa_status"), "tps.id_transporte_pessoa_status = tp.id_transporte_pessoa_status", array());
        $sql->Join(array("p" => "pessoa"), "p.id_pessoa = tp.id_pessoa", array("p.id_pessoa"));

        $sql->where("tpt.chave = 'PR'");
        $sql->where("tps.chave = 'A'");

        $sql->where("st.chave = 'TR'");
        $sql->where("ssps.chave = 'A'");

        if (isset($this->dados["id_transporte_grupo"]) && $this->dados["id_transporte_grupo"]) {
            $sql->where("tg.id_transporte_grupo = {$this->dados["id_transporte_grupo"]}");
        }

        if (isset($this->dados["data_inicio"]) && $this->dados["data_inicio"]) {
            $data_inicio = Escola_Util::montaData($this->dados["data_inicio"]);
            $sql->where("ssp.data_pagamento >= '{$data_inicio}'");
        }

        if (isset($this->dados["data_fim"]) && $this->dados["data_fim"]) {
            $data_fim = Escola_Util::montaData($this->dados["data_fim"]);
            $sql->where("ssp.data_pagamento <= '{$data_fim}'");
        }

        if (isset($this->dados["nome_proprietario"]) && $this->dados["nome_proprietario"]) {
            $db = Zend_Registry::get("db");
            $sql_pf = $db->select();
            $sql_pf->from(array("pf" => "pessoa_fisica"), array("id_pessoa"));
            $sql_pf->where("pf.nome like '%{$this->dados["nome_proprietario"]}%'");
            $sql_pj = $db->select();
            $sql_pj->from(array("pj" => "pessoa_juridica"), array("id_pessoa"));
            $sql_pj->where("pj.razao_social like '%{$this->dados["nome_proprietario"]}%'");
            $sql->Where("(p.id_pessoa in ({$sql_pf}) or p.id_pessoa in ({$sql_pj}))");
        }

        $sql->group("tg.descricao");
        $sql->group("tg.id_transporte_grupo");
        $sql->group("ssp.data_pagamento");
        $sql->group("p.id_pessoa");

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
                                            if (!array_key_exists($id_tg, $resumo)) {
                                                $item = new stdClass();
                                                $item->id_transporte_grupo = $obj->id_transporte_grupo;
                                                $item->transporte_grupo_descricao = $obj->transporte_grupo_descricao;
                                                $item->valor = 0;
                                                $resumo[$id_tg] = $item;
                                            }
                                            ?>
                        <thead>
                            <tr>
                                <th colspan="3">Tipo de Transporte - <?php echo $obj->transporte_grupo_descricao; ?></th>
                            </tr>
                            <tr>
                                <th>Data</th>
                                <th>Nome</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                                        }
                                        $pessoa = TbPessoa::pegaPorId($obj->id_pessoa);
                                        $filho = $pessoa->pegaPessoaFilho();
                                        $resumo[$obj->id_transporte_grupo]->valor = $resumo[$obj->id_transporte_grupo]->valor + $obj->valor_servico;
                                        ?>
                        <tr>
                            <td>
                                <div class="text-center"><?php echo Escola_Util::formatData($obj->data_pagamento); ?></div>
                            </td>
                            <td><?php echo $filho->mostrar_nome(); ?></td>
                            <td>
                                <div class="text-center"><?php echo Escola_Util::number_format($obj->valor_servico); ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                        </tbody>
                        <?php if (count($resumo)) { ?>
            </table>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th colspan="2">Resumo</th>
                    </tr>
                    <tr>
                        <th>Tipo de Veiculo</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <body>
                    <?php foreach ($resumo as $item_resumo) { ?>
                        <tr>
                            <td><?php echo $item_resumo->transporte_grupo_descricao; ?></td>
                            <td>
                                <div class="text-center">
                                    <div class="text-center"><?php echo Escola_Util::number_format($item_resumo->valor); ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                </body>
            </table>
<?php
            }
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
