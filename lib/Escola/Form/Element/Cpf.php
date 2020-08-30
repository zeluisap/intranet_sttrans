<?php 
class Escola_Form_Element_Cpf extends Zend_Form_Element_Text {
    public function render(Zend_View_Interface $view = null) {
        ob_start();
?>
        <input type="text" name="<?php echo $this->getName(); ?>" id="<?php echo $this->getId(); ?>" class="cpf" size="15" value="<?php echo $this->getValue(); ?>" />
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}