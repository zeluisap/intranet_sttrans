<?php

class Escola_Relatorio_Ponto extends Escola_Relatorio
{

    private $ano_mes;
    private $ids = false;
    private $funcionarios = false;

    public function __construct($ano_mes)
    {
        $this->set_ano_mes($ano_mes);
        parent::__construct("relatorio_ponto_{$this->ano_mes}");
        $this->SetTopMargin(5);
    }

    public function set_ids($ids)
    {
        if ($ids && is_array($ids) && count($ids)) {
            $tb = new TbFuncionario();
            foreach ($ids as $id) {
                $funcionario = $tb->pegaPorId($id);
                if ($funcionario && $funcionario->ativo()) {
                    $this->funcionarios[] = $funcionario;
                }
            }
        }
    }

    public function set_funcionarios($funcionarios)
    {
        $this->funcionarios = $funcionarios;
    }

    public function set_ano_mes($ano_mes)
    {
        $this->ano_mes = $ano_mes;
    }

    public function header()
    { }

    public function footer()
    { }

    public function imprimir()
    {
        if ($this->funcionarios && is_array($this->funcionarios) && count($this->funcionarios)) {
            $am = explode("_", $this->ano_mes);
            if (is_array($am) && count($am)) {
                $ano = $am[0];
                $mes = $am[1];
            }
            $tb = new TbSistema();
            $sistema = $tb->pegaSistema();
            $pj = $sistema->findParentRow("TbPessoaJuridica");
            $pessoa = $pj->findParentRow("TbPessoa");
            $arquivo = $pessoa->getFoto();
            foreach ($this->funcionarios as $funcionario) {
                $pf = $funcionario->findParentRow("TbPessoaFisica");
                $cargo = "";
                $rg_cargo = $funcionario->findParentRow("TbCargo");
                if ($rg_cargo) {
                    $cargo = $rg_cargo->toString();
                }
                $str_lotacao = $str_funcao = "";
                $lotacao = $funcionario->pegaLotacaoAtual();
                if ($lotacao) {
                    $funcao = $lotacao->findParentRow("TbFuncionarioFuncao");
                    if ($funcao) {
                        $str_funcao = $funcao->toString();
                    }
                    $setor = $lotacao->findParentRow("TbSetor");
                    if ($setor) {
                        $str_lotacao = $setor->toString();
                    }
                }
                $this->AddPage();
                $this->Image($arquivo->pegaNomeCompleto(), 5, 5, 40, 20, "png");
                ob_start();
                $this->css();
                ?>
                <table>
                    <tr>
                        <td width="100px"></td>
                        <td align="center" class="titulo" width="470px"><?php echo $pj->razao_social; ?></td>
                        <td width="100px"></td>
                    </tr>
                </table>
                <br />
                <table>
                    <tr>
                        <td align="center" class="titulo">FOLHA DE PONTO</td>
                    </tr>
                </table>
                <br />
                <table width="100%" border="0">
                    <tr>
                        <td class="destaque" width="90px">NOME: </td>
                        <td class="destaque" width="353px"><?php echo $pf->nome; ?></td>
                        <td class="destaque" width="110px">MATRÍCULA:</td>
                        <td class="destaque" width="120px"><?php echo $funcionario->matricula; ?></td>
                    </tr>
                    <tr>
                        <td class="destaque">CARGO: </td>
                        <td class="destaque" colspan="3"><?php echo $cargo; ?></td>
                    </tr>
                    <tr>
                        <td class="destaque">LOTAÇÃO: </td>
                        <td class="destaque" colspan="3"><?php echo $str_lotacao; ?></td>
                    </tr>
                    <tr>
                        <td class="destaque">FUNÇÃO: </td>
                        <td class="destaque"><?php echo $str_funcao; ?></td>
                        <td class="destaque">MÊS:</td>
                        <td class="destaque"><?php echo Escola_Util::pegaMes($mes); ?> / <?php echo $ano; ?></td>
                    </tr>
                </table>
                <br />
                <table border="1" class="grade">
                    <tr class="cabecalho">
                        <td width="40px">Dia</td>
                        <td width="70px">Entrada</td>
                        <td width="120px">Rubrica</td>
                        <td width="70px">Saída</td>
                        <td width="120px">Rubrica</td>
                        <td width="150px">Ocorrências</td>
                        <td width="110px">Rubrica Chefia</td>
                    </tr>
                    <?php
                                    $qtd_dias = date("t", strtotime($ano . "-" . $mes . "-01"));
                                    for ($i = 1; $i <= $qtd_dias; $i++) {
                                        $dt = new Zend_Date($i . "/" . $mes . "/" . $ano);
                                        $dia_semana = $dt->get(Zend_Date::WEEKDAY_DIGIT);
                                        $dia = "";
                                        $class = "";
                                        $ocorrencia = $funcionario->pegaOcorrenciaPorData($dt);
                                        if ($ocorrencia) {
                                            $class = ' class="cabecalho" ';
                                            $dia = $ocorrencia->findParentRow("TbFuncionarioOcorrenciaTipo")->toString();
                                        } elseif ($dia_semana == 0 || $dia_semana == 6) {
                                            $class = ' class="cabecalho" ';
                                            $dia = Escola_Util::pegaDiaSemana($dt);
                                        }
                                        ?>
                        <tr <?php echo $class; ?>>
                            <td><?php echo $i; ?></td>
                            <?php if ($dia) { ?>
                                <td colspan="2"><?php echo $dia; ?></td>
                                <td colspan="2"><?php echo $dia; ?></td>
                            <?php } else { ?>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php } ?>
                </table>
                <table>
                    <tr>
                        <td>Emissão: <?php echo date("d/m/Y"); ?></td>
                    </tr>
                </table>
                <br /><br />
                <table>
                    <tr>
                        <td align="center">____________________________________________</td>
                        <td align="center">____________________________________________</td>
                    </tr>
                    <tr>
                        <td align="center">Assinatura do Funcionário</td>
                        <td align="center">Assinatura do Chefe Imediato</td>
                    </tr>
                </table>
        <?php
                        $html = ob_get_contents();
                        ob_end_clean();
                        $this->writeHTML($html, true, false, true, false, '');
                    }
                    $this->lastPage();
                    $this->download();
                    // $this->show();
                }
            }

            public function css()
            {
                ?>
        <style type="text/css">
            td {
                font-family: Times;
            }

            .titulo {
                font-size: 16pt;
                font-weight: bold;
            }

            .destaque {
                font-size: 11pt;
                font-weight: bold;
            }

            .grade td {
                text-align: center;
                line-height: 6.5px;
            }

            .grade tr.cabecalho td {
                font-weight: bold;
                background-color: #ccc;
            }
        </style>
<?php
    }
}
