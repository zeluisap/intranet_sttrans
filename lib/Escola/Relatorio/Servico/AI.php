<?php

class Escola_Relatorio_Servico_AI extends Escola_Relatorio_Servico {

    public function __construct() {
        parent::__construct();
        $this->setFilename("relatorio_autorizacao_interdicao");
    }

    public function validarEmitir() {
        $p_errors = parent::validarEmitir();
        $errors = array();
        $ref = $this->registro->pegaReferencia();
        if (!$ref) {
            $errors[] = "NENHUMA AUTORIZAÇÃO DE INTERDIÇÃO VINCULADA!";
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

        $interdicao = $this->registro->pegaReferencia();
        if (!$interdicao) {
            throw new Exception("Falha ao Gerar Relatorio, Nenhuma Interdição Vinculado!");
        }
        
        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        if (!$sistema) {
            throw new Exception("Falha ao Gerar Relatorio, Confirações do Sistema Inválidas!");
        }

        $pj = $sistema->findParentRow("TbPessoaJuridica");
        if (!$pj) {
            throw new Exception("Falha ao Gerar Relatorio, Confirações do Sistema Inválidas!!");
        }
        $pessoa = $pj->pega_pessoa();

        $this->setFilename($this->getFilename() . "_" . $this->registro->ano_referencia . "_" . Escola_Util::zero($this->registro->codigo, 4));
        $stg = $this->registro->findParentRow("TbServicoTransporteGrupo");
        $servico = $stg->findParentRow("TbServico");
        $this->AddPage();
        ob_start();
        $this->css();
?>        
        <table width="100%">
            <tr>
                <td align="center" class="titulo_servico">AUTORIZAÇÃO DE INTERDIÇÃO</td>
            </tr>
            <tr><td></td></tr>
            <tr>
                <td align="center" class="titulo_servico">No.: <?php echo $this->registro->mostrar_numero(); ?> - <?php echo $pj->sigla; ?></td>
            </tr>
        </table>
        <p class="paragrafo">A <?php echo $pj->razao_social; ?> - <?php echo $pj->sigla; ?>, através da lei No.: 9.503, de 23 de setembro de 1997 do código de trânsito brasileiro em seu artigo 24, <strong>compete aos órgãos e entidades executivos de trânsito dos municípios, no âmbito de sua circunscrição. Inciso I - cumprir e fazer cumprir a legislação e as normas de trânsito, no âmbito de suas atribuições</strong>, concede ao <?php echo $pessoa->mostrar_nome(); ?> - <strong>AUTORIZAÇÃO DE INTERDIÇÃO</strong>, da <strong><?php echo $interdicao->informacoes; ?>.</strong></p>
        <p class="paragrafo"><strong>OBS:</strong> Informamos que o requerente fica responsável pela sinalização do local de acordo com o que preceitua o Art. 95 do Código de Trânsito Brasileiro. Informamos ainda que a inobservância do disposto neste artigo será punida com multa entre cinquenta e trezentas UFIR como no disposto do parágrafo 3o do referido artigo, independentemente das cominações cíveis e penais cabíveis.</p>
        <?php
        $dia = date("d");
        $desc_mes = Escola_Util::pegaMes(date("n"));
        $ano = date("Y");
        $municipio = "Santana";
        $uf = "AP";
        ?>
        <p></p>
        <p></p>
        <p></p>
        <div class="direita"><?php echo $municipio; ?>-<?php echo $uf; ?>, <?php echo $dia; ?> de <?php echo $desc_mes; ?> de <?php echo $ano; ?>.</div>
        <p></p>
        <p></p>
        <p></p>
        <p></p>
        <div class="centro"><?php echo $pj->sigla; ?></div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');
        $this->lastPage();
        $this->download();
    }

    public function css() {
        ?>
        <style type="text/css">
            .titulo_servico {
                font-size: 15pt;
                font-weight: bold;
            }
            .negrito {
                font-weight: bold;
            }
            .paragrafo {
                font-size: 12pt;
                text-align: justify;
                text-indent: 50px;
                line-height: 6px;
            }
            .direita {
                text-align: right;
                font-size: 12pt;
            }
            .centro {
                text-align: center;
                font-size: 15pt;
            }
        </style>
        <?php
    }

}