<?php
class Escola_Gtk_MeuProgresso extends GtkProgressBar {
	private $valor_total;
	private $progresso = 0;
	
	public function set_valor_total($valor_total) {
		$this->valor_total = $valor_total;
	}
	
	public function set_progresso($progresso = 0) {
		$this->progresso = $progresso;
		$this->set_fraction(0);
	}
	
	public function progresso($txt = "CALCULANDO") {
		$this->progresso++;
		$fraction = ($this->progresso * 100 / $this->valor_total);
		$this->set_fraction(($fraction)/100);
		$this->set_text($txt . " - " . (int)$fraction . "%");
		$this->atualizaEventos();
	}
	
	public function atualizaEventos() {
		while (Gtk::events_pending()) {
			Gtk::main_iteration();
		}
	}
	
	public function set_text($texto) {
		parent::set_text($texto);
		$this->atualizaEventos();
	}
}