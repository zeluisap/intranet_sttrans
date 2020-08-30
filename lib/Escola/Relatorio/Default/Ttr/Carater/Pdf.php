<?php

class Escola_Relatorio_Default_Ttr_Carater_Pdf extends Escola_Relatorio
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
            ini_set("display_errors", true);
            ini_set("max_execution_time", 10000);
            $stmt = $this->dados["statement"];
            $this->AddPage("L");
            ob_start();
            $this->css();
            ?>
        <table border="1" cellpadding="4">
            <tr>
                <th width="70px" align="center">Código</th>
                <th align="center" width="275px">Nome</th>
                <th width="100px" align="center">Decreto</th>
                <th width="80px" align="center">Dt Decreto</th>
                <th align="center" width="76px">Dt Cadastro</th>
                <th align="center" width="96px">Marca/Modelo</th>
                <th align="center" width="66px">Ano</th>
                <th align="center" width="66px">Placa</th>
                <th align="center" width="76px">Dt Licença</th>
                <th align="center" width="76px">Validade</th>
            </tr>
            <?php
                    if ($stmt && $stmt->rowCount()) {
                        $id_concessao_tipo = 0;
                        while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                            $transporte = TbTransporte::pegaPorId($obj->id_transporte);
                            $proprietario = $transporte->pegaProprietario();
                            $proprietario_txt = "--";
                            if ($proprietario) {
                                $proprietario_txt = $proprietario->toString();
                            }
                            $tv = TbTransporteVeiculo::pegaPorId($obj->id_transporte_veiculo);
                            $veiculo_txt = $data_cadastro = $txt_modelo = "";
                            if ($tv) {
                                $data_cadastro = Escola_Util::formatData($tv->data_cadastro);
                                $veiculo = $tv->findParentRow("TbVeiculo");
                                if ($veiculo) {
                                    $veiculo_txt = $veiculo->placa;
                                    $fabricante = $veiculo->findParentRow("TbFabricante");
                                    $modelo = array();
                                    if ($fabricante) {
                                        $modelo[] = $fabricante->descricao;
                                    }
                                    if ($veiculo->modelo) {
                                        $modelo[] = $veiculo->modelo;
                                    }
                                    if (count($modelo)) {
                                        $txt_modelo = implode(" / ", $modelo);
                                    }
                                    $ano = $veiculo->ano_fabricacao;
                                }
                            }
                            $licenca_numero = $licenca_data = $licenca_validade = "";
                            $licencas = $tv->pegaLicencaAtiva();
                            if ($licencas) {
                                $licenca = $licencas->current();
                                $licenca_numero = $licenca->mostrar_numero();
                                $licenca_data = Escola_Util::formatData($licenca->data_inicio);
                                $licenca_validade = Escola_Util::formatData($licenca->data_validade);
                            }
                            ?>
                    <tr>
                        <td align="center">
                            <div class="text-center"><?php echo $transporte->codigo; ?></div>
                        </td>
                        <td><?php echo $proprietario_txt; ?></td>
                        <td align="center">
                            <div class="text-center"><?php echo $obj->decreto; ?></div>
                        </td>
                        <td align="center">
                            <div class="text-center"><?php echo Escola_Util::formatData($obj->concessao_data); ?></div>
                        </td>
                        <td align="center">
                            <div class="text-center"><?php echo $data_cadastro; ?></div>
                        </td>
                        <td align="center">
                            <div class="text-center"><?php echo $txt_modelo; ?></div>
                        </td>
                        <td align="center">
                            <div class="text-center"><?php echo $ano; ?></div>
                        </td>
                        <td align="center">
                            <div class="text-center"><?php echo $veiculo_txt; ?></div>
                        </td>
                        <td align="center">
                            <div class="text-center"><?php echo $licenca_data; ?></div>
                        </td>
                        <td align="center">
                            <div class="text-center"><?php echo $licenca_validade; ?></div>
                        </td>
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
