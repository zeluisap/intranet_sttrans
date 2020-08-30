<?php

class Escola_Relatorio_Carteira extends Escola_Relatorio
{

    public function __construct()
    {
        parent::__construct("relatorio_carteira");
        $this->SetTopMargin(5);
        $this->SetAutoPageBreak(5);
    }

    public function header()
    { }

    public function Footer()
    { }

    public function toPDF($registros)
    {
        if ($registros) {
            ob_start();
            $this->AddPage();
            $this->css();
            foreach ($registros as $id_transporte_pessoa) {
                $tp = TbTransportePessoa::pegaPorId($id_transporte_pessoa);
                $pdf = $tp->toPDF();
                if ($pdf) {
                    echo $pdf;
                }
            }
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
            $this->lastPage();
            $this->download();
        }
        return false;
    }

    public function css()
    {
        ?>
        <style type="text/css">
            body,
            td {
                font-size: 8pt;
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

            .font_10 {
                font-size: 10pt;
            }

            .td_foto {
                border: 1px solid #000;
                height: 80px;
            }
        </style>
<?php

    }
}
