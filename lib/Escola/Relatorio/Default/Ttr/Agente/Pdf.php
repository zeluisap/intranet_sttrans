<?php

class Escola_Relatorio_Default_Ttr_Agente_Pdf extends Escola_Relatorio
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
            $stmt = $this->dados["statement"];
            $this->AddPage();
            ob_start();
            $this->css();
            ?>
        <style type="text/css">
            body,
            td,
            th {
                font-size: 8pt;
            }
        </style>
        <table border="1" cellpadding="4">
            <?php
                    if ($stmt && count($stmt)) {
                        ?>
                <tr>
                    <th align="center" width="60px" bgcolor="#ccc">Código</th>
                    <th width="60px" align="center" bgcolor="#ccc">Matrícula</th>
                    <th width="110px" align="center" bgcolor="#ccc">C.P.F.</th>
                    <th align="center" width="240px" bgcolor="#ccc">Nome</th>
                    <th align="center" width="200px" bgcolor="#ccc">Cargo</th>
                </tr>
                <?php
                            foreach ($stmt as $agente) {
                                $cpf = $matricula = $nome = $cargo = "";
                                $funcionario = $agente->findParentRow("TbFuncionario");
                                if ($funcionario) {
                                    $matricula = $funcionario->matricula;
                                    $pf = $funcionario->findParentRow("TbPessoaFisica");
                                    if ($pf) {
                                        $nome = $pf->nome;
                                        $cpf = Escola_Util::formatCpf($pf->cpf);
                                    }
                                    $cargo = $funcionario->findParentRow("TbCargo");
                                    if ($cargo) {
                                        $cargo = $cargo->toString();
                                    }
                                }
                                ?>
                    <tr>
                        <td align="center"><?php echo $agente->codigo; ?></td>
                        <td align="center"><?php echo $matricula; ?></td>
                        <td align="center"><?php echo $cpf; ?></td>
                        <td align="left"><?php echo $nome; ?></td>
                        <td align="left"><?php echo $cargo; ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td>
                        <div class="text-center">NENHUM REGISTRO LOCALIZADO!</div>
                    </td>
                </tr>
            <?php } ?>
        </table>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');

        $this->lastPage();
        $this->download();
    }
}
