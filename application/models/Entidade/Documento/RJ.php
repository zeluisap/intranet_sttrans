<?php
class Documento_RJ extends Documento {
    
    protected $_ain = false;
    
    public function init() {
        parent::init();
        $this->_ain = $this->pegaAutoInfracaoNotificacao();
    }
    
    public function setFromArray(array $dados) {
        parent::setFromArray($dados);
        if (isset($dados["id_auto_infracao_notificacao"])) {
            $this->_ain = TbAutoInfracaoNotificacao::pegaPorId($dados["id_auto_infracao_notificacao"]);
        }
    }
    
    public function toForm(Zend_View_Abstract $view, $funcionario) {
        $html_parent = parent::toForm($view, $funcionario);
        $ctrl = new Escola_Form_Element_Select_Table_AutoInfracaoNotificacao("id_auto_infracao_notificacao");
        $id_ain = 0;
        if ($this->_ain && $this->_ain->getId()) {
            $id_ain = $this->_ain->getId();
        }
        $ctrl->setValue($id_ain);
        $ctrl->setLabel("Notificação de Auto de Infração: ");
        
        ob_start();
?>
<div class="well">
    <fieldset>
        <legend>NOTIFICAÇÃO DE AUTO DE INFRAÇÃO</legend>
<?php if ($this->getId()) { ?>
<?php echo $this->_ain->view($view); ?>
<?php } else { echo $ctrl->render($view); } ?>
    </fieldset>
</div>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html_parent . PHP_EOL . $html;
    }
    
    public function getErrors() {
        $errors = parent::getErrors();
        if (!$errors) {
            $errors = array();
        }
        if (!$this->_ain || !$this->_ain->getId()) {
            $errors[] = "CAMPO NOTIFICAÇÃO DE AUTO DE INFRAÇÃO OBRIGATÓRIO!";
        }
        if ($this->_ain) {
            if (!$this->_ain->pendente()) {
                $errors[] = "NOTIFICAÇÃO DE AUTO DE INFRAÇÃO NÃO DISPONÍVEL PARA RECURSO!";
            }
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }
    
    public function save() {
        $id_anterior = $this->getId();
        $id = parent::save();
        if ($this->_ain && !$id_anterior) {
            $tb = new TbRequerimentoJari();
            $rj = $tb->createRow();
            $rj->id_documento = $this->getId();
            $rj->id_auto_infracao_notificacao = $this->_ain->getId();
            $erros = $rj->getErrors();
            if ($erros) {
                throw new Exception(implode("<br>", $erros));
            }
            $rj->save();
        }
        return $id;
    }
    
    public function view(\Zend_View_Abstract $view) {
        $html_parent = parent::view($view);
        ob_start();
?>
            <div class="well">
                <fieldset>
                    <div class="page-header">
                        <h4>Notificação de Auto de Infração</h4>
                    </div>
<?php echo $this->_ain->view($view); ?>
                </fieldset>
            </div>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html_parent . PHP_EOL . $html;
    }
}