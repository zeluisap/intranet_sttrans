<?php

class Escola_Relatorio_Default_Ttr_Motorista_Pdf extends Escola_Relatorio {

    public function __construct() {
        parent::__construct("relatorio");
        $this->SetTopMargin(30);
    }

    public function set_dados($dados) {
        parent::set_dados($dados);
        if (isset($dados["filename"])) {
            $this->setFilename($dados["filename"]);
        }
    }

    public function header() {
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

    public function imprimir() {
        $stmt = $this->dados["statement"];
        $this->AddPage("L");
        ob_start();
        $this->css();
        ?>
        <table border="1" cellpadding="4">
            <?php
            if ($stmt && count($stmt)) {
                $id_transporte_grupo = 0;
                foreach ($stmt as $motorista) {
                    if ($id_transporte_grupo != $motorista->id_transporte_grupo) {
                        if ($id_transporte_grupo) {
                            ?>
                        </table>
                        <?php
                        $html = ob_get_contents();
                        ob_end_clean();
                        $this->writeHTML($html, true, false, true, false, '');
                        $this->AddPage("L");
                        ob_start();
                        $this->css();
                        ?>
                        <table border="1" cellpadding="4">    
                            <?php
                        }
                        $tg = $motorista->findParentRow("TbTransporteGrupo");
                        ?>
                        <tr>
                            <th colspan="10"><?php echo $tg->toString(); ?></th>
                        </tr>
                        <tr>
                            <th width="60px" align="center">Matrícula</th>
                            <th align="center" width="110px">C.P.F.</th>
                            <th width="291px" align="center">Nome</th>
                            <th width="100px" align="center">Data Cadastro</th>
                            <th align="center" width="100px">CNH Número</th>
                            <th align="center" width="100px">CNH Validade</th>
                            <th align="center" width="60px">CNH Cat</th>
                            <th align="center" width="80px">Carteira Número</th>
                            <th align="center" width="80px">Carteira Validade</th>
                        </tr>
                        <?php
                        $id_transporte_grupo = $motorista->id_transporte_grupo;
                    }

                    $cpf = $nome = $data_cadastro = $cnh_numero = $cnh_validade = $cnh_categoria = $carteira_numero = $carteira_validade = "";
                    $pm = $motorista->findParentRow("TbPessoaMotorista");
                    if ($pm) {
                        $cnh_numero = $pm->cnh_numero;
                        $cnh_validade = Escola_Util::formatData($pm->cnh_validade);
                        $cat = $pm->findParentRow("TbCnhCategoria");
                        if ($cat) {
                            $cnh_categoria = $cat->codigo;
                        }
                        $pf = $pm->findParentRow("TbPessoaFisica");
                        if ($pf) {
                            $cpf = Escola_Util::formatCpf($pf->cpf);
                            $nome = $pf->nome;
                        }
                    }
                    $data_cadastro = Escola_Util::formatData($motorista->data_cadastro);
                    $ss_carteira = $motorista->pegaCarteiraAtiva();
                    if ($ss_carteira) {
                        $carteira_numero = $ss_carteira->mostrar_numero();
                        $carteira_validade = Escola_Util::formatData($ss_carteira->data_validade);
                    }
                    ?>
                    <tr>
                        <td align="center"><?php echo $motorista->matricula; ?></td>
                        <td align="center"><?php echo $cpf; ?></td>
                        <td align="left"><?php echo $nome; ?></td>
                        <td align="center"><?php echo $data_cadastro; ?></td>
                        <td align="center"><?php echo $cnh_numero; ?></td>
                        <td align="center"><?php echo $cnh_validade; ?></td>
                        <td align="center"><?php echo $cnh_categoria; ?></td>
                        <td align="center"><?php echo $carteira_numero; ?></td>
                        <td align="center"><?php echo $carteira_validade; ?></td>
                    </tr>
            <?php } ?>
            <?php } else { ?>
                <tr>
                    <td><div class="text-center">NENHUM REGISTRO LOCALIZADO!</div></td>
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