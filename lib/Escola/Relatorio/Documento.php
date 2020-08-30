<?php

class Escola_Relatorio_Documento extends Escola_Relatorio
{

    private $registro = false;

    public function __construct()
    {
        parent::__construct("relatorio_documento");
        $this->SetTopMargin(30);
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
                $this->registro = false;
                if (isset($this->dados["documento"]) && $this->dados["documento"]) {
                    $this->registro = $this->dados["documento"];
                }
                if ($this->registro) {
                    $this->AddPage();
                    ob_start();
                    $this->css();
                    ?>
            <table border="1" cellpadding="5" class="grade">
                <tr class="cabecalho">
                    <td colspan="2" align="center"><strong>INFORMAÇÕES DO DOCUMENTO</strong></td>
                </tr>
                <tr>
                    <td align="right" width="150px">ID:</td>
                    <td align="left" width="523px"><?php echo $this->registro->getId(); ?></td>
                </tr>
                <tr>
                    <td align="right">Tipo:</td>
                    <td align="left"><?php echo $this->registro->findParentRow("TbDocumentoTipo")->descricao; ?>
                        <?php if ($this->registro->findParentRow("TbDocumentoModo")->circular()) { ?>
                            - CIRCULAR
                        <?php } ?>
                    </td>
                </tr>
                <?php
                            $processo = $this->registro->pegaDocumentoPrincipal();
                            if ($processo) {
                                ?>
                    <tr>
                        <td align="right">Documento Principal:</td>
                        <td align="left"><?php echo $processo->toString(); ?></td>
                    </tr>
                <?php } ?>
                <?php
                            $doc = $this->registro->pegaDocumentoOriginal();
                            if ($doc) {
                                ?>
                    <tr>
                        <td align="right">Documento Original:</td>
                        <td align="left"><?php echo $doc->toString(); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td align="right">Número:</td>
                    <td align="left"><?php echo $this->registro->mostrarNumero(); ?></td>
                </tr>
                <?php
                            $prioridade = $this->registro->findParentRow("TbPrioridade");
                            if ($prioridade) {
                                ?>
                    <tr>
                        <td align="right">Prioridade:</td>
                        <td align="left"><?php echo $prioridade->toString(); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td align="right">Data / Hora:</td>
                    <td align="left"><?php echo Escola_Util::formatData($this->registro->data_criacao); ?> <?php echo $this->registro->hora_criacao; ?></td>
                </tr>
                <tr>
                    <td align="right">Criado Por:</td>
                    <td align="left"><?php echo $this->registro->findParentRow("TbFuncionario")->toString(); ?> - <?php echo $this->registro->findParentRow("TbSetor")->toString(); ?></td>
                </tr>
                <tr>
                    <td align="right">Procedência:</td>
                    <td align="left"><?php echo $this->registro->mostrarProcedencia(); ?></td>
                </tr>
                <tr>
                    <td align="right">Interessado:</td>
                    <td align="left"><?php echo $this->registro->mostrarInteressado(); ?></td>
                </tr>
                <?php
                            $atual = $this->registro->pegaSetorAtual();
                            if ($atual) {
                                ?>
                    <tr>
                        <td align="right">Setor Atual:</td>
                        <td align="left"><?php echo $atual->toString(); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td align="right">Resumo:</td>
                    <td align="left"><?php echo $this->registro->resumo; ?></td>
                </tr>
                <tr>
                    <td align="right">Tempo no Setor:</td>
                    <td align="left"><?php echo $this->registro->mostrarTempoSetor(); ?></td>
                </tr>
                <tr>
                    <td align="right">Situação:</td>
                    <td align="left"><?php echo $this->registro->findParentRow("TbDocumentoStatus")->toString(); ?></td>
                </tr>
            </table>
            <?php
                        $anexos = $this->registro->pegaTbDocumento();
                        if ($anexos) {
                            ?>
                <br />
                <table border="1" cellpadding="5" class="grade">
                    <tr class="cabecalho">
                        <td colspan="6" align="center">DOCUMENTOS VINCULADOS</td>
                    </tr>
                    <tr class="cabecalho">
                        <td width="90px">Tipo</td>
                        <td width="80px">Número</td>
                        <td width="200px">Interessado</td>
                        <td width="200px">Resumo</td>
                        <td width="103px">Tipo Anexo</td>
                    </tr>
                    <?php
                                    foreach ($anexos as $anexo) {
                                        $doc = $anexo->pegaObjeto();
                                        ?>
                        <tr>
                            <td>
                                <div align="left"><?php echo $doc->findParentRow("TbDocumentoTipo")->toString(); ?></div>
                            </td>
                            <td>
                                <div align="left"><?php echo $doc->mostrarNumero(); ?></div>
                            </td>
                            <td>
                                <div align="left"><?php echo $doc->mostrarInteressado(); ?></div>
                            </td>
                            <td>
                                <div align="left"><?php echo $doc->resumo; ?></div>
                            </td>
                            <td>
                                <div align="left"><?php echo $anexo->mostrarTipo(); ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
            <?php
                        $movs = $this->registro->pegaMovimentacao();
                        if ($movs) {
                            ?>
                <br />
                <table border="1" cellpadding="4" class="grade">
                    <tr class="cabecalho">
                        <td colspan="5">Movimentações</td>
                    </tr>
                    <tr class="cabecalho">
                        <td width="90px">Data / Hora</td>
                        <td width="100px">Tipo</td>
                        <td width="150px">Setor</td>
                        <td width="135px">Funcionário</td>
                        <td width="198px">Destino</td>
                    </tr>
                    <?php foreach ($movs as $mov) { ?>
                        <tr>
                            <td>
                                <div align="center"><?php echo Escola_Util::formatData($mov->data_movimentacao); ?> - <?php echo $mov->hora_movimentacao; ?></div>
                            </td>
                            <td>
                                <div align="center"><?php echo $mov->findParentRow("TbMovimentacaoTipo")->toString(); ?></div>
                            </td>
                            <td>
                                <div align="left"><?php echo $mov->findParentRow("TbSetor")->toString(); ?></div>
                            </td>
                            <td>
                                <div align="left"><?php echo $mov->findParentRow("TbFuncionario")->toString(); ?></div>
                            </td>
                            <td>
                                <div align="left"><?php echo $mov->mostrarDestino(); ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>

        <?php
                    $html = ob_get_contents();
                    ob_end_clean();
                    $this->writeHTML($html, true, false, true, false, '');
                    $this->lastPage();
                    $this->download();
                }
            }

            public function css()
            {
                ?>
        <style type="text/css">
            td {
                font-family: Arial;
                font-size: 8pt;
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
