<?php
class Escola_View_Helper_Alert {
	function alert($text = "NENHUM REGISTRO LOCALIZADO.", $icon = "icon-warning-sign") {
		ob_start();
		?>
			<div style="text-align: center; margin-top: 60px; margin-bottom: 40px;">
				<i class="<?= $icon ?>" style="font-size: 80pt;"></i>
				<div style="font-size: 16pt; margin-top: 20px;"><?= $text ?></div>
			</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}