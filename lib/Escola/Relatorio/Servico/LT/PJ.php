<?php
class Escola_Relatorio_Servico_LT_PJ extends Escola_Relatorio_Servico_LT
{
    public function getFilhos()
    {
        return [];
    }

    public function header()
    {
    }

    public function footer()
    {
    }

    public function enabled()
    {
        if (!parent::enabled()) {
            return false;
        }

        return true;

        // return (isset($this->tp_pessoa) && $this->tp_pessoa->pj());
    }

    // public function getFilename()
    // {
    //     return "licenca_pj";
    // }

    public function getLicenca()
    {
        return $this->registro;
    }

    public function getNomeTransporte()
    {
        $txt = [
            $this->transporte_grupo->descricao
        ];

        $t = $this->getTransporte();
        if (!$t) {
            $txt[] = $t->codigo;
            return implode("\n", $txt);
        }

        $tp = $t->pegaProprietario();
        if (!$tp) {
            $txt[] = $t->codigo;
            return implode("\n", $txt);
        }

        $pessoa = $tp->getPessoa();
        if (!$pessoa) {
            $txt[] = $t->codigo;
            return implode("\n", $txt);
        }

        $pessoa_veiculo = $this->getProprietarioVeiculo();
        if (!$pessoa_veiculo) {
            $txt[] = $t->codigo;
            return implode("\n", $txt);
        }

        $p_id = $pessoa->getId();
        $v_id = $pessoa_veiculo->getId();

        if ($p_id == $v_id) {
            $txt[] = $t->codigo;
            return implode("\n", $txt);
        }

        $txt[] = $t->codigo . " - " . $pessoa->toString();

        return implode("\n", $txt);
    }

    public function getProprietarioVeiculo()
    {
        if (!$this->veiculo) {
            return null;
        }

        $prop_veiculo = $this->veiculo->getProprietario();
        if (!$prop_veiculo) {
            return null;
        }

        return $prop_veiculo;
    }

    public function toPDF()
    {

        if (!(isset($this->transporte_grupo) && $this->transporte_grupo)) {
            throw new Escola_Exception("Falha! Grupo de transporte não localizado!");
        }

        $txt_transporte = $this->getNomeTransporte();

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

        $nome_numero_ano = $this->getTituloLicenca();

        $font_name = "Helvetica";

        $this->AddPage();

        $this->setFont($font_name, "B", 14);
        $this->Image(ROOT_DIR . "/application/file/imagem_licenca.png", 54, 14, 96, 140, 'PNG');

        $this->setCellHeightRatio(0.8);

        // $this->setXY(115, 39);
        // $this->MultiCell(20, 20, $txt_licenca_numero, 0, 'R');

        // $this->setXY(134.5, 39);
        // $this->MultiCell(20, 20, $txt_licenca_ano, 0, 'L');

        $this->setFont($font_name, "B", 10);
        $this->setXY(57, 34);
        $this->MultiCell(90, 10, $nome_numero_ano, 0, 'C', 0, 0, '', '', true, 0, false, true, 10, 'M');

        $this->setFont($font_name, "B", 8.5);
        $this->setXY(57, 46);
        $this->MultiCell(90, 13, $txt_transporte, 0, 'C', 0, 0, '', '', true, 0, false, true, 13, 'M');

        $this->setXY(59, 63);
        $this->MultiCell(25, 20, $txt_placa, 0, 'C');

        $this->setXY(82, 59.5);
        $this->MultiCell(40, 5, strtoupper($txt_marca_modelo), 0, 'C', 0, 0, '', '', true, 0, false, true, 10, 'M');

        $this->setXY(117, 63);
        $this->MultiCell(30, 20, $txt_cor, 0, 'C');

        $this->setXY(55, 76);
        $this->MultiCell(30, 20, $txt_ano_fab, 0, 'C');

        $this->setXY(84, 76);
        $this->MultiCell(35, 20, $txt_chassi, 0, 'C');

        $this->setXY(118, 73);
        $this->MultiCell(30, 6, $txt_tipo_especie, 0, 'C', 0, 0, '', '', true, 0, false, true, 8, 'M');

        $this->setCellHeightRatio(1.2);
        $this->setXY(62, 83.5);
        $this->MultiCell(80, 13, $txt_nome_proprietario, 1, 'C', false, 1, '', '', true, 0, false, true, 13, 'M');

        $this->setCellHeightRatio(0.8);
        $this->setXY(77, 106);
        $this->MultiCell(20, 20, $txt_emissao, 0, 'C');

        $this->setXY(105, 106);
        $this->MultiCell(20, 20, $txt_validade, 0, 'C');

        $this->lastPage();
        // $this->show();
        $this->download();
        die();
    }

    private function getNomeProprietario()
    {
        $txt = $this->tp_pessoa->toString();

        $prop_veiculo = $this->getProprietarioVeiculo();
        if (!$prop_veiculo) {
            return $txt;
        }

        return $prop_veiculo->mostrar_documento() . " - " . $prop_veiculo->mostrar_nome();
    }
}
