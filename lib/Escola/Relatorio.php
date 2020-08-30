<?php

class Escola_Relatorio extends TCPDF
{

    protected $dados = array();
    private $filename = "relatorio";
    protected $relatorio = false;

    public function __construct($filename)
    {
        $this->setFilename($filename);

        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Intranet');
        $this->SetTitle($this->getFilename());
        $this->SetSubject($this->getFilename());
        $this->SetKeywords('TCPDF, PDF');

        // set default header data
        //$this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);
        // set header and footer fonts
        $this->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        //$this->setLanguageArray($l);
        // set font
        $this->SetFont('helvetica', '', 8);

        $this->SetTopMargin(40);
        $this->SetAutoPageBreak(true, 10);

        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        if (!$sistema) {
            return;
        }
        $this->pj = $sistema->findParentRow("TbPessoaJuridica");
    }

    public function set_transporte($transporte)
    {
        $this->setTransporte($transporte);
    }

    public function setTransporte($transporte)
    {

        $this->concessao = null;

        if (!$transporte) {
            $this->transporte = null;
            $this->transporte_veiculo = null;
            $this->transporte_grupo = null;
            $this->setProprietario(null);
            return;
        }

        $this->transporte = $transporte;

        $this->transporte_grupo = $transporte->getTransporteGrupo();
        $proprietario = $transporte->pegaProprietario();
        $this->setProprietario($proprietario);

        $tv = $transporte->pegaTransporteVeiculoAtivo();

        $this->setTransporteVeiculo($tv);

        $concessao = $this->transporte->get_concessao();
        if ($concessao) {
            $this->concessao = $concessao;
        }
    }

    public function setProprietario($obj)
    {
        $this->setPessoa($obj);
    }

    public function setTransporteVeiculo($tv)
    {
        if (!$tv) {
            $this->transporte_veiculo = null;
            $this->licenca_ativa = null;
            $this->veiculo = null;
            return;
        }

        $veiculo = $tv->findParentRow("TbVeiculo");
        $this->veiculo = $veiculo;
        $this->transporte_veiculo = $tv;
    }

    public function setPessoa($objeto, $prefixo = "proprietario")
    {

        $obj = $objeto;

        if (!$obj) {
            $this->$prefixo = null;
            return;
        }

        $this->$prefixo = $obj;

        $pessoa = $obj->findParentRow("TbPessoa");

        $nome_pessoa = $prefixo . "_pessoa";
        $nome_pessoa_pf = $prefixo . "_pessoa_pf";
        $nome_pessoa_pj = $prefixo . "_pessoa_pj";

        if (!$pessoa) {
            $this->$nome_pessoa = null;
            $this->$nome_pessoa_pf = null;
            $this->$nome_pessoa_pj = null;
            return;
        }

        $ref = $pessoa->pegaPessoaFilho();
        if (!$ref) {
            $this->$nome_pessoa = null;
            $this->$nome_pessoa_pf = null;
            $this->$nome_pessoa_pj = null;
            return;
        }

        if ($pessoa->pf()) {
            $this->$nome_pessoa_pf = $ref;
            $this->$nome_pessoa_pj = null;
        } elseif ($pessoa->pj()) {
            $this->$nome_pessoa_pf = null;
            $this->$nome_pessoa_pj = $ref;
        }

        $this->$nome_pessoa = $pessoa;
    }

