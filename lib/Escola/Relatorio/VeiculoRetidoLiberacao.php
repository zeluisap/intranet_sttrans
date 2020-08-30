<?php

class Escola_Relatorio_VeiculoRetidoLiberacao extends Escola_Relatorio
{

    private $veiculo_retido = false;

    public function set_veiculo_retido($vr)
    {
        $this->veiculo_retido = $vr;
    }

    public function get_veiculo_retido()
    {
        return $this->veiculo_retido;
    }

    public function __construct()
    {
        $filename = "relatorio_veiculo_retido_liberacao";
        parent::__construct($filename);
        $this->SetTopMargin(45);
    }

    public function validarEmitir()
    {
        $errors = array();
        if (!$this->veiculo_retido->liberado()) {
            $errors[] = "Veículo Retido Não Liberado!";
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }

    public function imprimir()
    {

        $txt_pessoa_nome = $txt_pessoa_cpf = $txt_pj = $txt_veiculo_marca = $txt_veiculo_placa = $txt_veiculo_cor = $txt_apreensao_data = $txt_apreensao_hora = $txt_dia_semana = $txt_auto_infracao = "";
        $txt_cidade_uf = $txt_data_dia = $txt_data_mes = $txt_data_ano = "";

        $erros = $this->validarEmitir();
        if ($erros) {
            throw new Exception(implode("<br>", $erros));
        }

        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        if (!$sistema) {
            throw new Exception("FALHA AO EXECUTAR OPERAÇAO, DADOS INVALIDOS!");
        }

        $pj = $sistema->findParentRow("TbPessoaJuridica");

        if (!$pj) {
            throw new Exception("FALHA AO EXECUTAR OPERAÇAO, DADOS INVALIDOS!!");
        }

        $txt_pj = $pj->toString();

        $tb = new TbMoeda();
        $moeda = $tb->pega_padrao();

        $ain = $this->veiculo_retido->pegaAutoInfracaoNotificacao();

        if (!$ain) {
            throw new Exception("FALHA AO EXECUTAR OPERAÇAO, DADOS INVALIDOS!!!");
        }

        $ai = $ain->pegaAutoInfracao();

        if (!$ai) {
            throw new Exception("FALHA AO EXECUTAR OPERAÇAO, DADOS INVALIDOS!!!!");
        }

        $motorista = $ain->pegaPessoaFisica();
        if ($motorista) {
            $txt_pessoa_nome = $motorista->toString();
            $txt_pessoa_cpf = Escola_Util::formatCPF($motorista->cpf);
        }
        $veiculo = $ain->pegaVeiculo();
        if ($veiculo) {
            $item = array();
            $marca = $veiculo->findParentRow("TbFabricante");
            if ($marca) {
                $item[] = $marca->toString();
            }
            $item[] = $veiculo->modelo;
            $txt_veiculo_marca = implode(" - ", $item);
            if ($veiculo->placa) {
                $txt_veiculo_placa = $veiculo->placa;
            } else {
                $txt_veiculo_placa = "S/P";
            }
            $cor = $veiculo->findParentRow("TbCor");
            if ($cor) {
                $txt_veiculo_cor = $cor->toString();
            }
        }
        $txt_apreensao_data = Escola_Util::formatData($ain->data_infracao);
        $txt_apreensao_hora = $ain->hora_infracao;
        $txt_dia_semana = Escola_Util::pegaDiaSemana(new Zend_Date($ain->data_infracao));
        $txt_auto_infracao = $ai->toString();

        $txt_cidade_uf = "Santana - AP";
        $txt_data_dia = date("d");
        $txt_data_mes = Escola_Util::pegaMes(date("n"));
        $txt_data_ano = date("Y");

        $this->addPage();
        $this->css();
        ?>
        <p class="centro titulo negrito">COMPROVANTE DE RECIBO</p>
        <p></p>
        <p></p>
        <p></p>
        <p></p>
        <p></p>
        <p class="paragrafo">EU, <strong><?php echo $txt_pessoa_nome; ?></strong>, CPF No.: <strong><?php echo $txt_pessoa_cpf; ?></strong> recebi da <?php echo $txt_pj; ?>, <strong>01 (UM) VEÍCULO</strong> Marca / Modelo: <strong><?php echo $txt_veiculo_marca; ?></strong> de Placa: <strong><?php echo $txt_veiculo_placa; ?></strong>, COR: <strong><?php echo $txt_veiculo_cor; ?></strong>, apreendido no dia <?php echo $txt_apreensao_data; ?> (<?php echo $txt_dia_semana; ?>), às <?php echo $txt_apreensao_hora; ?> através do Auto de Transporte No.: <strong><?php echo $txt_auto_infracao; ?></strong>.</p>
        <p></p>
        <p></p>
        <p></p>
        <p></p>
        <p class="direita"><?php echo $txt_cidade_uf; ?>, <?php echo $txt_data_dia; ?> de <?php echo $txt_data_mes; ?> de <?php echo $txt_data_ano; ?>.</p>
        <p></p>
        <p></p>
        <p></p>
        <p></p>
        <p></p>
        <p class="centro">_______________________________________________________<br>PROPRIETÁRIO OU PROCURADOR</p>
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');

            $this->lastPage();
            $this->download();
        }

        public function css()
        {
            ?>
        <style type="text/css">
            p {
                font-size: 12pt;
            }

            p.paragrafo {
                text-indent: 100px;
                text-align: justify;
                line-height: 7px;
            }

            p.direita {
                text-align: right;
            }

            p.centro {
                text-align: center;
            }

            p.titulo {
                line-height: 20px;
                font-size: 18pt;
                text-decoration: underline;
            }

            p.negrito {
                font-weight: bold;
            }
        </style>
<?php
    }
}
