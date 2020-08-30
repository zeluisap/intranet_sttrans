<?php

class Escola_Relatorio_Servico_DEIPVA extends Escola_Relatorio_Servico_Declaracao
{
    public function getFilename()
    {
        return "declaracao_isencao_ipva";
    }

    public function getAssunto()
    {
        return "Isenção de I.P.V.A. ({$this->registro->ano_referencia})";
    }

    public function getAutoridade()
    {
        return "Secretário da SEFAZ";
    }

    public function getTextoCentral()
    {
        $transporte = $this->registro->pegaTransporte();
        if (!$transporte) {
            return false;
        }
        $tg = $transporte->getTransporteGrupo();

        $proprietario = $transporte->pegaProprietario();
        if ($proprietario) {
            $pessoa = $proprietario->findParentRow("TbPessoa");
        }
        $concessao = $transporte->findParentRow("TbConcessao");

        $tv = $this->registro->pegaReferencia();
        $dados_veiculo = Escola_DbUtil::first("
            select 
                upper(vm.descricao) as marca,
                upper(v.modelo),
                v.ano_fabricacao,
                v.ano_modelo,
                coalesce(c.descricao, '--') as cor,
                coalesce(v.chassi, '--') as chassi,
                coalesce(v.placa, '--') as placa
            from transporte_veiculo tv
                left outer join veiculo v on tv.id_veiculo = v.id_veiculo
                left outer join fabricante vm on v.id_fabricante = vm.id_fabricante
                left outer join cor c on v.id_cor = c.id_cor
            where (id_transporte_veiculo = ?)
        ", [$tv->id_transporte_veiculo]);
        $txt_ano = [];
        if ($dados_veiculo->ano_fabricacao) {
            $txt_ano[] = $dados_veiculo->ano_fabricacao;
        }
        if ($dados_veiculo->ano_modelo) {
            $txt_ano[] = $dados_veiculo->ano_modelo;
        }

        $txt_marca_modelo = [];
        if ($dados_veiculo->marca) {
            $txt_marca_modelo[] = $dados_veiculo->marca;
        }
        if ($dados_veiculo->modelo) {
            $txt_marca_modelo[] = $dados_veiculo->modelo;
        }
        ?>
        <style>
            table tr td {
                text-align: center;
                font-size: 10pt;
            }
        </style>
        <div class="paragrafo">A <?php echo $this->pj->razao_social; ?>-<?php echo $this->pj->sigla; ?>,
            autarquia municipal de regime especial, criada através da Lei N°.: 434/1999,
            inscrita no CNPJ/SRFB/MF sob o N°.: <?php echo $this->pj->mostrar_documento(); ?>,
            Órgão Executivo Municipal de Transportes e Trânsito, declara para os fins de direito,
            que o veículo abaixo individuado, encontra-se registrado no Cadastro de Veículos de Aluguel
            na Categoria de <?= $tg->descricao ?>, vinculado a permissão <?= $transporte->codigo ?>,
            de propriedade do(a) Sr(a). <?= $pessoa->mostrar_nome() ?>, outorgada através do Decreto n°
            <span class="negrito"><?php echo $concessao->decreto; ?></span> em caráter <span class="negrito"><?php echo $concessao->findParentRow("TbConcessaoTipo")->toString(); ?></span>.
        </div>
        <br>
        <!-- <table border="1" cellpadding="10">
            <tr class="negrito">
                <td width="130px">Marca / Modelo</td>
                <td width="90px">Ano / Fab</td>
                <td>Cor</td>
                <td width="160px">Chassi</td>
                <td width="90px">Placa</td>
            </tr>
            <tr>
                <td><?= implode(" / ", $txt_marca_modelo) ?></td>
                <td><?= implode(" / ", $txt_ano) ?></td>
                <td><?= $dados_veiculo->cor ?></td>
                <td><?= $dados_veiculo->chassi ?></td>
                <td><?= $dados_veiculo->placa ?></td>
            </tr>
        </table> -->
        <?= $this->showVeiculoLista() ?>
<?php
    }
}
