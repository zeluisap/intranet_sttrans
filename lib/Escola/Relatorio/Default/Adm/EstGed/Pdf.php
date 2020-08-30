<?php

class Escola_Relatorio_Default_Adm_EstGed_Pdf extends Escola_Relatorio
{

    public function __construct()
    {
        parent::__construct("relatorio");
        $this->SetTopMargin(30);
    }

    public function set_dados($dados)
    {
        parent::set_dados($dados);
        if (isset($dados["filename"])) {
            $this->setFilename($dados["filename"]);
        }
    }

    public function header()
    {
        parent::header();
        ob_start();
        $this->css();
        ?>
        <table>
            <tr>
                <td align="center" class="titulo-secundario"><?php echo $this->relatorio->descricao; ?></td>
            </tr>
        </table>
        <br />
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
        }

        public function imprimir()
        {
            $dados = TbDocumento::pegaEstatistica();
            $this->AddPage();
            ob_start();
            $this->css();
            ?>
        <table class="lista" border="1" cellpadding="3">
            <thead>
                <tr>
                    <th align="center"><strong>Tipo de Documentos</strong></th>
                    <th align="center"><strong>Quantidade de Docs Importados</strong></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$dados) { ?>
                    <tr>
                        <td colspan="2">NENHUM REGISTRO LOCALIZADO!</td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($dados as $obj) { ?>
                        <tr>
                            <td><?php echo $obj->descricao; ?></td>
                            <td align="center"><?php echo Escola_Util::number_format($obj->total); ?></td>
                        </tr>
                <?php }
                        } ?>
            </tbody>
        </table>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');

        $this->lastPage();
        $this->download();
    }
}
