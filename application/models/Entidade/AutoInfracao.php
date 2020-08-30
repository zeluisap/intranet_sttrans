<?php
class AutoInfracao extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $tb = new TbAutoInfracaoStatus();
            $ais = $tb->getPorChave("DI");
            if ($ais) {
                $this->id_auto_infracao_status = $ais->getId();
            }
        }
    }
    
    public function save($flag = false) {
        $id = $this->getId();
        $flag =  parent::save($flag);
        if (!$id) {
            $tb = new TbAutoInfracaoOcorrenciaTipo();
            $aiot = $tb->getPorChave("C");
            if ($aiot) {
                $tb = new TbFuncionario();
                $funcionario = $tb->pegaLogado();
                if ($funcionario) {
                    $tb = new TbAutoInfracaoOcorrencia();
                    $aio = $tb->createRow();
                    $aio->setFromArray(array("id_auto_infracao_ocorrencia_tipo" => $aiot->getId(),
                                             "id_funcionario" => $funcionario->getId(),
                                             "id_auto_infracao" => $this->getId()));
                    $aio->save();
                }
            }
        }
        return $flag;
    }
    
	public function toString() {
		$txt = array();
        $st = $this->findParentRow("TbServicoTipo");
        if ($st) {
            $txt[] = $st->toString();
        }
        if ($this->codigo) {
            $txt[] = $this->mostrar_codigo();
        }
        if (count($txt)) {
            return implode(" - ", $txt);
        }
        return "";
	}
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->codigo)) {
			$msgs[] = "CAMPO CÓDIGO OBRIGATÓRIO!";
		}
		if (!trim($this->id_auto_infracao_status)) {
			$msgs[] = "CAMPO STATUS DO AUTO DE INFRAÇÃO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" alfa = '{$this->alfa}' and codigo = '{$this->codigo}' and id_auto_infracao <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "CÓDIGO DE AUTO DE INFRAÇÃO JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $ais = $this->findParentRow("TbAutoInfracaoStatus");
        if ($ais && !$ais->disponivel()) {
            $msgs[] = "Somente Permitido exclusão de Auto de Infração Disponível!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function delete() {
        $rs = $this->findDependentRowset("TbAutoInfracaoOcorrencia");
        if ($rs && count($rs)) {
            foreach ($rs as $obj) {
                $obj->delete();
            }
        }
        parent::delete();
    }
    
    public function mostrar_codigo() {
        return $this->alfa . "" . Escola_Util::zero($this->codigo, 8);
    }
    
    public function disponivel() {
        $ais = $this->findParentRow("TbAutoInfracaoStatus");
        if ($ais) {
            return $ais->disponivel();
        }
        return false;
    }
    
    public function entregue() {
        $ais = $this->findParentRow("TbAutoInfracaoStatus");
        if ($ais) {
            return $ais->entregue();
        }
        return false;
    }
    
    public function devolvido() {
        $ais = $this->findParentRow("TbAutoInfracaoStatus");
        if ($ais) {
            return $ais->devolvido();
        }
        return false;
    }
    
    public function setAgente($agente) {
        if ($this->disponivel()) {
            $tb = new TbAutoInfracaoStatus();
            $ais = $tb->getPorChave("EN");
            if ($ais) {
                $this->id_auto_infracao_status = $ais->getid();
                $this->id_agente = $agente->getId();
                $this->save();
                $this->refresh();
                if ($this->id_agente == $agente->getId()) {
                    $tb = new TbAutoInfracaoOcorrenciaTipo();
                    $aiot = $tb->getPorChave("EN");
                    if ($aiot) {
                        $tb = new TbFuncionario();
                        $funcionario = $tb->pegaLogado();
                        if ($funcionario) {
                            $tb = new TbAutoInfracaoOcorrencia();
                            $aio = $tb->createRow();
                            $aio->setFromArray(array("id_auto_infracao_ocorrencia_tipo" => $aiot->getId(),
                                                     "id_funcionario" => $funcionario->getId(),
                                                     "id_auto_infracao" => $this->getId(),
                                                     "observacoes" => "Auto de Infração Atribuído ao Agente: {$agente->toString()}."));
                            $aio->save();
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }
    
    public function listar_ocorrencias() {
        $tb = new TbAutoInfracaoOcorrencia();
        return $tb->listar(array("id_auto_infracao" => $this->getId()));
    }
    
    public function pegaServicoSolicitacao() {
        $ain = $this->pegaNotificacao();
        if ($ain) {
            return $ain->pegaServicoSolicitacao();
        }
        return false;
    }
        
    public function devolver($dados) {
        $id_auto_infracao_notificacao = null;
        if ($this->entregue()) {
            $tb = new TbAutoInfracaoStatus();
            $ais = $tb->getPorChave("DV");
            if ($ais) {
                $db = Zend_Registry::get("db");
                $db->beginTransaction();
                try {
                    $tb = new TbAutoInfracaoNotificacao();
                    $ain = $tb->createRow();
                    $ain->setFromArray($dados);
                    $errors = $ain->getErrors();
                    if (!$errors) {
                        $ain->save();
                        $id_auto_infracao_notificacao = $ain->getId();
                        if ($id_auto_infracao_notificacao) {
                            if (isset($dados["id_infracao"]) && is_array($dados["id_infracao"]) && count($dados["id_infracao"])) {
                                foreach ($dados["id_infracao"] as $id_infracao) {
                                    $infracao = TbInfracao::pegaPorId($id_infracao);
                                    if ($infracao) {
                                        $ain->addInfracao($infracao);
                                    }
                                }
                            }
                            $tb = new TbNotificacaoMedicao();
                            $medicao = $tb->createRow();
                            $medicao->setFromArray($dados["medicao"]);
                            $medicao->id_auto_infracao_notificacao = $id_auto_infracao_notificacao;
                            $errors = $medicao->getErrors();
                            if (!$errors) {
                                $medicao->save();
                            }
                            
                            $ss = $ain->pegaServicoSolicitacao();
                            if (!$ss) {
                                $tb_s = new TbServico();
                                $s = $tb_s->getPorCodigo("AUI");
                                if ($s) {
                                    $tb_stg = new TbServicoTransporteGrupo();
                                    $stgs = $tb_stg->listar(array("id_servico" => $s->getId()));
                                    if ($stgs && count($stgs)) {
                                        $stg = $stgs->current();
                                        $tb_ss = new TbServicoSolicitacao();
                                        $ss = $tb_ss->createRow();
                                        $dados_ss = array();
                                        $dados_ss["valor"] = $ain->pegaValorTotal();
                                        $dados_ss["id_servico_transporte_grupo"] = $stg->getId();
                                        $dados_ss["tipo"] = "NO";
                                        $dados_ss["chave"] = $ain->getId();
                                        $ss->setFromArray($dados_ss);
                                        $ss->atualiza_datas();
                                        $erros = $ss->getErrors();
                                        if (!$erros) {
                                            $ss->save();
                                        }
                                    }
                                }            
                            }
                            
                        }
                    }
                    $this->id_auto_infracao_status = $ais->getid();
                    if (isset($dados["id_auto_infracao_devolucao_status"]) && $dados["id_auto_infracao_devolucao_status"]) {
                        $this->id_auto_infracao_devolucao_status = $dados["id_auto_infracao_devolucao_status"];
                    }
                    $this->save();
                    $this->refresh();
                    if ($this->devolvido()) {
                        $tb = new TbAutoInfracaoOcorrenciaTipo();
                        $aiot = $tb->getPorChave("DV");
                        if ($aiot) {
                            $tb = new TbFuncionario();
                            $funcionario = $tb->pegaLogado();
                            if ($funcionario) {
                                $tb = new TbAutoInfracaoOcorrencia();
                                $aio = $tb->createRow();
                                $dados = array_merge(array("id_auto_infracao_ocorrencia_tipo" => $aiot->getId(),
                                                           "id_funcionario" => $funcionario->getId(),
                                                           "id_auto_infracao" => $this->getId(),
                                                           "id_auto_infracao_notificacao" => $id_auto_infracao_notificacao), $dados);
                                $aio->setFromArray($dados);
                                $aio->save();
                            }
                        }
                        $db->commit();
                        return true;
                    }
                    $db->rollBack();
                } catch (Exception $e) {
                    $db->rollBack();
                    die($e->getMessage());
                }
            }
        }
        return false;
    }
    
    public function cancelar_entrega() {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {
            $tb = new TbAutoInfracaoStatus();
            $ais = $tb->getPorChave("DI");
            if ($ais) {
                $this->id_auto_infracao_status = $ais->getId();
                $this->id_auto_infracao_devolucao_status = null;
                $this->id_agente = null;
                $this->save();
                $this->refresh();
                if ($this->disponivel()) {
                    $tb = new TbAutoInfracaoOcorrenciaTipo();
                    $aiot = $tb->getPorChave("CE");
                    if ($aiot) {
                        $tb = new TbFuncionario();
                        $funcionario = $tb->pegaLogado();
                        if ($funcionario) {
                            $tb = new TbAutoInfracaoOcorrencia();
                            $aio = $tb->createRow();
                            $dados = array("id_auto_infracao_ocorrencia_tipo" => $aiot->getId(),
                                           "id_funcionario" => $funcionario->getId(),
                                           "id_auto_infracao" => $this->getId(),
                                           "observacoes" => "Cancelamento de Entrega de Auto de Infração.");
                            $aio->setFromArray($dados);
                            $aio->save();
                        }
                    }
                }
            }
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
    
    public function view(Zend_View_Abstract $view) {
        $txt_valor_total = $txt_status_pagamento = "--";
        $ss = $this->pegaServicoSolicitacao();
        if ($ss) {
            if ($ss->aguardando_pagamento()) {
                $emitir_boleto = true;
            }
            $sss = $ss->findParentRow("TbServicoSolicitacaoStatus");
            if ($sss) {
                $txt_status_pagamento = $sss->toString();
            }
            $valor_total = $ss->pega_valor();
            if ($valor_total) {
                $txt_valor_total = $valor_total->toString();
            }
        }
        ob_start();
?>
            <div class="well">
                <div class="page-header">
                    <h4>Cadastro de Auto de Infração</h4>
                </div>
                <dl class="dl-horizontal">
                    <dt>ID:</dt>
                    <dd><?php echo $this->getId(); ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Tipo:</dt>
                    <dd><?php echo $this->findParentRow("TbServicoTipo")->toString(); ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Código:</dt>
                    <dd><?php echo $this->mostrar_codigo(); ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Status:</dt>
                    <dd><?php echo $this->findParentRow("TbAutoInfracaoStatus")->toString(); ?></dd>
                </dl>
<?php
$agente = $this->findParentRow("TbAgente");
?>
                <dl class="dl-horizontal">
                    <dt>Agente:</dt>
                    <dd><?php echo ($agente)?$agente->toString():"--"; ?></dd>
                </dl>
<?php
$aids = $this->findParentRow("TbAutoInfracaoDevolucaoStatus");
?>
                <dl class="dl-horizontal">
                    <dt>Status da Devolução:</dt>
                    <dd><?php echo ($aids)?$aids->toString():"--"; ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Valor Total:</dt>
                    <dd><?php echo $txt_valor_total; ?></dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Status Pagamento:</dt>
                    <dd><?php echo $txt_status_pagamento; ?></dd>
                </dl>
            </div>
<?php 
$ocorrencias = $this->listar_ocorrencias();
if ($ocorrencias) { ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th colspan="7">Ocorrências</th>
                </tr>
                <tr>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Funcionário</th>
                    <th>Observações</th>
                    <th>Status da Devolução</th>
                    <th>Notificação</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($ocorrencias as $ocorrencia) { 
    $aids = $ocorrencia->findParentRow("TbAutoInfracaoDevolucaoStatus");
    $notificacao = $ocorrencia->findParentRow("TbAutoInfracaoNotificacao");
?>
                <tr>
                    <td><?php echo Escola_Util::formatData($ocorrencia->data_ocorrencia); ?></td>
                    <td><?php echo $ocorrencia->hora_ocorrencia; ?></td>
                    <td><?php echo $ocorrencia->findParentRow("TbAutoInfracaoOcorrenciaTipo")->toString(); ?></td>
                    <td><?php echo $ocorrencia->findParentRow("TbFuncionario")->toString(); ?></td>
                    <td><?php echo $ocorrencia->observacoes; ?></td>
                    <td><?php echo ($aids)?$aids->toString():"--"; ?></td>
                    <td>
<?php 
if ($notificacao) { 
?>
                        <div class="btn-group">
                            <a href="<?php echo $view->url(array("action" => "viewnotificacao", "id" => $notificacao->getId())); ?>" class="btn" id="<?php echo $notificacao->getId(); ?>">Visualizar Notificação</a>
                        </div>
<?php } ?>
                    </td>
                </tr>
<?php } ?>
            </tbody>
        </table>
<?php } 
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function pegaNotificacao() {
        if ($this->getId()) {
            //pega status de devolução = OK
            $tb_aids = new TbAutoInfracaoDevolucaoStatus();
            $aids = $tb_aids->getPorChave("O"); // OK
            if ($aids) {
                //lista ocorrências devolvidas com o status OK.
                $tb_aio = new TbAutoInfracaoOcorrencia();
                $ocorrencias = $tb_aio->listar(array("id_auto_infracao" => $this->getId(), "id_auto_infracao_devolucao_status" => $aids->getId()));
                if ($ocorrencias && count($ocorrencias)) {
                    foreach ($ocorrencias as $ocorrencia) {
                        $notificacao = $ocorrencia->findParentRow("TbAutoInfracaoNotificacao");
                        if ($notificacao) {
                            return $notificacao;
                        }
                    }
                }
            }
        }
        return false;
    }
}