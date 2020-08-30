<?php

class Escola_Relatorio_Servico_DPC extends Escola_Relatorio_Servico_Declaracao
{
    public function getFilename()
    {
        return "declaracao_autorizatario";
    }

    public function getAutoridade()
    {
        return null;
    }

    public function getAssunto()
    {
        return null;
    }

    public function getTextoCentral()
    {
        $transporte = $this->transporte;
        if (!$transporte) {
            return false;
        }
        $tg = $this->transporte_grupo;

        $pessoa = $this->proprietario_pessoa;
        if (!$pessoa) {
            return false;
        }

        $rg = "";
        if ($pessoa->pf() && isset($this->proprietario_pessoa_pf) && $this->proprietario_pessoa_pf) {
            $rg = $this->proprietario_pessoa_pf->identidade_numero;
        }
        $concessao = $transporte->findParentRow("TbConcessao");

        ?>
        <style>
            table tr td {
                text-align: center;
                font-size: 12pt;
            }
        </style>
        <br>
        <br>
        <br>
        <div class="paragrafo">A <?php echo $this->pj->razao_social; ?> - <?php echo $this->pj->sigla; ?>,
            autarquia municipal de regime especial, criada através da Lei N°.: 434/1999,
            inscrita no CNPJ/SRFB/MF sob o N°.: <?php echo $this->pj->mostrar_documento(); ?>,
            Órgão Executivo Municipal de Transportes e Trânsito, declara para os fins de direito,
            que senhor(a) <span class="negrito"><?= $pessoa->mostrar_nome() ?></span>, inscrito(a) sob o
            CPF n°. <span class="negrito"><?= $pessoa->mostrar_documento() ?></span>,
            <?php if ($rg) : ?>
                RG. n°. <span class="negrito"><?= $rg ?></span>,
            <?php endif; ?>
            residente na <?= $pessoa->endereco_extenso() ?>, é autorizatário(a) do Serviço de
            <span class="negrito"><?= $tg->descricao ?></span>, matrícula <span class="negrito"><?= $transporte->codigo ?></span>,
            outorgada através do <span class="negrito">Decreto <?= $concessao->decreto ?></span>, em <span class="negrito"><?= Escola_Util::formatData($concessao->concessao_data) ?></span>, em caráter <span class="negrito"><?php echo $concessao->findParentRow("TbConcessaoTipo")->toString(); ?></span>.
        </div>
        <br>
        <br><br>
<?php
    }
}
