<?php
class Escola_Relatorio_Servico_CA extends Escola_Relatorio_Servico
{

    public function getFilhos()
    {
        return [
            "MOT", "CP", "MO", "AUT"
        ];
    }

    public function header()
    { }

    // public function footer()
    // { }

    public function enabled()
    {
        return true;
    }

    public function getFilename()
    {
        return "carteira_de_autorizatario";
    }

    public function getLicencaCodigo()
    {

        if ($this->transporte->taxi()) {
            return "ct";
        }

        return parent::getLicencaCodigo();
    }

    public function getNomenclaturaLicenca()
    {
        if (!$this->transporte) {
            return "AUTORIZAÇÃO DE TRÁFEGO";
        }

        if ($this->transporte->taxi()) {
            return "CERTIFICADO PARA TRAFEGAR";
        }

        return "AUTORIZAÇÃO DE TRÁFEGO";
    }

    public function getTipo()
    {
        return "AUTORIZATÁRIO";
        // if (isset($this->transporte) && $this->transporte->taxi()) {
        //     return "AUTORIZATÁRIO";
        // }

        // if (!$this->tp) {
        //     return "";
        // }

        // $tpt = $this->tp->getTipo();
        // if (!$tpt) {
        //     return "";
        // }
        // return $tpt->toString();
    }

    public function getMatricula()
    {
        if (!$this->transporte) {
            return "";
        }

        if (!(isset($this->transporte->codigo) && $this->transporte->codigo)) {
            return "";
        }

        return $this->transporte->codigo;
    }

    public function setTransporte($transporte)
    {
        parent::setTransporte($transporte);

        if (!$transporte) {
            $this->setTransporteVeiculo(null);
            return;
        }

        if (!$this->transporte_veiculo) {
            $tv = $transporte->pegaTransporteVeiculoAtivo();
            $this->setTransporteVeiculo($tv);
        }
    }

    public function validarEmitir()
    {
        $erros = parent::validarEmitir();

        if (!(isset($this->registro) && $this->registro)) {
            return ["Solicitação de Serviço não Definida!"];
        }

        if (!(isset($this->transporte) && $this->transporte)) {
            return ["Transporte não Definido!"];
        }

        if (!(isset($this->proprietario_pessoa) && $this->proprietario_pessoa)) {
            return ["Proprietário não Definido!"];
        }

        if (!(isset($this->tp) && $this->tp)) {
            // return ["Pessoa Vinculada não Definida!"];
        }

        if (!(isset($this->transporte_veiculo) && $this->transporte_veiculo)) {
            return ["Nenhum Veículo Detectado!"];
        }

        if (!count($erros)) {
            return null;
        }

        return $erros;
    }

    public function getMarcaModelo()
    {
        $arr = [];
        $marca = $this->veiculo->getFabricante();
        if ($marca) {
            $txt = $marca->descricao;
            if ($txt) {
                $arr[] = $txt;
            }
        }

        $txt = $this->veiculo->modelo;
        if ($txt) {
            $arr[] = $txt;
        }

        if (!count($arr)) {
            return "";
        }

        return implode(" / ", $arr);
    }

    public function getCor()
    {
        if (!$this->veiculo) {
            return "";
        }

        $cor = $this->veiculo->getCor();
        if (!$cor) {
            return "";
        }

        $txt = $cor->descricao;
        if (!$txt) {
            return "";
        }

        return $txt;
    }

    public function getAnoFabricacaoModelo()
    {
        if (!$this->veiculo) {
            return "";
        }

        $arr = [];

        $txt = $this->veiculo->ano_fabricacao;
        if ($txt) {
            $arr[] = $txt;
        }

        $txt = $this->veiculo->ano_modelo;
        if ($txt) {
            $arr[] = $txt;
        }

        if (!count($arr)) {
            return "";
        }

        return implode(" / ", $arr);
    }

    public function getVeiculoTipoEspecie()
    {
        if (!$this->veiculo) {
            return "";
        }

        $arr = [];

        $tipo = $this->veiculo->getVeiculoTipo();
        if ($tipo) {
            $txt = $tipo->descricao;
            if ($txt) {
                $arr[] = $txt;
            }
        }

        $especie = $this->veiculo->getVeiculoEspecie();
        if ($especie) {
            $txt = $especie->descricao;
            if ($txt) {
                $arr[] = $txt;
            }
        }

        if (!count($arr)) {
            return "";
        }

        return implode(" / ", $arr);
    }

    public function getVeiculoProprietarioNome()
    {
        if (!$this->veiculo) {
            return "";
        }

        $prop = $this->veiculo->getProprietario();
        if (!$prop) {
            return "";
        }

        $txt = $prop->mostrar_nome();
        if (!$txt) {
            return "";
        }

        return $txt;
    }

