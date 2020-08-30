<?php

class Escola_Relatorio_Protocolo extends Escola_Relatorio
{

    protected $funcionario = false;
    protected $filtro = false;

    public function __construct()
    {
        parent::__construct("relatorio_protocolo");
        $this->SetTopMargin(30);
    }

    public function pega_funcionario()
    {
        return $this->funcionario;
    }

    public function set_funcionario($funcionario)
    {
        $this->funcionario = $funcionario;
    }

    public function pega_filtro()
    {
        return $this->filtro;
    }

    public function set_filtro($filtro)
    {
        $this->filtro = $filtro;
    }

    public function header()
    {
        parent::header();
        ob_start();
        $this->css();
        ?>
        <table>
            <tr>
                <td align="center" class="titulo">RELATÓRIO DE DOCUMENTO</td>
            </tr>
        </table>
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
        }

        public function imprimir()
        {
            $this->AddPage("L");
            ob_start();
            $this->css();
            $tb = new TbDocumento();
            $registros = $tb->listar($this->filtro);
            ?>
        <table>
            <?php
                    if ($this->funcionario) {
                        $lotacao = $this->funcionario->pegaLotacaoAtual();
                        ?>
                <tr>
                    <td>Nome: <?php echo $this->funcionario->toString(); ?></td>
                </tr>
                <?php
                            if ($lotacao) {
                                $setor = $lotacao->findParentRow("TbSetor");
                                ?>
                    <tr>
                        <td>Unidade: <?php echo $setor->toString(); ?></td>
                    </tr>
            <?php }
                    } ?>
            <?php
                    if ($this->dados) {
                        ?>
                <tr>
                    <td>Filtrado Por:</td>
                </tr>
                <tr>
                    <td>
                        <ul>
                            <?php foreach ($this->dados as $titulo => $filtro) { ?>
                                <li><?php echo $titulo; ?>: <?php echo $filtro; ?></li>
                        <?php }
                                } ?>
                        </ul>
                    </td>
                </tr>
        </table>
        <br />
        <table border="1">
            <tr class="itenTitulo">
                <td colspan="10">Relatório do protocolo</td>
            </tr>
            <tr class="titulo">
                <td>Data/Hora</td>
                <td>Tipo</td>
                <td>Número</td>
                <td>Procedência</td>
                <td>Setor</td>
                <td>Funcionário</td>
                <td>Interessado</td>
                <td>Resumo</td>
                <td>Localização Atual</td>
                <td>Situação</td>
            </tr>
            <?php
                    if ($registros) {
                        foreach ($registros as $registro) {
                            $atual = $registro->pegaSetorAtual();
                            ?>
                    <tr class="grade">
                        <td><?php echo $registro->data_criacao; ?> - <?php echo $registro->hora_criacao; ?></td>
                        <td><?php echo $registro->findParentRow("TbDocumentoTipo")->toString(); ?></td>
                        <td><?php echo $registro->mostrarNumero(); ?></td>
                        <td><?php echo $registro->mostrarProcedencia(); ?></td>
                        <td><?php echo $registro->findParentRow("TbSetor")->sigla; ?></td>
                        <td><?php echo $registro->findParentRow("TbFuncionario")->toString(); ?></td>
                        <td><?php echo $registro->mostrarInteressado(); ?></td>
                        <td><?php echo $registro->resumo; ?></td>
                        <td><?php echo $atual->toString(); ?></td>
                        <td><?php echo $registro->findParentRow("TbDocumentoStatus")->toString(); ?></td>
                    </tr>
            <?php }
                    } ?>
        </table>
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
            $this->lastPage();
            $this->download();
        }

        public function css()
        {
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

            .grade td {
                line-height: 6.5px;
            }

            .itenTitulo td {
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
