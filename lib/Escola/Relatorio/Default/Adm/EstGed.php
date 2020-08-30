<?php
class Escola_Relatorio_Default_Adm_EstGed extends Escola_Relatorio_Default
{

	public function pegaEstatistica()
	{
		$this->dados = TbDocumento::pegaEstatistica();
	}

	public function toPDF()
	{
		$pdf_class_name = get_class($this) . "_Pdf";
		$zla = Zend_Loader_Autoloader::getInstance();
		if ($zla->autoload($pdf_class_name)) {
			$obj = new $pdf_class_name;
			$filter = new Zend_Filter_CharConverter();
			$filename = $filter->filter($this->relatorio->descricao);
			$filter = new Zend_Filter_StringToLower();
			$filename = $filter->filter($filename);
			$filename = str_replace(" ", "_", $filename);
			$obj->set_dados(array("filename" => "relatorio_" . $filename));
			$obj->set_relatorio($this->relatorio);
			$obj->imprimir();
		}
	}

	public function toXLS()
	{ }

	public function toHTML()
	{
		$this->pegaEstatistica();
		ob_start();
		?>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Tipo de Documentos</th>
					<th>Quantidade de Documentos Importados</th>
				</tr>
			</thead>
			<?php if (!$this->dados) { ?>
				<tr>
					<td colspan="2">NENHUM REGISTRO LOCALIZADO!
				</tr>
			<?php } else { ?>
				<?php foreach ($this->dados as $obj) { ?>
					<tr>
						<td><?php echo $obj->descricao; ?></td>
						<td><?php echo Escola_Util::number_format($obj->total); ?></td>
					</tr>
			<?php }
					} ?>
		</table>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
