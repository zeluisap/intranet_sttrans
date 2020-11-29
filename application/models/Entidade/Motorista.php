<?php
class Motorista extends Escola_Entidade
{

    public function init()
    {
        if (!$this->getId()) {
            $this->data_cadastro = date("Y-m-d");
        }
    }

    public function getTransporteGrupo()
    {
        return $this->findParentRow("TbTransporteGrupo");
    }

    public function setFromArray(array $data)
    {
        if (isset($data["data_cadastro"])) {
            $data["data_cadastro"] = Escola_Util::montaData($data["data_cadastro"]);
        }
        parent::setFromArray($data);
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->id_pessoa_motorista)) {
            $msgs[] = "CAMPO PESSOA OBRIGATÓRIO!";
        }
        if (!trim($this->id_transporte_grupo)) {
            $msgs[] = "CAMPO GRUPO DE TRANSPORTE OBRIGATÓRIO!";
        }
        if (!trim($this->data_cadastro)) {
            $msgs[] = "CAMPO DATA DE CADASTRO OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaData($this->data_cadastro)) {
            $msgs[] = "CAMPO DATA DE CADASTRO INVÁLIDO!";
        }
        if ($this->matricula) {
            $rg = $this->getTable()->fetchAll(" id_transporte_grupo = {$this->id_transporte_grupo} and  matricula = '{$this->matricula}' and id_motorista <> '" . $this->getId() . "' ");
            if ($rg && count($rg)) {
                $msgs[] = "MATRÍCULA JÁ REGISTRADA PARA OUTRO MOTORISTA!";
            }
        }
        if ($this->id_pessoa_motorista) {
            $rg = $this->getTable()->fetchAll(" id_transporte_grupo = {$this->id_transporte_grupo} and id_pessoa_motorista = '{$this->id_pessoa_motorista}' and id_motorista <> '" . $this->getId() . "' ");
            if ($rg && count($rg)) {
                $msgs[] = "MOTORISTA JÁ CADASTRADO!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function save()
    {
        $id = $this->getId();
        if (!$this->matricula) {
            $tb = $this->getTable();
            $this->matricula = $tb->pegaProximaMatricula($this->id_transporte_grupo);
        }
        $id_motorista = parent::save();
        // if (!$id) {
        //     $this->atualizaCarteiras();
        // }
        return $id_motorista;
    }

    public function getDeleteErrors()
    {
        $msgs = array();
        $tb = new TbServicoSolicitacao();
        $objs = $tb->fetchAll("tipo = 'MO' and chave = {$this->getId()}");
        if ($objs && count($objs)) {
            $msgs[] = "EXISTEM SOLICITAÇÕES DE SERVIÇOS VINCULADAS A ESTE MOTORISTA!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getPessoaMotorista()
    {
        $pm = $this->findParentRow("TbPessoaMotorista");
        if (!$pm) {
            return null;
        }
        return $pm;
    }

    public function getPessoaFisica()
    {
        return $this->pegaPessoaFisica();
    }

    public function pegaPessoaFisica()
    {
        $pm = $this->getPessoaMotorista();
        if (!$pm) {
            return null;
        }

        $pf = $pm->findParentRow("TbPessoaFisica");
        if (!$pf) {
            return null;
        }

        return $pf;
    }

    public function toString()
    {
        $txt = array($this->matricula);
        $pf = $this->pegaPessoaFisica();
        if ($pf) {
            $txt[] = $pf->toString();
        }
        return implode(" - ", $txt);
    }

    public function pegaSolicitacaoAtiva()
    {
        if ($this->getId()) {
            $tb = new TbServicoSolicitacao();
            $sql = $tb->select();
            $sql->from(array("ss" => "servico_solicitacao"));
            $sql->join(array("stg" => "servico_transporte_grupo"), "ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo", array());
            $sql->join(array("s" => "servico"), "stg.id_servico = s.id_servico", array());

            $sql->where("ss.tipo = 'MO'"); //motorista
            $sql->where("s.codigo = 'CM'"); //carteira de motorista

            $sql->where("ss.chave = {$this->getId()}");
            $sql->where("ss.data_inicio <= '" . date("Y-m-d") . "'");
            $sql->where("ss.data_validade >= '" . date("Y-m-d") . "'");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                return $rs->current();
            }
        }
        return false;
    }

    public function pegaSolicitacao()
    {
        $tb = new TbServico();
        $servico = $tb->getPorCodigo("CM");
        $tg = $this->findParentRow("TbTransporteGrupo");
        if ($servico && $tg) {
            $tb = new TbServicoTransporteGrupo();
            $stgs = $tb->listar(array("id_servico" => $servico->getId(), "id_transporte_grupo" => $tg->getId()));
            if ($stgs && count($stgs)) {
                $stg = $stgs->current();
                $tb = new TbServicoSolicitacao();
                $sss = $tb->listar(array(
                    "id_servico_transporte_grupo" => $stg->getId(),
                    "tipo" => "MO",
                    "chave" => $this->getId()
                ));
                if ($sss && count($sss)) {
                    return $sss;
                }
            }
        }
        return false;
    }

    public function regular()
    {
        $sss = $this->pegaSolicitacao();
        if (!$sss) {
            return false;
        }

        foreach ($sss as $ss) {
            if (!$ss->pago()) {
                return false;
                break;
            }
        }

        return true;
    }

    public function mostrarStatus()
    {
        if ($this->regular()) {
            return "Regular";
        }
        return "Irregular";
    }

    public function pegaProprietario()
    {
        $pm = $this->findParentRow("TbPessoaMotorista");
        if ($pm) {
            return $pm->findParentRow("TbPessoaFisica");
        }
        return false;
    }

    public function atualizaCarteiras()
    {
        $cadastro = new Zend_Date($this->data_cadastro);
        $hoje = new Zend_Date();
        $tb = new TbServico();
        $servico = $tb->getPorCodigo("CM");
        $tg = $this->findParentRow("TbTransporteGrupo");
        if (!($servico && $tg)) {
            return;
        }

        $tb = new TbServicoTransporteGrupo();
        $stgs = $tb->listar(array("id_servico" => $servico->getId(), "id_transporte_grupo" => $tg->getId()));
        if (!($stgs && count($stgs))) {
            return;
        }

        $stg = $stgs->current();
        $periodicidade = $stg->findParentRow("TbPeriodicidade");
        if (!($periodicidade && $periodicidade->anual())) {
            return;
        }

        $hoje_ano = $hoje->get("YYYY");
        $cadastro_ano = $cadastro->get("YYYY");
        if (($cadastro_ano <= $hoje_ano)) {
            return;
        }

        $tb = new TbServicoSolicitacao();
        for ($ano = $cadastro_ano; $ano <= $hoje_ano; $ano++) {
            $sss = $tb->listar(array(
                "id_servico_transporte_grupo" => $stg->getId(),
                "tipo" => "MO",
                "chave" => $this->getId(),
                "ano_referencia" => $ano
            ));

            if ($sss && count($sss)) {
                continue;
            }

            $ss = $tb->createRow();
            $ss->setFromArray(array(
                "id_servico_transporte_grupo" => $stg->getId(),
                "tipo" => "MO",
                "chave" => $this->getId(),
                "ano_referencia" => $ano,
                "valor" => $stg->pega_valor()->valor
            ));

            $ss->atualiza_datas();
            $errors = $ss->getErrors();

            if ($errors) {
                continue;
            }

            $ss->save();
        }
    }

    public function toPDF($ss = false)
    {
        if ($this->getId()) {
            $relatorio = new Escola_Relatorio_Carteira_Motorista();
            $relatorio->toPDF($this, $ss);
        }
        return false;
    }

    public function pegaCarteiraAtiva()
    {
        if ($this->getId()) {
            $tb = new TbServicoSolicitacao();
            $sql = $tb->select();
            $sql->from(array("ss" => "servico_solicitacao"));
            $sql->join(array("stg" => "servico_transporte_grupo"), "ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo", array());
            $sql->join(array("s" => "servico"), "stg.id_servico = s.id_servico", array());

            $sql->where("s.codigo = 'CM'");

            $sql->where("ss.tipo = 'MO'");
            $sql->where("ss.chave = {$this->getId()}");
            $sql->where("ss.data_inicio <= '" . date("Y-m-d") . "'");
            $sql->where("ss.data_validade >= '" . date("Y-m-d") . "'");

            $sql->order("ss.data_validade desc");

            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                return $rs->current();
            }
        }
        return false;
    }
}
