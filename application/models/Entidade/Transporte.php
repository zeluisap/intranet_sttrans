<?php

class Transporte extends Escola_Entidade {

    private $_concessao = false;

    public function init() {
        $this->get_concessao();
    }
    
    public function getTransporteGrupo() {
        return $this->findParentRow("TbTransporteGrupo");
    }

    public function set_concessao($concessao) {
        $this->_concessao = $concessao;
        $this->id_concessao = $this->_concessao->getId();
    }

    public function get_concessao() {
        if (!$this->_concessao) {
            $concessao = $this->findParentRow("TbConcessao");
            if ($concessao) {
                $this->set_concessao($concessao);
            } else {
                $tb = new TbConcessao();
                $this->set_concessao($tb->createRow());
            }
        }
        return $this->_concessao;
    }

    public function setFromArray(array $data) {
        if (isset($data["codigo"])) {
            $data["codigo"] = Escola_Util::maiuscula($data["codigo"]);
        }
        parent::setFromArray($data);
        if ($this->possui_concessao()) {
            $this->_concessao->setFromArray($data);
        }
    }

    public function save($flag = false) {
        if ($this->possui_concessao()) {
            $this->_concessao->save();
            if ($this->_concessao->getId()) {
                $this->id_concessao = $this->_concessao->getId();
            }
        } else {
            $concessao = $this->get_concessao();
            $concessao->delete();
            $this->id_concessao = null;
        }
        parent::save($flag);
        /*
         * atualização das solicitações de serviço, caso haja mudança no tipo de transporte.
         */
        $sss = $this->pegaServicoSolicitacao();
        if ($sss) {
            $tb_stg = new TbServicoTransporteGrupo();
            foreach ($sss as $ss) {
                $stg = $ss->findParentRow("TbServicoTransporteGrupo");
                if ($stg) {
                    if ($this->id_transporte_grupo != $stg->id_transporte_grupo) {
                        $rs_stg_novo = $tb_stg->listar(array("id_transporte_grupo" => $this->id_transporte_grupo, "id_servico" => $stg->id_servico));
                        if ($rs_stg_novo && count($rs_stg_novo)) {
                            $stg_novo = $rs_stg_novo->current();
                        } else {
                            $dados = array();
                            $dados["id_servico"] = $stg->id_servico;
                            $dados["id_transporte_grupo"] = $this->id_transporte_grupo;
                            $valor = $stg->pega_valor();
                            $dados["valor"] = Escola_Util::number_format($valor->valor);
                            $dados["validade_dias"] = $stg->validade_dias;
                            $dados["obrigatorio"] = $stg->obrigatorio;
                            $dados["juros_dia"] = Escola_Util::number_format($stg->juros_dia);
                            $dados["id_periodicidade"] = $stg->id_periodicidade;
                            $dados["mes_referencia"] = $stg->mes_referencia;
                            $dados["vencimento_dias"] = $stg->vencimento_dias;
                            $stg_novo = $tb_stg->createRow();
                            $stg_novo->setFromArray($dados);
                            $errors = $stg_novo->getErrors();
                            if (!$errors) {
                                $stg_novo->save();
                            } else {
                                Zend_Debug::dump($linha);
                                Zend_Debug::dump($stg->toArray());
                                Zend_Debug::dump($errors);
                                die();
                            }
                        }
                        if ($stg_novo->getId()) {
                            $ss->id_servico_transporte_grupo = $stg_novo->getId();
                            $ss->save();
                        }
                    }
                }
            }
        }

        //$this->atualiza_solicitacao_servicos();
    }

