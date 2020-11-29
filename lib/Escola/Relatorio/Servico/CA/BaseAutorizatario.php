<?php

/**
 * carteira base de autorizatário
 * o autorizatário está disponível para todos os serviços, menos moto-taxi
 * também não está disponível para auxiliares e motorista avulso (que terão suas próprias implementações)
 */
class Escola_Relatorio_Servico_CA_BaseAutorizatario extends Escola_Relatorio_Servico_CA
{
    public function getFilhos()
    {
        return [];
    }

    public function enabled()
    {
        if (!parent::enabled()) {
            return false;
        }

        if ($this->registro->motorista()) {
            return false;
        }

        if ($this->transporte_grupo && $this->transporte_grupo->moto_taxi()) {
            return false;
        }

        if (!($this->tp && $this->tp->proprietario())) {
            return false;
        }

        return true;
    }

    public function set_registro($registro)
    {
        parent::set_registro($registro);

        if (!$this->enabled()) {
            return;
        }

        $tp = $this->registro->pegaReferencia();
        $this->setPessoa($tp, "tp");
    }

    public function getCarteiraCodigo()
    {
        return $this->registro->codigo;
    }

    public function getCarteiraAno()
    {
        return $this->registro->ano_referencia;
    }

    public function getDataValidade()
    {
        return $this->registro->data_validade;
    }

    public function getFilename()
    {
        return "carteira_autorizatario";
    }

    public function getNomenclaturaLicenca()
    {
        return "";
    }

    public function getTipo()
    {
        return "AUTORIZATÁRIO";
    }

    public function getMatricula()
    {

        if (!$this->transporte) {
            return "--";
        }

        $codigo = $this->transporte->codigo;
        if (!$codigo) {
            return "--";
        }

        return $codigo;
    }

    public function getPessoaFisica()
    {
        return $this->tp_pessoa_pf;
    }

    public function getPessoaMotorista()
    {
        $pf =  $this->getPessoaFisica();
        if (!$pf) {
            throw new Escola_Exception("Falha ao emitir documento, pessoa física não localizada.");
        }

        return $pf->pegaPessoaMotorista();
    }

    public function toPDF()
    {

        $txt_imagem = $txt_transporte = $txt_tipo = "";
        $txt_matricula = $txt_rg = $txt_cpf = $txt_cnh = $txt_registro = $txt_nome = $txt_tipo_pessoa = "--";
        $txt_servico_codigo = $txt_servico_ano = "";
        $txt_servico_data_inicio = $txt_servico_data_validade = "";

        $pf = $this->getPessoaFisica();
        if (!$pf) {
            throw new Escola_Exception("Falha ao localizar pessoa!");
        }

        $pf_foto = $pf->getFoto();
        if ($pf_foto) {
            $wi = $pf_foto->getWideImage();
            $txt_imagem = $wi->asString('png');
        }

        if (!isset($this->transporte_grupo) && $this->transporte_grupo) {
            throw new Escola_Exception("Não é possível identificar o Grupo de Transporte!");
        }
        $txt_transporte = $this->transporte_grupo->descricao;

        $txt_tipo = $this->getTipo();
        if (!$txt_tipo) {
            throw new Escola_Exception("Falha! Tipo de pessoa não disponível");
        }

        $txt_matricula = $this->getMatricula();

        $txt = $pf->mostrar_identidade();
        if ($txt) {
            $txt_rg = $txt;
        }

        $txt = $pf->mostrar_documento();
        if ($txt) {
            $txt_cpf = $txt;
        }

        $pm = $this->getPessoaMotorista();
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

        $txt_nome = $pf->mostrar_nome();

        $txt_servico_codigo = $this->getCarteiraCodigo();
        $txt_servico_ano = $this->getCarteiraAno();

        $txt_servico_data_inicio = date("d/m/Y");
        $txt_servico_data_validade = Escola_Util::formatData($this->getDataValidade());

        $font_name = "Helvetica";

        $this->AddPage();

        $this->setFont($font_name, "B", 8);
        $this->Image(ROOT_DIR . "/application/file/imagem_carteira_desvinculado.png", 50, 14, 102, 130, 'PNG');

        if ($txt_imagem) {
            $this->Image('@' . $txt_imagem, 55.5, 31, 26.5, 33.5, 'PNG');
        }

        $this->setXY(85, 32.5);
        $this->MultiCell(60, 10, $txt_transporte, 0, 'C', 0, 0, '', '', true, 0, false, true, 10, 'M');

        $this->setFont($font_name, "B", 7);
        $this->setXY(85, 45);
        $this->MultiCell(24, 20, $txt_tipo, 0, 'C');

        $this->setXY(125, 45);
        $this->MultiCell(24, 20, $txt_matricula, 0, 'C');

        $this->setXY(85, 52.5);
        $this->MultiCell(30, 20, $txt_rg, 0, 'C');

        $this->setXY(118, 52.5);
        $this->MultiCell(30, 20, $txt_cpf, 0, 'C');

        $this->setXY(85, 60);
        $this->MultiCell(30, 20, $txt_cnh, 0, 'C');

        $this->setXY(118, 60);
        $this->MultiCell(30, 20, $txt_registro, 0, 'C');

        $this->setFont($font_name, "B", 8);
        $this->setXY(56, 67);
        $this->MultiCell(100, 20, $txt_nome, 0, 'C');

        // segunda pagina                
        $this->setFont($font_name, "B", 10);
        $this->setXY(76, 82);
        $this->MultiCell(100, 20, $txt_servico_codigo, 0, 'C');

        $this->setXY(85, 82);
        $this->MultiCell(100, 20, $txt_servico_ano, 0, 'C');

        $this->setXY(75, 104.5);
        $this->MultiCell(20, 20, Escola_Util::formatData($txt_servico_data_inicio), 0, 'C');

        $this->setXY(106, 104.5);
        $this->MultiCell(20, 20, Escola_Util::formatData($txt_servico_data_validade), 0, 'C');

        $this->setFont($font_name, "B", 5.5);
        $this->setXY(54, 122);

        $this->lastPage();
        // $this->show();
        $this->download();
        die();
    }
}
