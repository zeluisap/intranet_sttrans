<?php

class Escola_Relatorio_Default_Ttr_Taxassacado_Pdf extends Escola_Relatorio {

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
        $this->AddPage("P");
        ob_start();
        $this->css();
        $resumo = array();
        if ($stmt && $stmt->rowCount()) {
            $id_tg = 0;
            ?>
            <table border="1" cellpadding="5">
                <?php
                while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                    if ($id_tg != $obj->id_transporte_grupo) {
                        $id_tg = $obj->id_transporte_grupo;
                        if (!array_key_exists($id_tg, $resumo)) {
                            $item = new stdClass();
                            $item->id_transporte_grupo = $obj->id_transporte_grupo;
                            $item->transporte_grupo_descricao = $obj->transporte_grupo_descricao;
                            $item->valor = 0;
                            $resumo[$id_tg] = $item;
                        }
                        ?>
                        <tr class="itenTitulo">
                            <th colspan="3">Tipo de Transporte - <?php echo $obj->transporte_grupo_descricao; ?></th>
                        </tr>
                        <tr class="itenTitulo">
                            <th width="20%">Data</th>
                            <th width="60%">Nome</th>
                            <th width="20%">Valor</th>
                        </tr>
                    <?php
                    }
                    $pessoa = TbPessoa::pegaPorId($obj->id_pessoa);
                    $filho = $pessoa->pegaPessoaFilho();
                    $resumo[$obj->id_transporte_grupo]->valor = $resumo[$obj->id_transporte_grupo]->valor + $obj->valor_servico;
                    ?>
                    <tr class="grade">
                        <td align="center" width="20%"><div><?php echo Escola_Util::formatData($obj->data_pagamento); ?></div></td>
                        <td width="60%"><?php echo $filho->mostrar_nome(); ?></td>
                        <td align="center" width="20%"><div><?php echo Escola_Util::number_format($obj->valor_servico); ?></div></td>
                    </tr>
            <?php } ?>

            </table>
            <?php if (count($resumo)) { ?>
                <br />
                <table border="1" cellpadding="5">
                    <tr class="itenTitulo">
                        <th colspan="2">Resumo</th>
                    </tr>
                    <tr class="itenTitulo">
                        <th width="70%">Tipo de Veiculo</th>
                        <th width="30%">Total</th>
                    </tr>
                <?php foreach ($resumo as $item_resumo) { ?>
                        <tr class="grade">
                            <td width="70%"><?php echo $item_resumo->transporte_grupo_descricao; ?></td>
                            <td width="30%" align="center"><?php echo Escola_Util::number_format($item_resumo->valor); ?></td>
                        </tr>
                <?php } ?>
                </table>
                <?php
            }
        }

        $html = ob_get_contents();
        ob_end_clean();
        $this->writeHTML($html, true, false, true, false, '');

        $this->lastPage();
        $this->download();
    }

    public function css() {
        ?>
        <style type="text/css">
            table {
                margin: 5px;
            }
            td {
                font-family: Arial;
                font-size: 9pt;
            }
            .titulo {
                font-size: 10pt;
                font-weight: bold;
                text-align: center;
            }
            .grade th {
                line-height: 6.5px;
            }
            .itenTitulo th{
                font-size: 10pt;
                font-weight: bold;
                text-align: center;
                background-color: #F0F0F0;
                padding: 5px;
            }
        </style>
        <?php
    }

}