    public function delete() {
        $tps = $this->findDependentRowset("TbTransportePessoa");
        if ($tps && count($tps)) {
            foreach ($tps as $tp) {
                $tp->delete();
            }
        }
        $tvs = $this->findDependentRowset("TbTransporteVeiculo");
        if ($tvs && count($tvs)) {
            foreach ($tvs as $tv) {
                $tv->delete();
            }
        }
        $registros = $this->pegaServicoSolicitacao();
        if ($registros && count($registros)) {
            foreach ($registros as $registro) {
                $registro->delete();
            }
        }
        /*
          $registros = $this->findDependentRowset("TbTaxi");
          if ($registros && count($registros)) {
          foreach ($registros as $registro) {
          $registro->delete();
          }
          }
         */
        parent::delete();
    }

    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_transporte_grupo)) {
            $msgs[] = "CAMPO GRUPO DE TRANSPORTE OBRIGATÓRIO!";
        }
        if (!trim($this->codigo)) {
            $msgs[] = "CAMPO CÓDIGO OBRIGATÓRIO!";
        }
        $rg = $this->getTable()->fetchAll(" id_transporte_grupo = '{$this->id_transporte_grupo}' and codigo = '{$this->codigo}' and id_transporte <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TRANSPORTE JÁ CADASTRADO!";
        }
        $msg_concessao = false;
        if ($this->possui_concessao()) {
            $msg_concessao = $this->_concessao->getErrors();
        }
        if ($msg_concessao) {
            $msgs = array_merge($msgs, $msg_concessao);
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function pegaServicoSolicitacao() {
        $tb = new TbServicoSolicitacao();
        /*
          $rs = $tb->listar(array("tipo" => "TR", "chave" => $this->getId()));
          if ($rs && count($rs)) {
          return $rs;
          }
         */
        $rs = $tb->listar(array("id_transporte" => $this->getId()));
        if ($rs && count($rs)) {
            return $rs;
        }
        return false;
    }

    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->pegaServicoSolicitacao();
        $sss = false;
        if ($registros && count($registros)) {
            foreach ($registros as $registro) {
                if ($registro->pago()) {
                    $sss = $registro;
                }
            }
        }
        if ($sss) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getTransporteInstancia() {
        $db = Zend_Registry::get("db");
        $tg = $this->findParentRow("TbTransporteGrupo");
        if (!$tg) {
            return $this;
        }
        $class_name = "Tb" . $tg->descricao;
        try {
            if (class_exists($class_name)) {
                $tb = new $class_name();
                if ($tg->taxi()) {
                    $obj = false;
                    $sql = $tb->select();
                    $sql->where("id_transporte = {$this->getId()}");
                    $rs = $tb->fetchAll($sql);
                    if ($rs && count($rs)) {
                        $obj = $rs->current();
                    }
                    if (!$obj) {
                        $obj = $tb->createRow();
                        $obj->id_transporte = $this->getId();
                    }
                    return $obj;
                }
                return $this;
            }
        } catch (Exception $e) {
            return $this;
        }
        return $this;
    }

    public function view() {
        $concessao = $this->findParentRow("TbConcessao");
        $proprietario = $this->pegaProprietario();
        $veiculo = $this->pegaVeiculo();
        ob_start();
        ?>
        <div class="well">
            <div class="page-header">
                <h4>Cadastro de Transporte</h4>
            </div>
            <dl class="dl-horizontal">
                <dt>Id:</dt>
                <dd><?php echo $this->getId(); ?></dd>
            </dl>
            <dl class="dl-horizontal">
                <dt>Tipo:</dt>
                <dd><?php echo $this->findParentRow("TbTransporteGrupo")->toString(); ?></dd>
            </dl>
            <dl class="dl-horizontal">
                <dt>Código:</dt>
                <dd><?php echo $this->codigo; ?></dd>
            </dl>
            <?php if ($proprietario) { ?>
                <dl class="dl-horizontal">
                    <dt>Proprietário:</dt>
                    <dd><?php echo $proprietario->toString(); ?></dd>
                </dl>
            <?php } ?>
            <?php if ($veiculo) { ?>
                <dl class="dl-horizontal">
                    <dt>Veículo:</dt>
                    <dd><?php echo $veiculo->toString(); ?></dd>
                </dl>
            <?php } ?>
            <dl class="dl-horizontal">
                <dt>Status:</dt>
                <dd><?php echo $this->mostrar_status(); ?></dd>
            </dl>
        </div>
        <?php if ($concessao) { ?>
            <div class="well">
                <div class="page-header">
                    <h4>Cadastro de Concessão</h4>
                </div>
                <dl class="dl-horizontal">
                    <dt>Tipo de Concessão:</dt>
                    <dd><?php echo $concessao->findParentRow("TbConcessaoTipo")->toString(); ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Data da Concessão:</dt>
                    <dd><?php echo Escola_Util::formatData($concessao->concessao_data); ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Número da Concessão:</dt>
                    <dd><?php echo $concessao->numero; ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Decreto da Concessão:</dt>
                    <dd><?php echo $concessao->decreto; ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Número / Ano do Processo:</dt>
                    <dd><?php echo $concessao->processo_numero; ?> / <?php echo $concessao->processo_ano; ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Validade da Concessão:</dt>
                    <dd><?php echo $concessao->findParentRow("TbConcessaoValidade")->toString(); ?></dd>
                </dl>
            </div>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function toString() {
        $msgs = array();
        $tg = $this->findParentRow("TbTransporteGrupo");
        if ($tg) {
            $msgs[] = $tg->toString();
        }
        if ($this->codigo) {
            $msgs[] = $this->codigo;
        }
        $registro = $this->getTransporteInstancia();
        if (!($registro instanceof Transporte)) {
            $str = $registro->toString();
            if ($str) {
                $msgs[] = $str;
            }
        }
        $proprietario = $this->pegaProprietario();
        if ($proprietario) {
            $msgs[] = $proprietario->toString();
        }
        /*
          $veiculo = $this->pegaVeiculo();
          if ($veiculo) {
          $msgs[] = $veiculo->toString();
          }
         */
        return implode(" - ", $msgs);
    }

    public function pegaLicencaAtiva($dados = array()) {
        $tb = new TbServicoSolicitacaoStatus();
        $sss = $tb->getPorChave("PG"); //pagamento confirmado
        if (!$sss) {
            return null;
        }
        
        $tb = new TbServicoSolicitacao();
        $sql = $tb->select();
        $sql->Where("((id_transporte = {$this->getId()}) or (tipo = 'TR' and chave = {$this->getId()}))");
        $sql->where("id_servico_solicitacao_status = {$sss->getId()}");
        if (isset($dados["id_servico_transporte_grupo"]) && $dados["id_servico_transporte_grupo"]) {
            $sql->where("id_servico_transporte_grupo = {$dados["id_servico_transporte_grupo"]}");
        }
        $data_atual = new Zend_Date();
        $sql->where("data_inicio <= '{$data_atual->toString("YYYY-MM-dd")}'");
        $rs = $tb->fetchAll($sql);
        if ($rs && count($rs)) {
            return $rs;
        }
    }

    public function mostrar_status() {
        $tg = $this->findParentRow("TbTransporteGrupo");
        if ($tg) {
            $servicos = $tg->pegaServicosObrigatorios();
            if ($servicos && count($servicos)) {
                foreach ($servicos as $servico) {
                    $licenca = $this->pegaLicencaAtiva(array("id_servico_transporte_grupo" => $servico->getId()));
                    if (!$licenca) {
                        return "Transporte Possui Serviços Pendentes!";
                    }
                }
            }
            return "Regular";
        }
        return "";
    }

    public function regular() {
        $flag = false;
        $msg = $this->mostrar_status();
        if ($msg == "Regular") {
            $flag = true;
        }
        return false;
    }

    public function pegaPessoa($transporte_pessoa_tipo_chave) {
        $tb = new TbTransportePessoaTipo();
        $tpt = $tb->getPorChave($transporte_pessoa_tipo_chave);
        if (!$tpt) {
            return null;
        }
        
        $tb = new TbTransportePessoaStatus();
        $tps = $tb->getPorChave("A");
        if (!$tps) {
            return null;
        }
        
        $tb = new TbTransportePessoa();
        $sql = $tb->select();
        $sql->where("id_transporte = {$this->getId()}");
        $sql->where("id_transporte_pessoa_tipo = {$tpt->getId()}");
        $sql->where("id_transporte_pessoa_status = {$tps->getId()}");
        $rs = $tb->fetchAll($sql);
        if ($rs && count($rs)) {
            return $rs;
        }
        
        return null;
    }

    public function pegaProprietario() {
        $pessoas = $this->pegaPessoa("PR");
        if ($pessoas) {
            return $pessoas->current();
        }
        return false;
    }
    
    public function getVeiculos() {
        $tb = new TbTransporteVeiculo();
        $sql = $tb->select();
        $sql->where("id_transporte = {$this->getId()}");
        $rs = $tb->fetchAll($sql);
        if (!($rs && count($rs))) {
            return null;
        }
        return $rs;
    }

    public function pegaTransporteVeiculoAtivos() {
        $tb = new TbTransporteVeiculoStatus();
        $tvs = $tb->getPorChave("A");
        if ($tvs) {
            $tb = new TbTransporteVeiculo();
            $sql = $tb->select();
            $sql->where("id_transporte = {$this->getId()}");
            $sql->where("id_transporte_veiculo_status = {$tvs->getId()}");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                return $rs;
            }
        }
        return false;
    }

    public function pegaTransporteVeiculoAtivo() {
        $tvs = $this->pegaTransporteVeiculoAtivos();
        if ($tvs) {
            return $tvs->current();
        }
        return false;
    }

    public function pegaVeiculo() {
        $tv = $this->pegaTransporteVeiculoAtivo();
        if ($tv) {
            return $tv->findParentRow("TbVeiculo");
        }
        return false;
    }

    public function atualiza_solicitacao_servicos() {
        $registro = $this->getTransporteInstancia();
        if (!($registro instanceof Transporte)) {
            $registro->atualiza_solicitacao_servicos();
        } else {
            $hoje = new Zend_Date();
            $transporte = $this;
            if ($transporte) {
                $concessao = $transporte->findParentRow("TbConcessao");
                if ($concessao) {
                    $cadastro = new Zend_Date($concessao->concessao_data);
                    $tg = $transporte->findParentRow("TbTransporteGrupo");
                    if ($tg) {
                        $servicos = $tg->pegaServicosObrigatorios();
                        if ($servicos && count($servicos)) {
                            foreach ($servicos as $servico) {
                                /*
                                  $licenca = $transporte->pegaLicenca(array("id_servico_transporte_grupo" => $servico->getId()));
                                  if (!$licenca) {
                                  $tb = new TbServicoSolicitacao();
                                  $ss = $tb->createRow();
                                  $ss->setFromArray(array("tipo" => "TR",
                                  "chave" => $transporte->getId(),
                                  "id_servico_transporte_grupo" => $servico->getId(),
                                  "valor" => $servico->pega_valor()->valor));
                                  $ss->atualiza_datas();
                                  $ss->save();
                                  }
                                 */
                                $periodicidade = $servico->findParentRow("TbPeriodicidade");
                                if ($periodicidade && $periodicidade->anual()) {
                                    $hoje_ano = $hoje->get("YYYY");
                                    $cadastro_ano = $cadastro->get("YYYY");
                                    if ($cadastro_ano <= $hoje_ano) {
                                        $tb = new TbServicoSolicitacao();
                                        for ($ano = $cadastro_ano; $ano <= $hoje_ano; $ano++) {
                                            $sss = $tb->listar(array("id_servico_transporte_grupo" => $servico->getId(),
                                                "tipo" => "TR",
                                                "chave" => $transporte->getId(),
                                                "ano_referencia" => $ano));
                                            if (!$sss || !count($sss)) {
                                                $ss = $tb->createRow();
                                                $ss->setFromArray(array("id_servico_transporte_grupo" => $servico->getId(),
                                                    "tipo" => "TR",
                                                    "chave" => $transporte->getId(),
                                                    "ano_referencia" => $ano,
                                                    "valor" => $servico->pega_valor()->valor));
                                                $ss->atualiza_datas();
                                                $errors = $ss->getErrors();
                                                if (!$errors) {
                                                    $ss->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function pegaLicenca($dados = array()) {
        $tb = new TbServicoSolicitacao();
        $sql = $tb->select();
        $sql->where("tipo = 'TR'");
        $sql->where("chave = {$this->getId()}");
        if (isset($dados["id_servico_transporte_grupo"]) && $dados["id_servico_transporte_grupo"]) {
            $sql->where("id_servico_transporte_grupo = {$dados["id_servico_transporte_grupo"]}");
        }
        $data_atual = new Zend_Date();
        $sql->where("data_inicio <= '{$data_atual->toString("YYYY-MM-dd")}'");
        $rs = $tb->fetchAll($sql);
        if ($rs && count($rs)) {
            return $rs;
        }
        return false;
    }

    public function pega_licenca_trafego_ativa() {
        $tb = new TbServico();
        $servico = $tb->getPorCodigo("LT"); //Licença de Tráfego
        if (!$servico) {
            return false;
        }
        $tb = new TbServicoTransporteGrupo();
        $rs = $tb->listar(array("id_servico" => $servico->getId(),
            "id_transporte_grupo" => $this->id_transporte_grupo));
        $stg = false;
        if ($rs && count($rs)) {
            $stg = $rs->current();
        }
        if (!$stg) {
            return false;
        }
        $ss = $this->pegaLicencaAtiva(array("id_servico_transporte_grupo" => $stg->getId()));
        if (!$ss) {
            return false;
        }
        foreach ($ss as $s) {
            if (!$s->valido()) {
                continue;
            }
            
            $obj = $s->pegaReferencia();
            if (!$obj || !$obj->ativo()) {
                continue;
            }
            return $s;
        }
    }

    public function mostrar_codigo() {
        /*
          $obj = $this->getTransporteInstancia();
          if ($obj && (!($obj instanceof Transporte))) {
          return $obj->mostrar_codigo();
          }
         */
        return $this->codigo;
    }

    public function listar_motorista() {
        $tb = new TbTransportePessoaTipo();
        $tpt = $tb->getPorChave("MO");
        if ($tpt) {
            $tb = new TbTransportePessoa();
            return $tb->listar(array("id_transporte" => $this->getId(), "id_transporte_pessoa_tipo" => $tpt->getId()));
        }
        return false;
    }

    public function possui_concessao() {
        $tg = $this->findParentRow("TbTransporteGrupo");
        if ($tg) {
            return $tg->possui_concessao();
        }
        return false;
    }

    public function pegaMotoristas() {
        $tb = new TbTransportePessoaTipo();
        $tpt = $tb->getPorChave("MO");
        if ($tpt) {
            $tb = new TbMotorista();
            $sql = $tb->select();
            $sql->from(array("m" => "motorista"));
            $sql->join(array("pm" => "pessoa_motorista"), "m.id_pessoa_motorista = pm.id_pessoa_motorista", array());
            $sql->join(array("pf" => "pessoa_fisica"), "pm.id_pessoa_fisica = pf.id_pessoa_fisica", array());
            $sql->join(array("tp" => "transporte_pessoa"), "tp.id_pessoa = pf.id_pessoa", array());
            $sql->where("id_transporte = {$this->getId()}");
            $sql->where("tp.id_transporte_pessoa_tipo = {$tpt->getId()}");
            $sql->order("pf.nome");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                return $rs;
            }
        }
        return false;
    }

    public function taxi() {
        $tg = $this->findParentRow("TbTransporteGrupo");
        if ($tg) {
            return $tg->taxi();
        }
        return false;
    }

    public function onibus() {
        $tg = $this->findParentRow("TbTransporteGrupo");
        if ($tg) {
            return $tg->onibus();
        }
        return false;
    }

    public function isVeiculoUnico() {
        $tg = $this->findParentRow("TbTransporteGrupo");
        if ($tg) {
            return $tg->isVeiculoUnico();
        }
        return false;
    }

    public function getLicencaCodigo() {
        if ($this->taxi()) {
            return "ct";
        }
        return "lt";
    }
}