    public function header()
    {
        $this->ln(8);
        $this->cabecalho();
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function show()
    {
        $this->Output($this->filename  . "_" . date("YmdHis") . ".pdf", 'I');
        die();
    }

    public function download()
    {
        $this->Output($this->filename . "_" . date("YmdHis") . ".pdf", 'D');
        die();
    }

    public function salvar()
    {
        $this->Output($this->filename . "_" . date("YmdHis") . ".pdf", 'F');
        die();
    }

    public function set_dados($dados)
    {
        $this->dados = $dados;
    }

    public function get_dados()
    {
        return $this->dados;
    }

    public function get_relatorio()
    {
        return $this->relatorio;
    }

    public function set_relatorio($relatorio)
    {
        $this->relatorio = $relatorio;
    }

    public function mostrar_data_completa($pessoa)
    {
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
        return "{$municipio}-{$uf}, {$dia} de {$desc_mes} de {$ano}.";
    }

    public function css()
    {
        ?>
        <style type="text/css">
            body,
            td,
            div {
                font-size: 10pt;
            }

            .tabela {
                border: 2px solid #000;
            }

            .titulo_servico {
                font-size: 15pt;
                font-weight: bold;
            }

            .titulo_servico_mini {
                font-size: 13pt;
            }

            .negrito {
                font-weight: bold;
            }

            .rr {
                background-color: #ccc;
            }

            .italico {
                font-style: italic;
            }

            .esquerda {
                text-align: left;
            }

            .direita {
                text-align: right;
            }

            .centro {
                text-align: center;
            }

            .justificado {
                text-align: justify;
            }

            .paragrafo {
                text-indent: 50px;
                text-align: justify;
                line-height: 6px;
            }

            div.normal {
                text-align: justify;
                line-height: 5px;
            }

            .fonte_12pt {
                font-size: 12pt;
            }

            .fonte_14pt {
                font-size: 14pt;
            }

            .fonte_16pt {
                font-size: 16pt;
            }

            .fonte_20pt {
                font-size: 20pt;
            }

            .linha_130 {
                line-height: 130%;
            }

            .linha_150 {
                line-height: 150%;
            }

            .linha_160 {
                line-height: 160%;
            }
        </style>
    <?php
        }

        public function html($func)
        {
            ob_start();
            $func();
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
        }

        public function cabecalho()
        {
            if (!$this->pj) {
                return;
            }
            $pessoa = $this->pj->pega_pessoa();
            $arquivo = $pessoa->getFoto();

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

            ob_start();
            $this->css();
            ?>
        <style>
            div {
                font-size: 11pt;
            }

            .endereco_sttrans {
                font-size: 8pt;
            }

            .titulo_servico {
                font-family: "Times New Roman";
                font-size: 15pt;
                font-style: italic;
            }

            .titulo_servico_mini {
                font-family: "Arial";
                font-style: normal;
                font-weight: bold;
                font-size: 11pt;
            }

            .negrito {
                font-weight: bold;
            }
        </style>
        <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td align="center" width="100px" rowspan="4"><img src="<?php echo $arquivo->pegaNomeCompleto(); ?>" alt="" /></td>
                            <td align="center" class="titulo_servico titulo_servico_mini" width="400px">ESTADO DO AMAPÁ</td>
                            <td align="center" width="100px" rowspan="4"></td>
                        </tr>
                        <tr>
                            <td align="center" class="titulo_servico titulo_servico_mini">PREFEITURA MUNICIPAL DE SANTANA</td>
                        </tr>
                        <tr>
                            <td align="center" class="titulo_servico titulo_servico_mini"><?php echo $this->pj->razao_social; ?></td>
                        </tr>
                        <tr>
                            <td align="center" class="titulo_servico titulo_servico_mini">- <?= $this->pj->sigla ?> -</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <hr>
        <br>
        <div></div>
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
        }

        public function footer()
        {
            $pessoa = $this->pj->pega_pessoa();
            if (empty($this->pagegroups)) {
                $pagenumtxt = $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
            } else {
                $pagenumtxt = $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias();
            }

            $txt_usuario = "";
            $usuario = Escola_Acl::getUsuarioLogado();
            if ($usuario) {
                $txt_usuario = $usuario->toString();
            }

            $txt_data = date("d/m/Y H:i:s");
            ob_start();
            ?>
        <hr>
        <table>
            <tr>
                <td style="text-align: left;" width="100px"><?= $txt_data ?> </td>
                <td style="text-align: center;" width="435px"><?php echo $pessoa->mostrar_endereco(); ?></td>
                <td style="text-align: right;" width="100px"><?= trim($pagenumtxt) ?></td>
            </tr>
            <tr>
                <td style="text-align: right;" width="600px">Emitido Por: <?= $txt_usuario ?>.</td>
            </tr>
        </table>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');
    }
}
