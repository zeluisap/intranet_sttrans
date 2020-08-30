<?php

class Escola_Relatorio_Contracheque extends Escola_Relatorio
{

    private $folha;

    public function __construct($folha)
    {
        $this->set_folha($folha);
        $folha = $this->pega_folha($folha);
        $data = new Zend_Date($folha->DATA);
        $mes = Escola_Util::pegaMes((int) $data->get("MM"));
        parent::__construct("contracheque_{$folha->FUNCIONARIOS}_{$folha->ANO}_{$mes}");
        $this->SetMargins(10, 10);
    }

    public function set_folha($folha)
    {
        $this->folha = $folha;
    }

    public function header()
    { }

    public function footer()
    { }

    public function pega_folha($folha)
    {
        $sql = "select *
				from folha
				where (folha = {$folha}) ";
        $folhas = Escola_Util::consulta_ibase($sql);
        if ($folhas) {
            return $folhas[0];
        }
        return false;
    }

    public function imprimir()
    {
        if ($this->folha) {
            $folha = $this->pega_folha($this->folha);
            $sql = "select a.setores, c.nome setor_nome, b.nome nome_funcao, a.funcionarios, a.nome, a.cpf, a.dataadmissao, a.cbo, a.numerorg
							from funcionarios a, funcao b, setores c
							where (a.funcao = b.funcao)
							and (a.setores = c.setores)
							and (funcionarios = {$folha->FUNCIONARIOS})";
            $funcionario = false;
            $funcionarios = Escola_Util::consulta_ibase($sql);
            if ($funcionarios) {
                $funcionario = $funcionarios[0];
                $sql = "select a.numero conta_numero, a.digito conta_digito, b.bancos, b.numero agencia_numero, b.DIGITO agencia_digito 
						from CONTASFUNCIONARIOS a, bancosagencias b
						where a.BANCOSAGENCIAS = b.BANCOSAGENCIAS
						and a.FUNCIONARIOS = {$funcionario->FUNCIONARIOS}";
                $conta = false;
                $contas = Escola_Util::consulta_ibase($sql);
                if ($contas) {
                    $conta = $contas[0];
                }
                $sql = "select b.TIPOEVENTOS, b.nome evento_nome, a.*
						from ITEMFOLHA a, eventos b
						where (a.eventos = b.eventos)
						and (a.ano = {$folha->ANO})
						and (a.folha = {$folha->FOLHA})
						order by b.TIPOEVENTOS";
                $items = Escola_Util::consulta_ibase($sql);
                ob_start();
                $this->AddPage();
                $this->css();
                ?>
                <table border="1" cellpadding="3">
                    <tr>
                        <td rowspan="2" colspan="2" width="100px" align="center">
                            <img src="<?php echo Escola_Util::getBaseUrl(); ?>/img/logo_santana.jpg" alt="" height="70px" />
                        </td>
                        <td colspan="6" width="573px">
                            <table width="100%">
                                <tr>
                                    <td align="center" class="negrito">COMPROVANTE DE RENDIMENTOS</td>
                                </tr>
                                <tr>
                                    <td align="center">PREFEITURA MUNICIPAL DE SANTANA</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>CNPJ</td>
                                </tr>
                                <tr>
                                    <td class="negrito">23.066.640/0001-08</td>
                                </tr>
                            </table>
                        </td>
                        <td>REG. JURÍDICO</td>
                        <td colspan="2">
                            <table width="100%">
                                <tr>
                                    <td>SITUAÇÃO DO SERVIDOR</td>
                                </tr>
                                <tr>
                                    <td class="negrito">Ativo</td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>UF</td>
                                </tr>
                                <tr>
                                    <td class="negrito">AP</td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>EXERCÍCIO / LOCAL</td>
                                </tr>
                                <tr>
                                    <td class="negrito"><?php echo $folha->ANO; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7">
                            <table width="100%">
                                <tr>
                                    <td>NOME DO SERVIDOR</td>
                                </tr>
                                <tr>
                                    <td class="negrito"><?php echo $funcionario->NOME; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>MATRÍCULA</td>
                                </tr>
                                <tr>
                                    <td class="negrito" align="center"><?php echo $funcionario->FUNCIONARIOS; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <table width="100%">
                                <tr>
                                    <td>CATEGORIA CARREIRA</td>
                                </tr>
                                <tr>
                                    <td class="negrito"><?php echo $funcionario->NOME_FUNCAO; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>CLASSE</td>
                                </tr>
                                <tr>
                                    <td class="negrito"></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>DATA ADMISSÃO</td>
                                </tr>
                                <tr>
                                    <td class="negrito"><?php echo Escola_Util::formatData($funcionario->DATAADMISSAO); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>CBO</td>
                                </tr>
                                <tr>
                                    <td class="negrito"><?php echo $funcionario->CBO; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>DEP S.F</td>
                                </tr>
                                <tr>
                                    <td class="negrito"></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>DEP I.R</td>
                                </tr>
                                <tr>
                                    <td class="negrito"></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>T.S (%)</td>
                                </tr>
                                <tr>
                                    <td class="negrito"></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>CPF</td>
                                </tr>
                                <tr>
                                    <td class="negrito"><?php echo Escola_Util::formatCpf($funcionario->CPF); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>BANCO</td>
                                </tr>
                                <tr>
                                    <td class="negrito"><?php echo ($conta) ? $conta->BANCOS : ""; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>AGÊNCIA</td>
                                </tr>
                                <tr>
                                    <td class="negrito">
                                        <?php
                                                        $agencia = "";
                                                        if ($conta) {
                                                            $agencia = $conta->AGENCIA_NUMERO;
                                                            if (trim($conta->AGENCIA_DIGITO)) {
                                                                $agencia .= "-" . $conta->AGENCIA_DIGITO;
                                                            }
                                                        }
                                                        echo $agencia;
                                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>CONTA CORRENTE</td>
                                </tr>
                                <tr>
                                    <td class="negrito">
                                        <?php
                                                        $cc = "";
                                                        if ($conta) {
                                                            $cc = $conta->CONTA_NUMERO;
                                                            if (trim($conta->CONTA_DIGITO)) {
                                                                $cc .= "-" . $conta->CONTA_DIGITO;
                                                            }
                                                        }
                                                        echo $cc;
                                                        ?> </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>MÊS PAGAMENTO</td>
                                </tr>
                                <tr>
                                    <td class="negrito">
                                        <?php
                                                        $data = new Zend_Date($folha->DATA);
                                                        echo Escola_Util::pegaMes((int) $data->get("MM"));
                                                        ?> / <?php echo $folha->ANO; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">
                            <table width="100%">
                                <tr>
                                    <td>SETOR</td>
                                </tr>
                                <tr>
                                    <td class="negrito"><?php echo $funcionario->SETORES; ?>-<?php echo $funcionario->SETOR_NOME; ?></td>
                                </tr>
                            </table>
                        </td>
                        <td colspan="2">
                            <table width="100%">
                                <tr>
                                    <td>IDENTIFICAÇÃO ÚNICA</td>
                                </tr>
                                <tr>
                                    <td class="negrito" align="center"><?php echo $funcionario->NUMERORG; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br />
                <table border="1" cellpadding="3">
                    <tr>
                        <th width="60px" class="negrito" align="center">Tipo</th>
                        <th width="373px" class="negrito" align="center">Discriminação</th>
                        <th width="100px" class="negrito" align="center">Qtd / Fator</th>
                        <th width="140px" class="negrito" align="center">Valor</th>
                    </tr>
                    <?php
                                    $dados = array(
                                        "fgts" => 0,
                                        "bruto" => 0,
                                        "desconto" => 0,
                                        "irpf" => 0
                                    );
                                    $vencimento = 0;
                                    if ($items) {
                                        foreach ($items as $item) {
                                            if ($item->EVENTOS == 1) {
                                                $vencimento = $item->VALOR;
                                            }
                                            if ($item->TIPOEVENTOS == 1) {
                                                $dados["bruto"] += $item->VALOR;
                                            } else {
                                                $dados["desconto"] += $item->VALOR;
                                            }
                                            ?>
                            <tr>
                                <td align="center"><?php echo $item->TIPOEVENTOS; ?></td>
                                <td align="left"><?php echo $item->EVENTO_NOME; ?></td>
                                <td align="center"><?php echo $item->QTD; ?>/<?php echo $item->FATOR; ?></td>
                                <td align="right" class="negrito"><?php echo Escola_Util::number_format($item->VALOR); ?></td>
                            </tr>
                    <?php
                                        }
                                    }
                                    $liquido = $dados["bruto"] - $dados["desconto"];
                                    ?>
                </table>
                <table border="1" cellpadding="3">
                    <tr>
                        <td width="25%">
                            <table width="100%">
                                <tr>
                                    <td>BASE DE CÁLCULO DO TETO</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior">xxxxxxxxxxxxxxx</td>
                                </tr>
                            </table>
                        </td>
                        <td width="25%">
                            <table width="100%">
                                <tr>
                                    <td>DEPÓSITO FGTS</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior"><?php echo Escola_Util::number_format($dados["fgts"]); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td class="borda" width="25%">
                            <table width="100%">
                                <tr>
                                    <td>BRUTO</td>
                                </tr>
                                <tr>
                                    <td class="direita maior"><?php echo Escola_Util::number_format($dados["bruto"]); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td class="borda" width="25%">
                            <table width="100%">
                                <tr>
                                    <td>DESCONTO</td>
                                </tr>
                                <tr>
                                    <td class="direita maior"><?php echo Escola_Util::number_format($dados["desconto"]); ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>BASE DE CÁLCULO DO IRPF</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior"><?php echo Escola_Util::number_format($dados["bruto"]); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>MARGEM CONSIGNÁVEL 30%</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior">
                                        <?php
                                                        $margem_30 = (($liquido * 30) / 100);
                                                        $margem_70 = (($liquido * 70) / 100);
                                                        echo Escola_Util::number_format($margem_30);
                                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>MARGEM CONSIGNÁVEL 70%</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior"><?php echo Escola_Util::number_format($margem_70); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td class="borda cor">
                            <table width="100%">
                                <tr>
                                    <td>LÍQUIDO</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior"><?php echo Escola_Util::number_format($liquido); ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br />
                <table border="1" cellpadding="3">
                    <tr>
                        <th colspan="3" class="negrito" align="center">DEMONSTRATIVO DE PAGAMENTO DE SALÁRIO</th>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>VENCIMENTO BÁSICO</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior"><?php echo Escola_Util::number_format($vencimento); ?></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>VANT. CALCULADAS S/VENC. BÁSICO</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior">xxxxxxxxxxxxxxx</td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>FUNÇÃO (ATIVOS)</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior">xxxxxxxxxxxxxxx</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>VANTAGEM PESSOAL (DÉCIMO)</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior">xxxxxxxxxxxxxxx</td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>VANTAGEM DECISÕES JUDICIAIS</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior">xxxxxxxxxxxxxxx</td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td>OUTRAS VANTAGENS</td>
                                </tr>
                                <tr>
                                    <td class="negrito direita maior">xxxxxxxxxxxxxxx</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="center" class="negrito">DPRH - RECURSOS HUMANOS</td>
                    </tr>
                    <tr>
                        <td colspan="3" align="center" height="70px"></td>
                    </tr>
                </table>
        <?php
                    }
                    $html = ob_get_contents();
                    ob_end_clean();
                    $this->writeHTML($html, true, false, true, false, '');
                    $this->lastPage();
                    $this->download();
                    //$this->show();
                }
            }

            public function css()
            {
                ?>
        <style type="text/css">
            body,
            td,
            th {
                font-size: 7pt;
            }

            table tr {
                line-height: 7px;
            }

            .negrito {
                font-weight: bold;
            }

            .direita {
                text-align: right;
            }

            .borda {
                border: 2px solid #000;
            }

            .maior {
                font-size: 9pt;
            }

            .cor {
                background-color: #F5ECEC;
            }
        </style>
<?php
    }
}
