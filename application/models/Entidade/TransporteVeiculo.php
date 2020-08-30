<?php
class TransporteVeiculo extends Escola_Entidade
{

    public function pegaVeiculo()
    {
        $obj = $this->findParentRow("TbVeiculo");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }

    public function init()
    {
        parent::init();
        if (!$this->getId()) {
            $tb = new TbTransporteVeiculoStatus();
            $tvs = $tb->getPorChave("A");
            if ($tvs) {
                $this->id_transporte_veiculo_status = $tvs->getId();
            }
            $this->data_cadastro = date("Y-m-d");
        }
    }

    public function getTransporteVeiculoStatus()
    {
        return $this->findParentRow("TbTransporteVeiculoStatus");
    }

    public function setFromArray(array $dados)
    {
        if (isset($dados["processo_data"])) {
            $dados["processo_data"] = Escola_Util::montaData($dados["processo_data"]);
        }
        if (isset($dados["data_cadastro"])) {
            $dados["data_cadastro"] = Escola_Util::montaData($dados["data_cadastro"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->id_transporte)) {
            $msgs[] = "CAMPO TRANSPORTE OBRIGATÓRIO!";
        }
        if (!trim($this->id_veiculo)) {
            $msgs[] = "CAMPO VEÍCULO OBRIGATÓRIO!";
        }
        if (!trim($this->id_transporte_veiculo_status)) {
            $msgs[] = "CAMPO STATUS DO TRANSPORTE DE VEÍCULO OBRIGATÓRIO!";
        }
        $rg = $this->getTable()->fetchAll(" id_transporte = '{$this->id_transporte}' and id_veiculo = '{$this->id_veiculo}' and id_transporte_veiculo_status = '{$this->id_transporte_veiculo_status}' and id_transporte_veiculo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "VÍNCULO DE TRANSPORTE E VEÍCULO JÁ CADASTRADO!";
        }
        $veiculo = $this->pegaVeiculo();
        if ($veiculo && $veiculo->retido()) {
            $msgs[] = "VEÍCULO RETIDO NO PÁTIO DA INSTITUIÇÃO!";
        }
        if ($this->id_veiculo && !$this->baixa()) {
            $transporte = $this->findParentRow("TbTransporte");
            if ($transporte && $transporte->taxi()) {
                $veiculo = $transporte->pegaVeiculo();
                if ($veiculo && ($veiculo->getId() != $this->id_veiculo)) {
                    $msgs[] = "Já Existe um Veículo Ativo Cadastrado Para este Transporte, para Adicionar um Novo Veículo é Preciso Dar Baixa no Antigo!";
                }
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function delete()
    {
        $baixa = $this->pegaBaixa();
        if ($baixa) {
            $baixa->delete();
        }
        return parent::delete();
    }

    public function ativo()
    {
        $tvs = $this->findParentRow("TbTransporteVeiculoStatus");
        if ($tvs) {
            return $tvs->ativo();
        }
        return false;
    }

    public function baixa()
    {
        $tvs = $this->findParentRow("TbTransporteVeiculoStatus");
        if ($tvs) {
            return $tvs->baixa();
        }
        return false;
    }

    public function pegaBaixa()
    {
        $tb = new TbTransporteVeiculoBaixa();
        $rs = $tb->fetchAll("id_transporte_veiculo = {$this->getId()}");
        if ($rs) {
            return $rs->current();
        }
        return false;
    }

    public function mostrar_processo()
    {
        $txt = array();
        if ($this->processo) {
            $txt[] = $this->processo;
        }
        $processo_data = Escola_Util::formatData($this->processo_data);
        if ($processo_data) {
            $txt[] = $processo_data;
        }
        if (count($txt)) {
            return implode(" - ", $txt);
        }
        return "";
    }

    public function toString()
    {
        $veiculo = $this->findParentRow("TbVeiculo");
        if ($veiculo) {
            return $veiculo->toString();
        }
        return "";
    }

    public function pegaLicencaAtiva($codigo = "LT")
    {

        $transporte = $this->getTransporte();
        if (!$transporte) {
            return null;
        }

        $codigo = $transporte->getLicencaCodigo();
        $tb = new TbServico();
        $servico = $tb->getPorCodigo(strtoupper($codigo)); //Licença de Tráfego
        if (!$servico) {
            return null;
        }

        $transpote = $this->findParentRow("TbTransporte");
        if (!$transpote) {
            return null;
        }

        $tb = new TbServicoTransporteGrupo();
        $rs = $tb->listar(array(
            "id_servico" => $servico->getId(),
            "id_transporte_grupo" => $transpote->id_transporte_grupo
        ));
        if (!($rs && count($rs))) {
            return null;
        }

        $stg = $rs->current();
        $tb = new TbServicoSolicitacaoStatus();
        $sss = $tb->getPorChave("PG"); //pagamento confirmado
        if (!$sss) {
            return null;
        }

        $data_atual = new Zend_Date();
        $tb = new TbServicoSolicitacao();
        $sql = $tb->select();
        $sql->Where("(tipo = 'TV' and chave = {$this->getId()})");
        $sql->where("(id_servico_solicitacao_status = {$sss->getId()})");
        $sql->where("(id_servico_transporte_grupo = {$stg->getId()})");
        $sql->where("data_inicio <= '{$data_atual->toString("YYYY-MM-dd")}'");
        $sql->where("data_validade >= '{$data_atual->toString("YYYY-MM-dd")}'");
        $rs = $tb->fetchAll($sql);

        if ($rs && count($rs)) {
            return $rs;
        }

        return null;
    }

    public function cancelarBaixa()
    {
        if ($this->baixa()) {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            try {
                $tvbs = $this->findDependentRowset("TbTransporteVeiculoBaixa");
                if ($tvbs && count($tvbs)) {
                    foreach ($tvbs as $tvb) {
                        $tvb->delete();
                    }
                }
                $tb = new TbTransporteVeiculoStatus();
                $tvs = $tb->getPorChave("A");
                if ($tvs) {
                    $this->id_transporte_veiculo_status = $tvs->getId();
                    $this->save();
                }
                $db->commit();
                return true;
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
        return false;
    }


    public function getTransporte()
    {
        return $this->findParentRow("TbTransporte");
    }

}
