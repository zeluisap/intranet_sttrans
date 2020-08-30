<?php

class Escola_Relatorio_Servico_Autorizacao extends Escola_Relatorio_Servico
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
        return "autorizacao";
    }

    public function getServicoAFazer()
    {
        return "<especificar o serviço>";
    }

    public function getOrgao()
    {
        return "<especificar órgão>";
    }

    public function getTextoCentro()
    {
        $transporte = $this->registro->pegaTransporte();
        if (!$transporte) {
            return false;
        }
        $tg = $transporte->findParentRow("TbTransporteGrupo");
        ?>
        <div class="paragrafo">A <?php echo $this->pj->sigla; ?> - <?php echo $this->pj->mostrar_nome(); ?>, através desta, autoriza o detentor da Permissão de Serviço de <?php echo $tg->toString(); ?>, executar junto ao <?= $this->getOrgao() ?>, o serviço de:<br />
            <span class="negrito font_14pt">* <?= $this->getServicoAFazer() ?></span> do veículo abaixo:</div>
    <?php
        }

        public function header()
        { }

        public function validarEmitir()
        {
            $p_errors = parent::validarEmitir();
            $errors = array();
            $transporte = $this->registro->pegaTransporte();
            if (!$transporte) {
                $errors[] = "NENHUM TRANSPORTE VINCULADO!";
            } else {
                if ($this->registro->veiculo()) {
                    $tv = $this->registro->pegaReferencia();
                    if ($tv) {
                        $veiculo = $tv->findParentRow("TbVeiculo");
                    }
                } else {
                    $veiculo = $transporte->pegaVeiculo();
                }
                if (!$veiculo) {
                    $errors[] = "TRANSPORTE NÃO POSSUI VEÍCULO VINCULADO!";
                } else {
                    if ($veiculo->retido()) {
                        $errors[] = "VEÍCULO VINCULADO AO TRANSPORTE ENCONTRA-SE RETIDO AO PÁTIO DA STTRANS!";
                    }
                }
            }
            if ($p_errors) {
                $errors = array_merge($p_errors, $errors);
            }
            if (count($errors)) {
                return $errors;
            }
            return false;
        }

        public function toPDF()
        {
            if (!$this->registro) {
                return false;
            }

            $transporte = $this->transporte;
            if (!$transporte) {
                return false;
            }


            $this->setFilename($this->getFilename() . "_" . $this->registro->ano_referencia . "_" . Escola_Util::zero($this->registro->codigo, 4));
            $proprietario = $this->proprietario;
            $this->AddPage();
            ob_start();
            $this->cabecalho();
            $this->css();
            ?>
        <?php
                if ($proprietario) {
                    $pessoa = $proprietario->findParentRow("TbPessoa");
                    if ($pessoa) {
                        $nome_pessoa_doc = "C.P.F.";
                        if ($pessoa->pj()) {
                            $nome_pessoa_doc = "C.N.P.J.";
                        }
                        $proprietario_pf = $pessoa->pegaPessoaFilho();
                        $endereco = $pessoa->getEndereco();
                        $endereco_txt = "";
                        if ($endereco) {
                            $endereco_txt = $endereco->logradouro;
                            if ($endereco->numero) {
                                $endereco_txt .= ", " . $endereco->numero;
                            }
                            if ($endereco->complemento) {
                                $endereco_txt .= "-" . $endereco->complemento;
                            }
                            $bairro = $endereco->findParentRow("TbBairro");
                            if ($bairro) {
                                $endereco_txt .= " - " . $bairro->descricao;
                            }
                        }
                    }
                }
                $concessao = $transporte->findParentRow("TbConcessao");
                ?>
        <div class="centro fonte_16pt negrito">DIRETORIA DE TRANSPORTES</div>
        <div class="centro fonte_14pt">AUTORIZAÇÃO No.: <span class="negrito"><?php echo $this->registro->mostrar_numero(); ?></span></div>
        <div></div>
        <div class="normal">Matrícula: <?php echo $transporte->mostrar_codigo(); ?><br />
            Nome: <?php echo $proprietario_pf->mostrar_nome(); ?><br />
            <?= $nome_pessoa_doc ?>: <?php echo $proprietario_pf->mostrar_documento(); ?><br />
            Endereço: <?php echo $endereco->toString(); ?></div>
        <div></div>

        <?= $this->getTextoCentro() ?>

        <?= $this->showVeiculoLista() ?>
        <div></div>
        <div></div>
        <div class="centro"><?php echo $this->pj->sigla; ?></div>
        <div></div>
        <div></div>
        <div class="negrito">DATA EMISSÃO: <?= date("d/m/Y") ?>.</div>
        <div class="negrito">VALIDO ATÉ: <?= $this->showValidade() ?>.</div>
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
            $this->lastPage();
            $this->download();
        }

        public function css()
        {
            echo parent::css();
            ?>
        <style type="text/css">
            div {
                font-size: 12pt;
            }

            .tabela {
                border: 2px solid #000;
            }

            .titulo_servico {
                font-family: "Times New Roman";
                font-size: 20pt;
                font-style: italic;
            }

            .titulo_servico_mini {
                font-family: "Arial";
                font-style: normal;
                font-weight: bold;
                font-size: 12pt;
            }

            .negrito {
                font-weight: bold;
            }

            .rr {
                background-color: #ccc;
            }

            .campo_legenda {
                font-size: 10pt;
            }

            .campo_valor {
                font-size: 12pt;
            }

            .declaracao {
                font-size: 22pt;
                font-weight: bold;
                text-align: center;
            }
        </style>
<?php
    }
}
