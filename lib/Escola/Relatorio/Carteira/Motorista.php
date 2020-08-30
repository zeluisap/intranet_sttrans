<?php

class Escola_Relatorio_Carteira_Motorista extends Escola_Relatorio
{

    public function __construct()
    {
        parent::__construct("relatorio_carteira_motorista_" . date("Ymd_His"));
        $this->SetTopMargin(5);
        $this->SetAutoPageBreak(5);
    }

    public function header()
    { }

    public function Footer()
    { }

    public function toPDF($motorista, $ss = false)
    {
        if (!$motorista) {
            return false;
        }

        if (!$ss) {
            $ss = $motorista->pegaSolicitacaoAtiva();
        }

        if (!$ss) {
            throw new Exception("Falha ao Gerar Carteira de Motorista, Nenhuma Licença Ativa!");
        }

        $tg = $motorista->getTransporteGrupo();
        if (!$tg) {
            throw new Exception("Falha ao Gerar Carteira de Motorista, Grupo de Transporte não vinculado!");
        }

        $pm = $motorista->getPessoaMotorista();
        if (!$pm) {
            throw new Exception("Falha ao Gerar Carteira de Motorista, Motorista não vinculado!");
        }

        $pf = $pm->getPessoaFisica();
        if (!$pf) {
            throw new Exception("Falha ao Gerar Carteira de Motorista, Nenhuma Pessoa Vinculada!");
        }

        $txt_transporte = $tg->toString();
        $txt_tipo = "AUXILIAR";
        $txt_matricula = $motorista->matricula;
        $txt_nome = $pf->toString();

        $txt_cnh = $pm->cnh_numero;
        $txt_registro = $pm->cnh_registro;

        $txt_rg = $pf->mostrar_identidade();
        $txt_cpf = Escola_Util::formatCPF($pf->cpf);

        $font_name = "Helvetica";

        $this->AddPage();

        $this->setFont($font_name, "B", 8);
        $this->Image(ROOT_DIR . "/application/file/imagem_carteira_desvinculado.png", 14, 14, 102, 130, 'PNG');

        //foto da pessoa
        $pf_foto = $pf->getFoto();
        if ($pf_foto) {
            $wi = $pf_foto->getWideImage();
            $txt_image = $wi->asString('png');
            $this->Image('@' . $txt_image, 19, 31, 27, 34, 'PNG');
        }

        $this->setXY(49, 33);
        $this->MultiCell(60, 10, $txt_transporte, 0, 'C', 0, 0, '', '', true, 0, false, true, 10, 'M');

        $this->setFont($font_name, "B", 7);
        $this->setXY(49, 45);
        $this->MultiCell(24, 20, $txt_tipo, 0, 'C');

        $this->setXY(89, 45);
        $this->MultiCell(24, 20, $txt_matricula, 0, 'C');

        $this->setXY(49, 53);
        $this->MultiCell(30, 20, $txt_rg, 0, 'C');

        $this->setXY(82, 53);
        $this->MultiCell(30, 20, $txt_cpf, 0, 'C');

        $this->setXY(49, 60);
        $this->MultiCell(30, 20, $txt_cnh, 0, 'C');

        $this->setXY(82, 60);
        $this->MultiCell(30, 20, $txt_registro, 0, 'C');

        $this->setXY(20, 68);
        $this->MultiCell(100, 20, $txt_nome, 0, 'C');

        $txt_servico_numero = $ss->codigo;
        $txt_servico_ano = $ss->ano_referencia;
        $txt_servico_data_inicio = Escola_Util::formatData($ss->data_inicio);
        $txt_servico_data_validade = Escola_Util::formatData($ss->data_validade);

        // segunda pagina
        $this->setFont($font_name, "B", 10);
        $this->setXY(84, 81.7);
        $this->MultiCell(10, 20, $txt_servico_numero, 0, 'R');

        $this->setFont($font_name, "B", 10);
        $this->setXY(95, 81.7);
        $this->MultiCell(10, 20, $txt_servico_ano, 0, 'L');

        $this->setXY(40, 104);
        $this->MultiCell(20, 20, $txt_servico_data_inicio, 0, 'C');

        $this->setXY(70, 104);
        $this->MultiCell(20, 20, $txt_servico_data_validade, 0, 'C');

        $this->lastPage();
        //$this->show();
        $this->download();
    }
}
