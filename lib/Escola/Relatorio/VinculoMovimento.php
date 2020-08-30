<?php

class Escola_Relatorio_VinculoMovimento extends Escola_Relatorio
{

    private $vinculo = false;
    private $info_bancaria = false;

    public function __construct($vinculo)
    {
        $this->set_vinculo($vinculo);
        $filename = "relatorio_movimento";
        if ($this->vinculo) {
            $filename = $filename . "_" . $this->vinculo->sigla;
        }
        parent::__construct($filename);
        $this->SetTopMargin(65);
    }

    public function set_vinculo($vinculo)
    {
        if ($vinculo) {
            $this->vinculo = $vinculo;
        }
    }

    public function get_vinculo()
    {
        return $this->vinculo;
    }

    public function get_info_bancaria()
    {
        return $this->info_bancaria;
    }

    public function set_info_bancaria($info_bancaria)
    {
        $this->info_bancaria = $info_bancaria;
    }

    public function validarEmitir()
    {
        $errors = array();
        if ($this->vinculo) {
            //$ib = $this->vinculo->pega_info_bancaria();
            $ib = $this->get_info_bancaria();
            if (!$ib) {
                $errors[] = "Nenhum Conta Vinculada ao Projeto!";
            }
        } else {
            $errors[] = "Nenhum Projeto Vinculado!";
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }

    public function header()
    {
        $tb = new TbMoeda();
        $moeda = $tb->pega_padrao();

        $ib = $this->get_info_bancaria();
        //$ib = $this->vinculo->pega_info_bancaria();
        parent::header();
        ob_start();
        $this->css();
        ?>
        <table>
            <tr>
                <td align="center">RELATÓRIO DE MOVIMENTAÇÃO BANCÁRIA</td>
            </tr>
        </table>
        <br />
        <br />
        <br />
        <table border="1" cellpadding="4">
            <tr>
                <td colspan="2" class="negrito" align="center">DADOS DO PROJETO</td>
            </tr>
            <tr>
                <td width="140px" align="right">Projeto:</td>
                <td width="533px"><?php echo $this->vinculo->toString(); ?></td>
            </tr>
            <?php if ($ib) { ?>
                <tr>
                    <td align="right">Conta Bancária: </td>
                    <td><?php echo $ib->toString(); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td align="right">Saldo Atual: </td>
                <td class="negrito"><?php echo $moeda->simbolo . " " . Escola_Util::number_format($ib->pegaSaldo()); ?></td>
            </tr>
        </table>
        <?php
                $html = ob_get_contents();
                ob_end_clean();
                $this->writeHTML($html, true, false, true, false, '');
            }

            public function imprimir()
            {
                $tb = new TbMoeda();
                $moeda = $tb->pega_padrao();

                $this->addPage();
                if ($this->vinculo) {
                    //$ib = $this->vinculo->pega_info_bancaria();
                    $ib = $this->get_info_bancaria();
                    if ($ib) {
                        $tb = new TbVinculoMovimento();
                        $vms = $tb->listar(array("filtro_id_info_bancaria" => $ib->getId()));
                        if ($vms) {
                            ob_start();
                            $this->css();
                            $txt_saldo_anterior = 0;
                            $primeiro = $vms->current();
                            $valor_anterior = $primeiro->pega_valor_anterior();
                            if ($valor_anterior) {
                                $txt_saldo_anterior = Escola_Util::number_format($valor_anterior->valor);
                            } else {
                                $txt_saldo_anterior = Escola_Util::number_format(0);
                            }
                            $txt_saldo_anterior = $moeda->simbolo . " " . $txt_saldo_anterior;
                            ?>
                    <table cellpadding="5" class="tabela_movimento">
                        <tr>
                            <th class="negrito" width="80px" align="center">Data</th>
                            <th class="negrito" width="160px" align="center">Tipo</th>
                            <th class="negrito" width="110px" align="center">Valor</th>
                            <th class="negrito" width="210px" align="center">Observação</th>
                            <th class="negrito" width="110px" align="center">Saldo</th>
                        </tr>
                        <tr>
                            <td colspan="4">SALDO ANTERIOR</td>
                            <td align="right"><?php echo $txt_saldo_anterior; ?></td>
                        </tr>
                        <?php
                                            foreach ($vms as $vm) {
                                                $vm = $tb->getPorId($vm->getId());
                                                $txt_data_movimento = $txt_movimento_tipo = $txt_valor = $txt_descricao = $txt_saldo = "--";
                                                $txt_data_movimento = Escola_Util::formatData($vm->data_movimento);
                                                $mt = $vm->findParentRow("TbVinculoMovimentoTipo");
                                                if ($mt) {
                                                    $txt_movimento_tipo = $mt->toString();
                                                    if ($mt->despesa()) {
                                                        $dt = $vm->findParentRow("TbDespesaTipo");
                                                        if ($dt && $dt->getId() && $dt->despesa_bancaria()) {
                                                            $txt_movimento_tipo = $dt->toString();
                                                        }
                                                    }
                                                }
                                                $valor = $vm->pega_valor();
                                                if ($valor) {
                                                    $txt_valor = $valor->toString();
                                                }
                                                $txt_descricao = $vm->descricao;
                                                $valor_posterior = $vm->pega_valor_posterior();
                                                $txt_saldo = $moeda->simbolo . " " . Escola_Util::number_format($valor_posterior);
                                                ?>
                            <tr>
                                <td align="center"><?php echo $txt_data_movimento; ?></td>
                                <td align="left"><?php echo $txt_movimento_tipo; ?></td>
                                <td align="right"><?php echo $txt_valor; ?></td>
                                <td align="left"><?php echo $txt_descricao; ?></td>
                                <td align="right"><?php echo $txt_saldo; ?></td>
                            </tr>
                        <?php
                                            }
                                            ?>
                    </table>
        <?php
                            $html = ob_get_contents();
                            ob_end_clean();
                            $this->writeHTML($html, true, false, true, false, '');
                        }
                    }
                }
                $this->lastPage();
                $this->download();
            }

            public function css()
            {
                parent::css();
                ?>
        <style type="text/css">
            .tabela_movimento th {
                border-bottom: 1px #000 double;
            }

            .tabela_movimento td {
                border-bottom: 1px #000 dotted;
            }
        </style>
<?php
    }
}
