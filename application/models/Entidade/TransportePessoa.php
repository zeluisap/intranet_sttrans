<?php

class TransportePessoa extends Escola_Entidade
{

    public $id_motorista = 0;

    public function init()
    {
        if (!$this->getId()) {
            $tb = new TbTransportePessoaStatus();
            $tps = $tb->getPorChave("A");
            if ($tps) {
                $this->id_transporte_pessoa_status = $tps->getId();
            }
        }
        $this->set_id_pessoa($this->id_pessoa);
    }

    public function getPessoa()
    {
        return $this->findParentRow("TbPessoa");
    }

    public function getTransporte()
    {
        return $this->findParentRow("TbTransporte");
    }

    public function getTransportePessoaTipo()
    {
        return $this->findParentRow("TbTransportePessoaTipo");
    }

    public function getTipo()
    {
        return $this->getTransportePessoaTipo();
    }

    public function set_id_pessoa($id_pessoa)
    {
        $this->id_pessoa = $id_pessoa;
        $pessoa = $this->findParentRow("TbPessoa");
        if (!($pessoa && $pessoa->pf())) {
            return null;
        }
        $pf = $pessoa->pegaPessoaFilho();
        if (!$pf) {
            return null;
        }
        $tb = new TbPessoaMotorista();
        $pms = $tb->fetchAll("id_pessoa_fisica = {$pf->getId()}");
        if (!($pms && count($pms))) {
            return null;
        }
        $pm = $pms->current();
        $tb = new TbMotorista();
        $ms = $tb->fetchAll("id_pessoa_motorista = {$pm->getId()}");
        if (!($ms && count($ms))) {
            return null;
        }
        $m = $ms->current();
        $this->id_motorista = $m->getId();
    }

    public function pegaMotorista()
    {
        $id = $this->id_motorista;

        if ($id) {
            $motorista = TbMotorista::pegaPorId($id);
            if ($motorista) {
                return $motorista;
            }
        }

        $pessoa = $this->getPessoa();
        if (!($pessoa && $pessoa->pf())) {
            return null;
        }

        $transporte = $this->getTransporte();
        if (!$transporte) {
            return null;
        }

        $tb = TbMotorista();
        $sql = $tb->select();
        $sql->from(array("m" => "motorista"));
        $sql->join(array("pm" => "pessoa_motorista"), "m.id_pessoa_motorista = pm.id_pessoa_motorista", array());
        $sql->join(array("pf" => "pessoa_fisica"), "pf.id_pessoa_fisica = pm.id_pessoa_fisica", array());
        $sql->where("m.id_transporte_grupo = ? ", $transporte->id_transporte_grupo);
        $sql->where("pf.id_pessoa = ?", $pessoa->getId());
        $ms = $tb->fetchAll($sql);
        if (!($ms && count($ms))) {
            return null;
        }
        return $ms->current();
    }

    public function set_id_motorista($id_motorista)
    {
        $this->id_motorista = $id_motorista;
        $motorista = $this->pegaMotorista();
        if ($motorista) {
            $pm = $motorista->findParentRow("TbPessoaMotorista");
            if ($pm) {
                $pf = $pm->findParentRow("TbPessoaFisica");
                if ($pf) {
                    $this->id_pessoa = $pf->id_pessoa;
                }
            }
        }
    }

