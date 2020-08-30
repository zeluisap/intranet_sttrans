<?php

class Escola_Relatorio_Servico_Declaracao extends Escola_Relatorio_Servico
{

    public function __construct()
    {
        parent::__construct();
        $this->setFilename($this->getFilename());
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        $this->SetTopMargin(20);
        $this->SetTopMargin(10);
    }

    public function getFilename()
    {
        return "declaracao";
    }

    public function header()
    { }

    public function getTextoCentral()
    {
        $transporte = $this->registro->pegaTransporte();
        if (!$transporte) {
            return false;
        }

        $proprietario = $transporte->pegaProprietario();
        if ($proprietario) {
            $pessoa = $proprietario->findParentRow("TbPessoa");
            if ($pessoa) {
                $proprietario_pf = $pessoa->pegaPessoaFilho();
            }
        }
        $concessao = $transporte->findParentRow("TbConcessao");
        ?>
        <div class="paragrafo">A <?php echo $this->pj->razao_social; ?>-<?php echo $this->pj->sigla; ?>,
            autarquia municipal de regime especial, criada através da Lei N°.: 434/1999,
            inscrita no CNPJ/SRFB/MF sob o N°.: <?php echo $this->pj->mostrar_documento(); ?>,
            Órgão Executivo Municipal de Transportes e Trânsito, declara para os fins de direito,
            que o(a) Senhor(a) <span class="negrito"><?php echo $proprietario_pf->mostrar_nome(); ?></span>,
            RG n° <?php echo $proprietario_pf->mostrar_identidade(); ?> e CPF n° <?php echo $pessoa->mostrar_documento(); ?>
            residente na <?= $pessoa->mostrar_endereco() ?>,
            é autorizatário na Categoria Transporte Individual de Passageiros - Táxi,
            matrícula <span class="negrito"><?php echo $transporte->mostrar_codigo(); ?></span>,
            outorgada através do Decreto n° <span class="negrito"><?php echo $concessao->decreto; ?></span> em <?= Escola_Util::formatData($concessao->concessao_data) ?>
            em caráter <span class="negrito"><?php echo $concessao->findParentRow("TbConcessaoTipo")->toString(); ?></span>.</div>
    <?php
        }

        public function toPDF()
        {
            $autoridade = $this->getAutoridade();
            $assunto = $this->getAssunto();
            $this->AddPage();
            ob_start();
            $this->cabecalho();
            $this->css();
            ?>
        <div class="esquerda"><?= $this->showDataExtenso() ?></div>
        <div>Declaração N°.: <span class="negrito"><?php echo $this->registro->mostrar_numero(); ?></span>-<?php echo $this->pj->sigla; ?>.</div>
        <div></div>
        <?php if ($autoridade) { ?>
            <div>Ao Ilmo. Senhor<br /><?= $autoridade ?>.</div>
        <?php } ?>
        <p></p>
        <?php if ($assunto) { ?>
            <div>Assunto: <span class="negrito"><?= $assunto ?></span>.</div>
        <?php } ?>
        <p></p>
        <div class="declaracao"><u>DECLARAÇÃO</u></div>
        <p></p>
        <p></p>
        <?= $this->getTextoCentral() ?>
        <p></p>
        <p></p>
        <div class="centro"><?php echo $this->pj->sigla; ?></div>
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
            $this->lastPage();
            $this->download();
            // $this->show();
        }

        public function css()
        {
            parent::css();
            ?>
        <style type="text/css">
            div {
                font-size: 12pt;
            }

            .declaracao {
                font-size: 16pt;
                font-weight: bold;
                text-align: center;
            }

            .fonte_14pt {
                font-size: 14pt;
            }
        </style>
<?php
    }
}
