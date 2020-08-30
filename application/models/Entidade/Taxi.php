<?php
class Taxi extends Escola_Entidade implements Escola_ITransporte {
    
    private $_transporte = false;
    
    public function init() {
        $this->get_transporte();
    }
    
    public function set_transporte($transporte) {
        $this->_transporte = $transporte;
    }
    
    public function get_transporte() {
        if (!$this->_transporte) {
            $transporte = $this->findParentRow("TbTransporte");
            if ($transporte) {
                $this->set_transporte($transporte);
            } else {
                $tb = new TbTransporte();
                $this->set_transporte($tb->createRow());
            }
        }
        return $this->_transporte;
    }
    
    public function setFromArray(array $data) {
        $this->_transporte->setFromArray($data);
        parent::setFromArray($data);
    }
    
    public function save($flag = false) {
        $this->_transporte->save();
        if ($this->_transporte->getId()) {
            $this->id_transporte = $this->_transporte->getId();
        }
        parent::save($flag);
    }
    
    public function getErrors() {
		$msgs = array();
        $rg = $this->getTable()->fetchAll(" id_transporte = '{$this->id_transporte}' and id_taxi <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TRANSPORTE JÁ CADASTRADO!";
        }
        $msg_transporte = $this->_transporte->getErrors();
        if ($msg_transporte) {
            $msgs = array_merge($msgs, $msg_transporte);
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function render(Zend_View_Interface $view) {
        ob_start();
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function view() {
        ob_start();
        $transporte = $this->findParentRow("TbTransporte");
        echo $transporte->view();
        /*
?>
            <div class="well">
                <div class="page-header">
                    <h4><?php echo $transporte->findParentRow("TbTransporteGrupo")->toString(); ?></h4>
                </div>
            </div>
<?php
         */
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function toString() {
        return "";
    }
    
    public function atualiza_solicitacao_servicos() {
        $hoje = new Zend_Date();
        $transporte = $this->findParentRow("TbTransporte");
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