    public function getLicenca()
    {
        return $this->licenca_ativa;
    }

    public function getCarteiraCodigo()
    {
        $licenca = $this->getLicenca();

        if (!$licenca) {
            return $this->registro->codigo;
        }
        return $licenca->codigo;
    }

    public function getCarteiraAno()
    {
        $licenca = $this->getLicenca();
        if (!$licenca) {
            return $this->registro->ano_referencia;
        }
        return $licenca->ano_referencia;
    }

    public function getDataValidade()
    {
        $data_validade = $this->registro->data_validade;
        $licenca = $this->getLicenca();
        if ($licenca) {
            $data_validade = $licenca->data_validade;
        }
        return $data_validade;
    }

    public function getConcessaoCodigo()
    {
        return $this->getMatricula();
    }

    public function toPDF()
    {

        if (!(isset($this->tp_pessoa_pf) && $this->tp_pessoa_pf)) {
            throw new Escola_Exception("Não é possível identificar a pessoa referente a Carteira!");
        }

        $txt_imagem = $txt_transporte = $txt_tipo = "";
        $txt_matricula = $txt_rg = $txt_cpf = $txt_cnh = $txt_registro = $txt_nome = $txt_tipo_pessoa = "";
        $txt_servico_codigo = $txt_servico_ano = "";
        $txt_placa = $txt_marca_modelo = $txt_ano_fabricacao_modelo = $txt_cor = $txt_chassi = $txt_veiculo_especie = $txt_veiculo_proprietario = "";
        $txt_servico_data_inicio = $txt_servico_data_validade = "";

        $pf_foto = $this->tp_pessoa_pf->getFoto();
        if ($pf_foto) {
            $wi = $pf_foto->getWideImage();
            $txt_imagem = $wi->asString('png');
        }

        if (!isset($this->transporte_grupo) && $this->transporte_grupo) {
            throw new Escola_Exception("Não é possível identificar o Grupo de Transporte!");
        }
        $txt_transporte = $this->transporte_grupo->descricao;

        $tp = $this->servico;

        $txt_tipo = $this->getTipo();
        if (!$txt_tipo) {
            throw new Escola_Exception("Falha! Tipo de pessoa não disponível");
        }

        $txt_matricula = $this->getMatricula();

        $txt = $this->tp_pessoa_pf->mostrar_identidade();
        if ($txt) {
            $txt_rg = $txt;
        }

        $txt = $this->tp_pessoa_pf->mostrar_documento();
        if ($txt) {
            $txt_cpf = $txt;
        }

        $pm = $this->tp_pessoa_pf->pegaPessoaMotorista();
        if ($pm) {
            $txt = $pm->cnh_numero;
            if ($txt) {
                $txt_cnh = $txt;
            }
            $txt = $pm->cnh_registro;
            if ($txt) {
                $txt_registro = $txt;
            }
        }

        $txt_nome = $this->tp_pessoa->mostrar_nome();

        $txt_servico_codigo = $this->getCarteiraCodigo();
        $txt_servico_ano = $this->getCarteiraAno();

        $txt_placa = $this->veiculo->placa;

        $txt_marca_modelo = $this->getMarcaModelo();
        $txt_cor = $this->getCor();
        $txt_ano_fabricacao_modelo = $this->getAnoFabricacaoModelo();

        $txt = $this->veiculo->chassi;
        if ($txt) {
            $txt_chassi = $txt;
        }

        $txt_veiculo_especie = $this->getVeiculoTipoEspecie();
        $txt_veiculo_proprietario = $this->getVeiculoProprietarioNome();

        $txt_servico_data_inicio = date("d/m/Y");
        $txt_servico_data_validade = Escola_Util::formatData($this->getDataValidade());

        $font_name = "Helvetica";

        $this->AddPage();

        $this->setFont($font_name, "B", 8);
        $this->Image(ROOT_DIR . "/application/file/imagem_carteira_padrao.png", 50, 14, 102, 130, 'PNG');

        if ($txt_imagem) {
            $this->Image('@' . $txt_imagem, 55.5, 31, 26.5, 33.5, 'PNG');
        }

        $this->setXY(85, 33);
        $this->MultiCell(60, 10, $txt_transporte, 0, 'C', 0, 0, '', '', true, 0, false, true, 10, 'M');

        $this->setFont($font_name, "B", 7);
        $this->setXY(85, 45);
        $this->MultiCell(24, 20, $txt_tipo, 0, 'C');

        $this->setXY(125, 45);
        $this->MultiCell(24, 20, $txt_matricula, 0, 'C');

        $this->setXY(85, 53);
        $this->MultiCell(30, 20, $txt_rg, 0, 'C');

        $this->setXY(118, 53);
        $this->MultiCell(30, 20, $txt_cpf, 0, 'C');

        $this->setXY(85, 60);
        $this->MultiCell(30, 20, $txt_cnh, 0, 'C');

        $this->setXY(118, 60);
        $this->MultiCell(30, 20, $txt_registro, 0, 'C');

        $this->setXY(56, 68);
        $this->MultiCell(100, 20, $txt_nome, 0, 'C');

        // segunda pagina                
        $this->setFont($font_name, "B", 10);
        $this->setXY(51, 81.5);
        $this->MultiCell(100, 20, $this->getNomenclaturaLicenca() . " N°.: {$txt_servico_codigo} / {$txt_servico_ano}", 0, 'C');

        $this->setFont($font_name, "B", 10);
        $this->setXY(51, 85);
        $this->MultiCell(100, 20, "PERMISSÃO " . $this->getConcessaoCodigo(), 0, 'C');

        $this->setFont($font_name, "B", 8);
        $this->setXY(56, 93);
        $this->MultiCell(25, 20, $txt_placa, 0, 'C');

        $this->setFont($font_name, "B", 7);
        $this->setXY(83, 90);
        $this->MultiCell(38, 8, $txt_marca_modelo, 0, 'C', 0, 0, '', '', true, 0, false, true, 8, 'M');

        $this->setFont($font_name, "B", 8);
        $this->setXY(120, 93);
        $this->MultiCell(30, 10, $txt_cor, 0, 'C');

        $this->setXY(56, 101.5);
        $this->MultiCell(24, 20, $txt_ano_fabricacao_modelo, 0, 'C');

        $this->setXY(84, 101.5);
        $this->MultiCell(35, 20, $txt_chassi, 0, 'C');

        $this->setFont($font_name, "B", 7);
        $this->setXY(121, 100);
        $this->html(function () use ($txt_veiculo_especie) {
            ?>
            <table style="width: 100px;text-align:center;" border="1">
                <tr>
                    <td><?= $txt_veiculo_especie ?></td>
                </tr>
            </table>
        <?php
                });

                // $this->MultiCell(30, 7, $txt_veiculo_especie, 0, 'C', 0, 0, '', '', true, 0, false, true, 7, 'M');

                $this->setFont($font_name, "B", 9);
                $this->setXY(56, 110);
                $this->MultiCell(90, 20, $txt_veiculo_proprietario, 0, 'C');

                $this->setXY(74, 117.5);
                $this->MultiCell(20, 20, Escola_Util::formatData($txt_servico_data_inicio), 0, 'C');

                $this->setXY(106, 117.5);
                $this->MultiCell(20, 20, Escola_Util::formatData($txt_servico_data_validade), 0, 'C');

                $this->setFont($font_name, "B", 5.5);
                $this->setXY(54, 122);

                $this->html(function () use ($txt_transporte) {
                    ?>
            <table style="width: 340px; text-align:justify;">
                <tr>
                    <td>O portador encontra-se cadastrado na STTRANS, para o serviço de <?= $txt_transporte ?>.</td>
                </tr>
                <tr>
                    <td>Em caso de irregularidade deve ser comunicado a STTRANS.</td>
                </tr>
                <tr>
                    <td>Só é válida mediante a apresentação da Carteira Nacional de Habilitação.</td>
                </tr>
                <tr>
                    <td>É obrigatório a apresentação desta, quando solicitado pelos Agentes da Autoridade de Trânsito.</td>
                </tr>
            </table>
<?php
        });

        // $this->setXY(17, 122);
        // $this->MultiCell(100, 20, "O portador encontra-se cadastrado na STTRANS, para o serviço de {$txt_transporte}.", 0, 'L');

        // $this->setFont($font_name, "B", 6);
        // $this->setXY(17, 124.5);
        // $this->MultiCell(100, 20, "Em caso de irregularidade deve ser comunicado a STTRANS.", 0, 'L');

        // $this->setFont($font_name, "B", 6);
        // $this->setXY(17, 127);
        // $this->MultiCell(100, 20, "Só é válida mediante a apresentação da Carteira Nacional de Habilitação.", 0, 'L');

        // $this->setFont($font_name, "B", 6);
        // $this->setXY(17, 129.5);
        // $this->MultiCell(100, 20, "É obrigatório a apresentação desta, quando solicitado pelos Agentes da Autoridade de Trânsito.", 0, 'L');

        $this->lastPage();
        // $this->show();
        $this->download();
        die();
    }
}
