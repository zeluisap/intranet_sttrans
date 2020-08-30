<?php
class Escola_Alerta_Item {
	private $titulo;
	private $mensagem;
        
        public function get_tipo() {
            return $this->tipo;
        }
        
        public function set_tipo($tipo) {
            $this->tipo = $tipo;
        }
	
	public function set_titulo($titulo) {
		$this->titulo = $titulo;
	}
	
	public function set_mensagem($mensagem) {
		$this->mensagem = $mensagem;
	}
	
	public function pega_titulo() {
		return $this->titulo;
	}
	
	public function pega_mensagem() {
		return $this->mensagem;
	}
	
	public function render() {
		ob_start();
?>
<div class="block">
    <p class="block-heading"><i class="icon-envelope"></i><span class="break"></span><?php echo $this->titulo; ?></p>
    <div class="block-body">
        <p><?php echo $this->mensagem; ?></p>
    </div>
</div>
<?php 
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}