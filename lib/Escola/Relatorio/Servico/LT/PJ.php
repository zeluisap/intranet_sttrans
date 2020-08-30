<?php
class Escola_Relatorio_Servico_LT_PJ extends Escola_Relatorio_Servico_LT
{
    public function getFilhos()
    {
        return [];
    }

    public function header()
    { }

    public function footer()
    { }

    public function enabled()
    {
        $enabled = parent::enabled();
        if (!$enabled) {
            return false;
        }

        return (isset($this->tp_pessoa) && $this->tp_pessoa->pj());
    }

    public function getFilename()
    {
        return "licenca_pj";
    }

    public function getLicenca()
    {
        return $this->registro;
    }

    public function toPDF()
    {

        $txt_licenca_numero = $this->getCarteiraCodigo();
        $txt_licenca_ano = $this->getCarteiraAno();

        if (!(isset($this->transporte_grupo) && $this->transporte_grupo)) {
            throw new Escola_Exception("Falha! Grupo de transporte não localizado!");
        }

        $txt_transporte = $this->transporte_grupo->descricao;

        if (!(isset($this->veiculo) && $this->veiculo)) {
            throw new Escola_Exception("Falha! Veículo não localizado!");
        }
        $veiculo = $this->veiculo;

        $txt_placa = $veiculo->placa;
        $txt_marca_modelo = $this->getMarcaModelo();
        $txt_cor = $this->getCor();
        $txt_ano_fab = $this->getAnoFabricacaoModelo();
        $txt_chassi = $veiculo->chassi;
        $txt_tipo_especie = $this->getVeiculoTipoEspecie();

        $txt_nome_proprietario = $this->getNomeProprietario();

        $txt_emissao = date('d/m/Y');
        $txt_validade = Escola_Util::formatData($this->getDataValidade());

        $font_name = "Helvetica";

        $this->AddPage();

        $this->setFont($font_name, "B", 14);
        $this->Image(ROOT_DIR . "/application/file/imagem_licenca.png", 54, 14, 96, 140, 'PNG');

        $this->setCellHeightRatio(0.8);

        $this->setXY(115, 39);
        $this->MultiCell(20, 20, $txt_licenca_numero, 0, 'R');

        $this->setXY(134.5, 39);
        $this->MultiCell(20, 20, $txt_licenca_ano, 0, 'L');

        $this->setFont($font_name, "B", 8.5);
        $this->setXY(72, 49);
        $this->MultiCell(60, 10, $txt_transporte, 0, 'C', 0, 0, '', '', true, 0, false, true, 10, 'M');

        $this->setXY(59, 65);
        $this->MultiCell(25, 20, $txt_placa, 0, 'C');

        $this->setXY(84.5, 61.5);
        $this->MultiCell(30, 5, strtoupper($txt_marca_modelo), 1, 'C', 0, 0, '', '', true, 0, false, true, 10, 'M');

        $this->setXY(117, 65);
        $this->MultiCell(30, 20, $txt_cor, 0, 'C');

        $this->setXY(55, 78);
        $this->MultiCell(30, 20, $txt_ano_fab, 0, 'C');

        $this->setXY(84, 78);
        $this->MultiCell(35, 20, $txt_chassi, 0, 'C');

        $this->setXY(118, 75);
        $this->MultiCell(30, 6, $txt_tipo_especie, 0, 'C', 0, 0, '', '', true, 0, false, true, 8, 'M');

        $this->setCellHeightRatio(1.2);
        $this->setXY(62, 90);
        $this->MultiCell(80, 20, $txt_nome_proprietario, 0, 'C');

        $this->setCellHeightRatio(0.8);
        $this->setXY(77, 104.5);
        $this->MultiCell(20, 20, $txt_emissao, 0, 'C');

        $this->setXY(105, 104.5);
        $this->MultiCell(20, 20, $txt_validade, 0, 'C');

        $this->lastPage();
        // $this->show();
        $this->download();
        die();
    }

    private function getNomeProprietario()
    {
        $txt = $this->tp_pessoa_pj->toString();

        if (!$this->veiculo) {
            return $txt;
        }

        $prop_veiculo = $this->veiculo->getProprietario();
        if (!$prop_veiculo) {
            return $txt;
        }

        return $prop_veiculo->mostrar_documento() . " - " . $prop_veiculo->mostrar_nome();
    }
}
