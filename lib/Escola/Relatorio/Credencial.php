<?php

class Escola_Relatorio_Credencial extends Escola_Relatorio {

    protected $credencial;

    public function __construct($filename) {
        parent::__construct($filename);
        $this->SetTopMargin(5);
        $this->SetAutoPageBreak(40);
    }

    public function setCredencial($credencial) {
        $isNull = ($this->credencial == null);
        $this->credencial = $credencial;
        if ($isNull && $credencial->numero) {
            $filename = $this->getFilename();
            $filename .= "_" . $credencial->numero . "_" . $credencial->ano . "_" . date("Ymd_His");
            $this->setFilename($filename);
        }
    }

    public function getCredencial() {
        return $this->getCredencial();
    }

    public function header() {
        
    }

    public function Footer() {
        
    }

    public function toPDF() {
        /* implementar nas classes filhas */
    }

    public function css() {
        ?>
        <style type="text/css">
            body, td, th {
                font-family: Times New Roman;
                font-size: 15pt;
            }
            .font_10 {
                font-size: 10pt;
            }
            .indent_1 {
                text-indent: 30px;
            }
        </style>
        <?php

    }

}