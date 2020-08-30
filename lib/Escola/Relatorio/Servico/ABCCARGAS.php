<?php

class Escola_Relatorio_Servico_ABCCARGAS extends Escola_Relatorio_Servico {

    public function __construct() {
        parent::__construct();
        $this->setFilename("autorizacao_baixa_categoria_particular");
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        $this->SetTopMargin(20);
        $this->SetTopMargin(10);
    }

    public function header() {
        
    }

    public function validarEmitir() {
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

    public function toPDF() {
        if (!$this->registro) {
            throw new Exception("Falha ao Gerar Relatorio, Nenhum Registro Vinculado!");
        }
        $transporte = $this->registro->pegaTransporte();
        if (!$transporte) {
            throw new Exception("Falha ao Gerar Relatorio, Nenhum Transporte Vinculado!");
        }
        $email_sttrans = "";
        $this->setFilename($this->getFilename() . "_" . $this->registro->ano_referencia . "_" . Escola_Util::zero($this->registro->codigo, 4));
        if ($this->registro->veiculo()) {
            $tv = $this->registro->pegaReferencia();
            if ($tv) {
                $veiculo = $tv->findParentRow("TbVeiculo");
            }
        } else {
            $veiculo = $transporte->pegaVeiculo();
        }
        $proprietario = $transporte->pegaProprietario();
        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        $pj = $pessoa = $arquivo = false;
        if ($sistema) {
            $pj = $sistema->findParentRow("TbPessoaJuridica");
            $pessoa = $pj->pega_pessoa();
            if ($pessoa) {
                $email_sttrans = $pessoa->email;
            }
            $arquivo = $pessoa->getFoto();
        }
        $this->AddPage();
        ob_start();
        $this->css();
        ?>
        <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td align="center" width="100px" rowspan="4"><img src="<?php echo $arquivo->pegaNomeCompleto(); ?>" alt="" /></td>
                            <td align="center" class="titulo_servico titulo_servico_mini" width="500px">ESTADO DO AMAPÁ</td>
                        </tr>
                        <tr><td align="center" class="titulo_servico titulo_servico_mini">PREFEITURA MUNICIPAL DE SANTANA</td></tr>
                        <tr><td align="center" class="titulo_servico titulo_servico_mini"><?php echo $pj->razao_social; ?></td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <br />
        <?php
        $dia = date("d");
        $desc_mes = Escola_Util::pegaMes(date("n"));
        $ano = date("Y");
        $municipio = "Santana";
        $uf = "AP";
        if ($pessoa) {
            $endereco = $pessoa->getEndereco();
            $bairro = $endereco->findParentRow("TbBairro");
            if ($bairro) {
                $mun = $bairro->findParentRow("TbMunicipio");
                if ($mun) {
                    $municipio = $mun->descricao;
                    $obj_uf = $mun->findParentRow("TbUf");
                    if ($obj_uf) {
                        $uf = $obj_uf->sigla;
                    }
                }
            }
        }
        if ($proprietario) {
            $pessoa = $proprietario->findParentRow("TbPessoa");
            if ($pessoa) {
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
        <div class="direita"><?php echo $municipio; ?>-<?php echo $uf; ?>, <?php echo $dia; ?> de <?php echo $desc_mes; ?> de <?php echo $ano; ?>.</div>
        <div></div>
        <div class="centro fonte_20pt">DIRETORIA DE TRANSPORTES</div>
        <br />
        <div class="centro fonte_16pt">AUTORIZAÇÃO No.: <?php echo $this->registro->mostrar_numero(); ?></div>
        <div></div>
        <div class="normal">Permissionário/Concessionário: <?php echo $transporte->mostrar_codigo(); ?><br />
            Nome: <?php echo $proprietario_pf->mostrar_nome(); ?><br />
            C.P.F.: <?php echo $proprietario_pf->mostrar_documento(); ?><br />
            Endereço: <?php echo $endereco->toString(); ?></div>
        <div></div>
        <div class="paragrafo">A <?php echo $pj->sigla; ?>-<?php echo $pj->mostrar_nome(); ?>, através desta autoriza o detentor da Permissão de Serviço de Transporte de Carga a executar junto ao DETRAN-AP, o serviço de:<br />
            <span class="negrito font_14pt">* BAIXA PARA CATEGORIA PARTICULAR</span> do veículo abaixo:</div>
<?php 
$txt_proprietario_veiculo = "";
$pt_pessoa = $pessoa;
$pv_pessoa = $veiculo->getProprietario();
if ($pv_pessoa && ($pv_pessoa->getId() != $pt_pessoa->getId())) {
    $txt_proprietario_veiculo = $pv_pessoa->toString();
}
if ($txt_proprietario_veiculo) { ?>
        <div class="normal">Proprietário (Veículo): <?php echo $txt_proprietario_veiculo; ?></div>
<?php } ?>
        <div class="normal">PLACA: <?php echo $veiculo->mostrar_placa(); ?><br />
            Marca: <?php echo $veiculo->findParentRow("TbFabricante")->toString(); ?><br />
            Modelo: <?php echo $veiculo->modelo; ?><br />
            Ano de Fabricação: <?php echo $veiculo->ano_fabricacao; ?><br />
            Cor: <?php echo $veiculo->findParentRow("TbCor"); ?><br />
            Chassi: <?php echo $veiculo->chassi; ?></div>
        <div></div><div></div>
        <div class="centro"><?php echo $pj->sigla; ?></div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');
        $this->lastPage();
        $this->download();
    }

    public function css() {
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