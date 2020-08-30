<?php
class Veiculo extends Escola_Entidade
{

    public function getFabricante()
    {
        return $this->findParentRow("TbFabricante");
    }

    public function getCor()
    {
        return $this->findParentRow("TbCor");
    }

    public function getVeiculoTipo()
    {
        return $this->findParentRow("TbVeiculoTipo");
    }

    public function getVeiculoCategoria()
    {
        return $this->findParentRow("TbVeiculoCategoria");
    }

    public function getVeiculoEspecie()
    {
        return $this->findParentRow("TbVeiculoEspecie");
    }

    public function setFromArray(array $dados)
    {
        if (isset($dados["chassi"])) {
            $dados["chassi"] = Escola_Util::maiuscula($dados["chassi"]);
        }
        if (isset($dados["placa"])) {
            $dados["placa"] = Escola_Util::maiuscula($dados["placa"]);
        }
        if (isset($dados["modelo"])) {
            $dados["modelo"] = Escola_Util::maiuscula($dados["modelo"]);
        }
        if (isset($dados["data_aquisicao"])) {
            $dados["data_aquisicao"] = Escola_Util::montaData($dados["data_aquisicao"]);
        }
        if (isset($dados["tara"])) {
            if (!$dados["tara"]) {
                $dados["tara"] = 0;
            } else {
                $dados["tara"] = Escola_Util::montaNumero($dados["tara"]);
            }
        }
        if (isset($dados["placa"])) {
            $filter = new Zend_Filter_Alnum();
            $dados["placa"] = $filter->filter($dados["placa"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->id_veiculo_tipo)) {
            $msgs[] = "CAMPO TIPO DE VEÍCULO OBRIGATÓRIO!";
        }
        if (!trim($this->id_veiculo_categoria)) {
            $msgs[] = "CAMPO CATEGORIA DE VEÍCULO OBRIGATÓRIO!";
        }
        if (!trim($this->id_uf)) {
            $msgs[] = "CAMPO UF OBRIGATÓRIO!";
        }
        if (!trim($this->chassi)) {
            $msgs[] = "CAMPO CHASSI OBRIGATÓRIO!";
        }
        if (!trim($this->id_municipio)) {
            $msgs[] = "CAMPO MUNICÍPIO OBRIGATÓRIO!";
        }
        if (!trim($this->id_combustivel)) {
            $msgs[] = "CAMPO COMBUSTÍVEL OBRIGATÓRIO!";
        }
        /*
		if (!trim($this->ano_fabricacao)) {
			$msgs[] = "CAMPO ANO DE FABRICAÇÃO OBRIGATÓRIO!";
		}
        if (!trim($this->ano_modelo)) {
			$msgs[] = "CAMPO ANO DO MODELO OBRIGATÓRIO!";
		} 
         */
        if (!trim($this->id_cor)) {
            $msgs[] = "CAMPO COR OBRIGATÓRIO!";
        }
        if (!trim($this->id_fabricante)) {
            $msgs[] = "CAMPO FABRICANTE OBRIGATÓRIO!";
        }
        if (trim($this->tara) && !is_numeric($this->tara)) {
            $msgs[] = "CAMPO TARA É NUMÉRICO!";
        }
        if (trim($this->lotacao) && !is_numeric($this->lotacao)) {
            $msgs[] = "CAMPO LOTAÇÃO É NUMÉRICO!";
        }
        if ($this->chassi) {
            $rg = $this->getTable()->fetchAll(" chassi = '{$this->chassi}' and id_veiculo <> '" . $this->getId() . "' ");
            if ($rg && count($rg)) {
                $veiculo = $rg->current();
                $msgs[] = "NÚMERO DO CHASSI {$this->placa} JÁ REGISTRADO PARA O VEÍCULO {$veiculo->toString()}!";
            }
        }
        if ($this->placa) {
            $rg = $this->getTable()->fetchAll(" placa = '{$this->placa}' and id_veiculo <> '" . $this->getId() . "' ");
            if ($rg && count($rg)) {
                $veiculo = $rg->current();
                $msgs[] = "PLACA {$this->placa} JÁ REGISTRADA PARA O VEÍCULO {$veiculo->toString()}!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getDeleteErrors()
    {
        $msgs = array();
        $registros = $this->findDependentRowset("TbTransporteVeiculo");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function mostrar_isento()
    {
        if ($this->isento == "S") {
            return "SIM";
        }
        return "NÃO";
    }

    public function toString()
    {
        $msgs = array();
        $msgs[] = $this->chassi;
        $vt = $this->findParentRow("TbVeiculoTipo");
        if ($vt) {
            $msgs[] = $vt->toString();
        }
        if ($this->placa) {
            $msgs[] = $this->placa;
        }
        $f = $this->findParentRow("TbFabricante");
        if ($f) {
            $msgs[] = $f->toString();
        }
        return implode(" - ", $msgs);
    }

    public function mostrar_placa()
    {
        if (!$this->sem_placa()) {
            return $this->placa;
        }
        return "--";
    }

    public function sem_placa()
    {
        if (trim($this->placa)) {
            return false;
        }
        return true;
    }

    public function pegaAutoInfracaoNotificacao()
    {
        $tb = new TbAutoInfracaoNotificacao();
        $rs = $tb->listar(array("id_veiculo" => $this->getId()));
        if ($rs && count($rs)) {
            return $rs;
        }
        return false;
    }

    public function mostrarTabelaNotificacao()
    {
        $notificacoes = $this->pegaAutoInfracaoNotificacao();
        ob_start();
        ?>
        <?php if ($notificacoes) { ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th colspan="8">Notificações de Infração do Veículo</th>
                    </tr>
                    <tr>
                        <th>Código</th>
                        <th>Agente</th>
                        <th width="500px">Infrações</th>
                        <th>Data / Hora Infração</th>
                        <th>Localização Infração</th>
                        <th>Motorista</th>
                        <th>Recolhido</th>
                        <th>Clandestino</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                                foreach ($notificacoes as $notificacao) {
                                    $txt_recolhido = $txt_clandestino = $txt_agente = "--";
                                    $ai = $notificacao->pegaAutoInfracao();
                                    $pf = $notificacao->findParentRow("TbPessoaFisica");
                                    $txt_infracao = "--";
                                    $ids = array();
                                    $infracoes = $notificacao->listarInfracao();
                                    if ($infracoes) {
                                        foreach ($infracoes as $infracao) {
                                            $ids[] = $infracao->toString();
                                        }
                                    }
                                    if (count($ids)) {
                                        $txt_infracao = "<ul><li>" . implode("</li><li>", $ids) . "</li></ul>";
                                    }
                                    $txt_recolhido = $notificacao->mostrarVeiculoRecolhido();
                                    $txt_clandestino = $notificacao->mostrarClandestino();
                                    if ($ai) {
                                        $agente = $ai->findParentRow("TbAgente");
                                        if ($agente) {
                                            $txt_agente = $agente->toString();
                                        }
                                    }
                                    ?>
                        <tr>
                            <td><?php echo $ai->mostrar_codigo(); ?></td>
                            <td><?php echo $txt_agente; ?></td>
                            <td><?php echo $txt_infracao; ?></td>
                            <td><?php echo Escola_Util::formatData($notificacao->data_infracao); ?> <?php echo $notificacao->hora_infracao; ?></td>
                            <td><?php echo $notificacao->local_infracao; ?></td>
                            <td><?php echo ($pf) ? $pf->toString() : "--"; ?></td>
                            <td><?php echo $txt_recolhido; ?></td>
                            <td><?php echo $txt_clandestino; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function recolhido()
    {
        $tb = new TbAutoInfracaoNotificacao();
        $sql = $tb->select();
        $sql->where("id_veiculo = {$this->getId()}");
        $sql->where("veiculo_recolhido = 'S'");
        $nots = $tb->fetchAll($sql);
        if ($nots && count($nots)) {
            return true;
        }
        return false;
    }

    public function clandestino()
    {
        $tb = new TbAutoInfracaoNotificacao();
        $sql = $tb->select();
        $sql->where("id_veiculo = {$this->getId()}");
        $sql->where("clandestino = 'S'");
        $nots = $tb->fetchAll($sql);
        if ($nots && count($nots)) {
            return true;
        }
        return false;
    }

    public function retido()
    {
        if ($this->getId()) {
            $tb = new TbVeiculoRetido();
            return $tb->retido($this);
        }
        return false;
    }

    public function getProprietario()
    {
        $prop = $this->findParentRow("TbPessoa");
        if (!$prop) {
            return null;
        }

        return $prop;
    }
}