    public function setFromArray(array $data)
    {
        if (isset($data["id_motorista"])) {
            $this->set_id_motorista($data["id_motorista"]);
            if ($this->id_pessoa) {
                if (isset($data["id_pessoa"])) {
                    unset($data["id_pessoa"]);
                }
            }
        }
        parent::setFromArray($data);
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->id_pessoa)) {
            $msgs[] = "CAMPO PESSOA OBRIGATï¿½RIO!";
        }
        if (!trim($this->id_transporte_pessoa_tipo)) {
            $msgs[] = "CAMPO TIPO DE Vï¿½NCULO DE PESSOA OBRIGATï¿½RIO!";
        }
        if (!trim($this->id_transporte)) {
            $msgs[] = "CAMPO TRANSPORTE OBRIGATï¿½RIO!";
        }
        if (!trim($this->id_transporte_pessoa_status)) {
            $msgs[] = "CAMPO STATUS DE Vï¿½NCULO DE PESSOA OBRIGATï¿½RIO!";
        }
        $rg = $this->getTable()->fetchAll(" id_pessoa = '{$this->id_pessoa}' and id_transporte_pessoa_tipo = '{$this->id_transporte_pessoa_tipo}' and id_transporte = '{$this->id_transporte}' and id_transporte_pessoa <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "Vï¿½NCULO DE PESSOA Jï¿½ CADASTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getDeleteErrors()
    {
        $msgs = array();
        if ($this->proprietario() && $this->ativo()) {
            $msgs[] = "Proprietï¿½rio Atual do Transporte nï¿½o pode ser Escluï¿½do!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function mudarStatus($status)
    {
        $tb = new TbTransportePessoaStatus();
        $tps = $tb->getPorChave($status);
        if (!$tps) {
            throw new Exception("Status de Pessoa Nï¿½o Localizado!");
        }
        $this->id_transporte_pessoa_status = $tps->getId();
        $this->save();
    }

    public function ativar()
    {
        $this->mudarStatus("A");
    }

    public function desativar()
    {
        $this->mudarStatus("I");
    }

    public function save($flag = false)
    {
        $tpt = $this->findparentRow("TbTransportePessoaTipo");
        if ($tpt && $tpt->proprietario()) {
            $tps = $this->findparentRow("TbTransportePessoaStatus");
            if ($tps && $tps->ativo()) {
                $transporte = $this->findParentRow("TbTransporte");
                if ($transporte) {
                    $proprietario = $transporte->pegaProprietario();
                    if ($proprietario && ($proprietario->id_pessoa != $this->id_pessoa)) {
                        $proprietario->desativar();
                    }
                }
            }
        }
        parent::save($flag);
    }

    public function ativo()
    {
        $tps = $this->findParentRow("TbTransportePessoaStatus");
        if ($tps) {
            return $tps->ativo();
        }
        return false;
    }

    public function proprietario()
    {
        $tpt = $this->findParentRow("TbTransportePessoaTipo");
        if ($tpt) {
            return $tpt->proprietario();
        }
        return false;
    }

    public function auxiliar()
    {
        $tpt = $this->findParentRow("TbTransportePessoaTipo");
        if ($tpt) {
            return $tpt->auxiliar();
        }
        return false;
    }

    public function toString()
    {
        $pessoa = $this->findParentRow("TbPessoa");
        if ($pessoa) {
            return $pessoa->toString();
        }
        return "";
    }

    public function toPDF($ss = false)
    {
        //if ($this->ativo() && !$this->motorista()) {
        if (!$this->ativo()) {
            throw new UnexpectedValueException("Falha ao Executar Operaï¿½ï¿½o, Pessoa Inativa!");
        }

        $transporte = $this->getTransporte();
        if (!$transporte) {
            throw new UnexpectedValueException("Falha ao Executar Operaï¿½ï¿½o, Transporte Invalido!");
        }

        $tg = $transporte->getTransporteGrupo();
        if (!$tg) {
            throw new UnexpectedValueException("Falha ao Executar Operaï¿½ï¿½o, Grupo de Transporte Invalido!");
        }

        $tpt = $this->getTransportePessoaTipo();
        if (!$tpt) {
            throw new UnexpectedValueException("Falha ao Executar Operaï¿½ï¿½o, Tipo de Grupo de Transporte Invalido!");
        }

        $s = $ss->getServico();
        if (!$s) {
            throw new UnexpectedValueException("Falha ao Executar Operaï¿½ï¿½o, Nenhum Serviï¿½o Vinculado!");
        }

        $classes = array();
        $classes[] = "Escola_Relatorio_Servico_{$s->codigo}_{$tpt->chave}_{$tg->chave}";
        $classes[] = "Escola_Relatorio_Servico_{$s->codigo}_{$tpt->chave}";
        $classes[] = "Escola_Relatorio_Servico_{$s->codigo}";

        $rel = null;
        foreach ($classes as $classe) {
            if (!class_exists($classe)) {
                continue;
            }
            $rel = new $classe($this);
            break;
        }

        if (!$rel) {
            throw new UnexpectedValueException("Falha ao Executar Operaï¿½ï¿½o, Relatorio Invalido!");
        }

        $rel->setRegistro($ss);

        $html = $rel->imprimir();

        return $html;
    }

    public function motorista()
    {
        $tipo = $this->findParentRow("TbTransportePessoaTipo");
        if ($tipo) {
            return $tipo->motorista();
        }
        return false;
    }

    public function mostrarStatus()
    {
        if ($this->ativo()) {
            if ($this->motorista()) {
                $motorista = $this->pegaMotorista();
                if ($motorista) {
                    return $motorista->mostrarStatus();
                }
            }
        }
        $status = $this->findParentRow("TbTransportePessoaStatus");
        if ($status) {
            return $status->toString();
        }
        return "";
    }

    public function getCarteira()
    {
        $transporte = $this->getTransporte();
        if (!$transporte) {
            throw new Escola_Exception("Falha ao localizar Carteira de Permissionï¿½rio!");
        }

        $tg = $transporte->getTransporteGrupo();
        if (!$tg) {
            throw new Escola_Exception("Falha ao localizar Carteira de Permissionï¿½rio!!");
        }

        $params = array(
            ":licenca_codigo" => "cp", //carteira de permissionario
            ":id_transporte_grupo" => $tg->getId(), ":tipo" => "tp", //transporte pessoa
            ":chave" => $this->getId(), ":status_chave" => "pg", //pagamento confirmado
            ":agora" => date("Y-m-d")
        );

        $tb = new TbServicoSolicitacao();

        $sql = $tb->select();

        $sql->from(array("ss" => "servico_solicitacao"));
        $sql->join(array("stg" => "servico_transporte_grupo"), "ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo", array());
        $sql->join(array("s" => "servico"), "stg.id_servico = s.id_servico", array());
        $sql->join(array("tg" => "transporte_grupo"), "stg.id_transporte_grupo = tg.id_transporte_grupo", array());
        $sql->join(array("sss" => "servico_solicitacao_status"), "ss.id_servico_solicitacao_status = sss.id_servico_solicitacao_status", array());

        $sql->where("lower(s.codigo) = lower(:licenca_codigo)");
        $sql->where("tg.id_transporte_grupo = :id_transporte_grupo");
        $sql->where("lower(ss.tipo) = lower(:tipo)");
        $sql->where("ss.chave = :chave");
        $sql->where("lower(sss.chave) = lower(:status_chave)");
        $sql->where("ss.data_inicio <= :agora");
        $sql->where("ss.data_validade >= :agora");

        $sql->order("ss.ano_referencia desc");
        $sql->order("ss.codigo desc");

        $sql->bind($params);

        $objs = $tb->fetchAll($sql);
        if (!$objs->count()) {
            throw new Exception("Falha ao Localizar Carteira de Permissionï¿½rio!!!");
        }

        if ($objs->count() > 1) {
            //throw new Exception("Permissionï¿½rio possui mais de uma Carteira Ativa!");
        }

        return $objs->current();
    }

    public function emitirCarteiraAtiva($tv = null, $licenca = null)
    {
        $ss = $this->getCarteira();
        if ($tv) {
            $ss->tipo = 'TV';
            $ss->chave = $tv->getId();
        }

        $ss->setLicencaAtual($licenca);
        $ss->toPDF();
    }
}
