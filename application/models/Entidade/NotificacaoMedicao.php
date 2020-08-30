<?php
class NotificacaoMedicao extends Escola_Entidade {
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_auto_infracao_notificacao)) {
            $msgs[] = "CAMPO ID NOTIFICAÇÃO NÃO INFORMADO!";
        }
		$flag = false;
        $fields = array("equipamento", "marca", "numero_serie", "numero_teste", "medicao_realizada", "limite_regulamentar", "medicao_considerada", "excesso_verificado");
        foreach ($fields as $field) {
            if (trim($this->$field)) {
                $flag = true;
                break;
            }
        }
        if (!$flag) {
            $msgs[] = "PELO MENOS UM CAMPO DEVE SER PREENCHIDO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }

    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbAutoInfracaoNotificacao");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function render(Zend_View_Abstract $view) {
        ob_start();
?>
<fieldset>
    <legend>Medições</legend>
    <div class="control-group">
        <label for="equipamento" class="control-label">Equipamento Utilizado:</label>
        <div class="controls">
            <input type="text" name="medicao[equipamento]" id="equipamento" class="span5" maxlength="100" />
        </div>
    </div>
    <div class="control-group">
        <label for="marca" class="control-label">Marca / Modelo:</label>
        <div class="controls">
            <input type="text" name="medicao[marca]" id="marca" class="span5" maxlength="100" />
        </div>
    </div>
    <div class="control-group">
        <label for="numero_serie" class="control-label">Número de Série:</label>
        <div class="controls">
            <input type="text" name="medicao[numero_serie]" id="numero_serie" class="span4" maxlength="100" />
        </div>
    </div>
    <div class="control-group">
        <label for="numero_teste" class="control-label">Número de Teste:</label>
        <div class="controls">
            <input type="text" name="medicao[numero_teste]" id="numero_teste" class="span4" maxlength="100" />
        </div>
    </div>
    <div class="control-group">
        <label for="medicao_realizada" class="control-label">Medicação Realizada:</label>
        <div class="controls">
            <input type="text" name="medicao[medicao_realizada]" id="medicao_realizada" class="span4" maxlength="100" />
        </div>
    </div>
    <div class="control-group">
        <label for="limite_regulamentar" class="control-label">Medicação Regulamentar:</label>
        <div class="controls">
            <input type="text" name="medicao[limite_regulamentar]" id="limite_regulamentar" class="span4" maxlength="100" />
        </div>
    </div>
    <div class="control-group">
        <label for="medicao_considerada" class="control-label">Medicação Considerada:</label>
        <div class="controls">
            <input type="text" name="medicao[medicao_considerada]" id="medicao_considerada" class="span4" maxlength="100" />
        </div>
    </div>
    <div class="control-group">
        <label for="excesso_verificado" class="control-label">Excesso Verificado:</label>
        <div class="controls">
            <input type="text" name="medicao[excesso_verificado]" id="excesso_verificado" class="span4" maxlength="100" />
        </div>
    </div>
</fieldset>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function view(Zend_View_Abstract $view) {
        ob_start();
?>
<div class="page-header">
    <h4>Medições</h4>
</div>
<dl class="dl-horizontal"><dt>Equipamento Utilizado:</dt><dd><?php echo $this->equipamento; ?></dd></dl>
<dl class="dl-horizontal"><dt>Marca / Modelo:</dt><dd><?php echo $this->marca; ?></dd></dl>
<dl class="dl-horizontal"><dt>Número de Série:</dt><dd><?php echo $this->numero_serie; ?></dd></dl>
<dl class="dl-horizontal"><dt>Número de Teste:</dt><dd><?php echo $this->numero_teste; ?></dd></dl>
<dl class="dl-horizontal"><dt>Medição Realizada:</dt><dd><?php echo $this->medicao_realizada; ?></dd></dl>
<dl class="dl-horizontal"><dt>Medição regulamentar:</dt><dd><?php echo $this->limite_regulamentar; ?></dd></dl>
<dl class="dl-horizontal"><dt>Medição Considerada:</dt><dd><?php echo $this->medicao_considerada; ?></dd></dl>
<dl class="dl-horizontal"><dt>Excesso Verificado:</dt><dd><?php echo $this->excesso_verificado; ?></dd></dl>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;        
    }
}