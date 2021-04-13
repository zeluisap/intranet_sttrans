<?php
class Escola_Relatorio_Default_Ttr_Debitos extends Escola_Relatorio_Default
{

    public function toPDF()
    {
        $pdf_class_name = get_class($this) . "_Pdf";
        $zla = Zend_Loader_Autoloader::getInstance();

        if ($zla->autoload($pdf_class_name)) {
            return;
        }

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

    public function pegaCampos()
    {
        return [
            "transporte_grupo",
            "permissionario",
            "codigo",
            "vinculo",
            "servico",
            "data_documento",
            "vencimento",
            "valor_original",
            "juros",
            "multa",
            "valor_final"
        ];
    }

    public function toXLS()
    {
        $campos = $this->pegaCampos();
        $lista = $this->pegaLista();

        $workbook = new Spreadsheet_Excel_Writer();

        $estilo_normal = $workbook->addFormat(array("size" => 9, "Border" => 1, "VAlign" => "vcenter"));
        $estilo_negrito = $workbook->addFormat(array("size" => 9, "Border" => 1, "Bold" => 1, "VAlign" => "vcenter"));
        $estilo_normal_centro = $workbook->addFormat(array("size" => 9, "Border" => 1, "Align" => "center", "VAlign" => "vcenter"));
        $estilo_cabecalho = $workbook->addFormat(array("size" => 11, "Border" => 1, "Align" => "center", "Bold" => 1, "VAlign" => "vcenter"));

        $worksheet = $workbook->addWorksheet("relatorio_debitos");
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
        $linha = 0;

        if ($lista && count($lista)) {
            $coluna = 0;
            foreach ($campos as $campo) {
                $worksheet->writeString($linha, $coluna++, $campo, $estilo_negrito);
            }

            $linha++;
            foreach ($lista as $obj) {
                $coluna = 0;
                foreach ($campos as $campo) {
                    $worksheet->writeString($linha, $coluna++, Escola_Util::valorOuCoalesce($obj, "txt_" . $campo, "--"), $estilo_normal);
                }

                $linha++;
            }
        }

        $workbook->send("relatorio_debitos_" . date("YmdHis") . ".xls");
        $workbook->close();
        die();
    }

    public function pegaStatement()
    {
        $db = Zend_Registry::get("db");

        $sql = "

            select
                    ss.id_servico_solicitacao as id,
                    tg.id_transporte_grupo,
                    tg.descricao as transporte_grupo,
                    t.id_transporte,
                        (
                    select
                        coalesce(pf.cpf, pj.cnpj, '') as doc_permissionario
            
                    from transporte_pessoa tp
                        left outer join transporte_pessoa_tipo tpt on tpt.id_transporte_pessoa_tipo = tp.id_transporte_pessoa_tipo
                        left outer join pessoa p on tp.id_pessoa = p.id_pessoa
                        left outer join pessoa_tipo pt on p.id_pessoa_tipo = pt.id_pessoa_tipo
                        left outer join pessoa_fisica pf on (p.id_pessoa = pf.id_pessoa and lower(pt.chave) = 'pf')
                        left outer join pessoa_juridica pj on (p.id_pessoa = pj.id_pessoa and lower(pt.chave) = 'pj')
            
                    where (lower(tpt.chave) = 'pr')
                    and (tp.id_transporte = t.id_transporte)
            
                    limit 1
                ) as doc_permissionario,
            
                (
                    select
                        coalesce(pf.nome, pj.nome_fantasia, '') as nome_permissionario
            
                    from transporte_pessoa tp
                        left outer join transporte_pessoa_tipo tpt on tpt.id_transporte_pessoa_tipo = tp.id_transporte_pessoa_tipo
                        left outer join pessoa p on tp.id_pessoa = p.id_pessoa
                        left outer join pessoa_tipo pt on p.id_pessoa_tipo = pt.id_pessoa_tipo
                        left outer join pessoa_fisica pf on (p.id_pessoa = pf.id_pessoa and lower(pt.chave) = 'pf')
                        left outer join pessoa_juridica pj on (p.id_pessoa = pj.id_pessoa and lower(pt.chave) = 'pj')
            
                    where (lower(tpt.chave) = 'pr')
                    and (tp.id_transporte = t.id_transporte)
            
                    limit 1
                ) as nome_permissionario,
            
                    t.codigo,
                    ss.tipo, ss.chave,
            
                    CASE lower(ss.tipo)
                        WHEN 'tv' THEN (
                                select concat(v2.chassi, ' - ', vt.descricao, ' - ', v2.placa, ' - ', f.descricao)
                                from transporte_veiculo tv
                                    left outer join veiculo v2 on tv.id_veiculo = v2.id_veiculo
                                    left outer join veiculo_tipo vt on v2.id_veiculo_tipo = vt.id_veiculo_tipo
                                    left outer join fabricante f on v2.id_fabricante = f.id_fabricante
                                where (tv.id_transporte_veiculo = ss.chave)
                            )
                        WHEN 'tp' THEN (
            
                        select
                            concat(coalesce(pf2.cpf, pj2.cnpj, ''), ' - ', coalesce(pf2.nome, pj2.nome_fantasia, '')) as nome_pessoa
            
                            from transporte_pessoa tp2
                                left outer join transporte_pessoa_tipo tpt2 on tpt2.id_transporte_pessoa_tipo = tp2.id_transporte_pessoa_tipo
                                left outer join pessoa p2 on tp2.id_pessoa = p2.id_pessoa
                                left outer join pessoa_tipo pt2 on p2.id_pessoa_tipo = pt2.id_pessoa_tipo
                                left outer join pessoa_fisica pf2 on (p2.id_pessoa = pf2.id_pessoa and lower(pt2.chave) = 'pf')
                                left outer join pessoa_juridica pj2 on (p2.id_pessoa = pj2.id_pessoa and lower(pt2.chave) = 'pj')
            
                            where tp2.id_transporte_pessoa = ss.chave
            
                            limit 1
                        )
                        ELSE null
                    END as servico_vinculo,
            
                    s.descricao as servico,
            
                    ss.data_solicitacao,
                    ss.data_validade,
            
                    ss.data_vencimento,
            
                    v.valor as valor_original
            
            from servico_solicitacao ss
                left outer join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
                left outer join transporte_grupo tg on stg.id_transporte_grupo = tg.id_transporte_grupo
                left outer join transporte t on ss.id_transporte = t.id_transporte
                left outer join transporte_pessoa tp on t.id_transporte = tp.id_transporte
                left outer join servico s on stg.id_servico = s.id_servico
                left outer join valor v on ss.id_valor = v.id_valor
                left outer join servico_solicitacao_status sss on ss.id_servico_solicitacao_status = sss.id_servico_solicitacao_status
            
            where (lower(sss.chave) = 'ag')
            and (lower(ss.tipo in ('tr', 'tv', 'tp')))
            
            order by 2, 6

        ";

        return $db->query($sql);
    }

    public function pegaLista()
    {

        $stmt = $this->pegaStatement();

        if (!($stmt && $stmt->rowCount())) {
            return;
        }

        $tb = new TbServicoSolicitacao();

        $lista = [];

        while ($obj = $stmt->fetchObject()) {

            $ss = $tb->getPorId(Escola_Util::valorOuNulo($obj, "id"));

            $valor_original = Escola_Util::valorOuNulo($obj, "valor_original");

            $desconjuros = TbDesconjuros::calcular($ss);

            $juros = $multa = 0;

            if ($desconjuros && count($desconjuros)) {
                foreach ($desconjuros as $desconjuro) {
                    $tipo = Escola_Util::valorOuNulo($desconjuro, "tipo");
                    $valor = Escola_Util::valorOuNulo($desconjuro, "valor");
                    if (!$tipo) {
                        continue;
                    }

                    if ($tipo == "juros") {
                        $juros += $valor;
                        continue;
                    }

                    if ($tipo == "multa") {
                        $multa += $valor;
                        continue;
                    }
                }
            }

            $valor_final = $valor_original + $juros + $multa;

            $lista_item = [
                "id_tg" => "--",
                "txt_transporte_grupo" => "--",
                "txt_permissionario" => "--",
                "txt_codigo" => "--",
                "txt_vinculo" => "--",
                "txt_servico" => "--",
                "txt_data_documento" => "--",
                "txt_vencimento" => "--",
                "txt_valor_original" => "--",
                "txt_juros" => "--",
                "txt_multa" => "--",
                "txt_valor_final" => "--"
            ];

            $txt = Escola_Util::valorOuNulo($obj, "id_transporte_grupo");
            if ($txt) {
                $lista_item["id_tg"] = $txt;
            }

            $txt = Escola_Util::valorOuNulo($obj, "transporte_grupo");
            if ($txt) {
                $lista_item["txt_transporte_grupo"] = $txt;
            }

            $txt = Escola_Util::valorOuNulo($obj, "nome_permissionario");
            if ($txt) {
                $lista_item["txt_permissionario"] = $txt;
            }

            $txt = Escola_Util::valorOuNulo($obj, "codigo");
            if ($txt) {
                $lista_item["txt_codigo"] = $txt;
            }

            $txt = Escola_Util::valorOuNulo($obj, "servico_vinculo");
            if ($txt) {
                $lista_item["txt_vinculo"] = $txt;
            }

            $txt = Escola_Util::valorOuNulo($obj, "servico");
            if ($txt) {
                $lista_item["txt_servico"] = $txt;
            }

            $txt = Escola_Util::valorOuNulo($obj, "data_solicitacao");
            if ($txt) {
                $lista_item["txt_data_documento"] = Escola_Util::formatData($txt);
            }

            $txt = Escola_Util::valorOuNulo($obj, "data_vencimento");
            if ($txt) {
                $lista_item["txt_vencimento"] = Escola_Util::formatData($txt);
            }

            $valor_original = Escola_Util::valorOuNulo($obj, "valor_original");
            if ($valor_original) {
                $lista_item["txt_valor_original"] = Escola_Util::number_format($valor_original);
            }

            if ($juros) {
                $lista_item["txt_juros"] = Escola_Util::number_format($juros);
            }

            if ($multa) {
                $lista_item["txt_multa"] = Escola_Util::number_format($multa);
            }

            if ($valor_final) {
                $lista_item["txt_valor_final"] = Escola_Util::number_format($valor_final);
            }

            $lista[] = $lista_item;
        }

        return $lista;
    }

    public function toHTML()
    {

        $lista = $this->pegaLista();

        ob_start();
        $id_tg = 0;
?>
        <table class="table table-striped table-bordered">
            <?php
            foreach ($lista as $obj) {

                $obj_id_tg = Escola_Util::valorOuNulo($obj, "id_tg");
                if ($id_tg != $obj_id_tg) {
                    if ($id_tg) {
            ?>
                        </body>
                    <?php
                    }
                    $id_tg = $obj_id_tg;
                    ?>
                    <thead>
                        <tr>
                            <th colspan="10">Tipo de Transporte - <?php echo Escola_Util::valorOuCoalesce($obj, "txt_transporte_grupo", "--"); ?></th>
                        </tr>
                        <tr>
                            <th>Permissionário</th>
                            <th>Transporte código</th>
                            <th>Vínculo</th>
                            <th>Serviço</th>
                            <th>Data documento</th>
                            <th>Vencimento</th>
                            <th>Valor original</th>
                            <th>Juros</th>
                            <th>Multa</th>
                            <th>Valor final</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php }


                    ?>
                    <tr>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_permissionario", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_codigo", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_vinculo", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_servico", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_data_documento", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_vencimento", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_valor_original", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_juros", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_multa", "--"); ?></td>
                        <td><?php echo Escola_Util::valorOuCoalesce($obj, "txt_valor_final", "--"); ?></td>
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
