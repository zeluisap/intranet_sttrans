<?php
class TbArquivoTipo extends Escola_Tabela {
	protected $_name = "arquivo_tipo";
	protected $_rowClass = "ArquivoTipo";
	protected $_dependentTables = array("TbArquivo");
	
	public function getPorExtensao($extensao) {
		$uss = $this->fetchAll(" extensao = '{$extensao}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function getPorMimeType($mime_type) {
		$uss = $this->fetchAll(" mime_type = '{$mime_type}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function listar() {
		$sql = $this->select();
		$sql->order("descricao");
		$rg = $this->fetchAll($sql);
		if (count($rg)) {
			return $rg;
		}
		return false;
	}

	public function listarPorPagina($dados = array()) {
		$select = $this->select();
		$select->order("descricao");
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage(50);
		return $paginator;
	}		
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
            $dados = array();
            $dados[] = array('extensao' => 'txt', 'mime_type' => 'text/plain', 'descricao' => 'text/plain');
            $dados[] = array('extensao' => 'pdf', 'mime_type' => 'application/pdf', 'descricao' => 'application/pdf');
            $dados[] = array('extensao' => 'doc', 'mime_type' => 'application/msword', 'descricao' => 'application/msword');
            $dados[] = array('extensao' => 'doc', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'descricao' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $dados[] = array('extensao' => 'doc', 'mime_type' => 'application/rtf', 'descricao' => 'application/rtf');
            $dados[] = array('extensao' => 'doc', 'mime_type' => 'application/x-rtf', 'descricao' => 'application/x-rtf');
            $dados[] = array('extensao' => 'doc', 'mime_type' => 'text/richtext', 'descricao' => 'text/richtext');
            $dados[] = array('extensao' => 'css', 'mime_type' => 'text/css', 'descricao' => 'text/css');
            $dados[] = array('extensao' => 'fh', 'mime_type' => 'image/x-freehand', 'descricao' => 'image/x-freehand');
            $dados[] = array('extensao' => 'htm', 'mime_type' => 'text/html', 'descricao' => 'text/html');
            $dados[] = array('extensao' => 'ini', 'mime_type' => 'zz-application/zz-winassoc-ini', 'descricao' => 'zz-application/zz-winassoc-ini');
            $dados[] = array('extensao' => 'inf', 'mime_type' => 'text/inf', 'descricao' => 'text/inf');
            $dados[] = array('extensao' => 'mov', 'mime_type' => 'video/quicktime', 'descricao' => 'video/quicktime');
            $dados[] = array('extensao' => 'mp3', 'mime_type' => 'audio/x-mpeg3', 'descricao' => 'audio/x-mpeg3');
            $dados[] = array('extensao' => 'mp4', 'mime_type' => 'video/mp4v-es', 'descricao' => 'video/mp4v-es');
            $dados[] = array('extensao' => 'mpg', 'mime_type' => 'video/x-mpeg', 'descricao' => 'video/x-mpeg');
            $dados[] = array('extensao' => 'ogg', 'mime_type' => 'audio/x-ogg', 'descricao' => 'audio/x-ogg');
            $dados[] = array('extensao' => 'ppt', 'mime_type' => 'application/mspowerpoint', 'descricao' => 'application/mspowerpoint');
            $dados[] = array('extensao' => 'ppt', 'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'descricao' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
            $dados[] = array('extensao' => 'ppt', 'mime_type' => 'application/vnd.ms-powerpoint', 'descricao' => 'application/vnd.ms-powerpoint');
            $dados[] = array('extensao' => 'ppt', 'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'descricao' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow');
            $dados[] = array('extensao' => 'swf', 'mime_type' => 'application/x-shockwave-flash', 'descricao' => 'application/x-shockwave-flash');
            $dados[] = array('extensao' => 'tiff', 'mime_type' => 'image/tiff', 'descricao' => 'image/tiff');
            $dados[] = array('extensao' => 'wav', 'mime_type' => 'audio/wav', 'descricao' => 'audio/wav');
            $dados[] = array('extensao' => 'wma', 'mime_type' => 'audio/x-ms-wma', 'descricao' => 'audio/x-ms-wma');
            $dados[] = array('extensao' => 'wmv', 'mime_type' => 'video/x-ms-wmv', 'descricao' => 'video/x-ms-wmv');
            $dados[] = array('extensao' => 'xls', 'mime_type' => 'application/msexcel', 'descricao' => 'application/msexcel');
            $dados[] = array('extensao' => 'xls', 'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'descricao' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $dados[] = array('extensao' => 'xml', 'mime_type' => 'text/xml', 'descricao' => 'text/xml');
            $dados[] = array('extensao' => 'zip', 'mime_type' => 'application/x-zip-compressed', 'descricao' => 'application/x-zip-compressed');
            $dados[] = array('extensao' => 'zip', 'mime_type' => 'application/zip', 'descricao' => 'application/zip');
            $dados[] = array('extensao' => 'rar', 'mime_type' => 'application/x-rar-compressed', 'descricao' => 'application/x-rar-compressed');
            $dados[] = array('extensao' => 'file', 'mime_type' => 'application/octet-stream', 'descricao' => 'application/octet-stream');
            $dados[] = array('extensao' => '7zip', 'mime_type' => 'application/x-7z-compressed', 'descricao' => 'application/x-7z-compressed');
            $dados[] = array('extensao' => 'ace', 'mime_type' => 'application/x-ace', 'descricao' => 'application/x-ace');
            $dados[] = array('extensao' => 'ai', 'mime_type' => 'application/illustrator', 'descricao' => 'application/illustrator');
            $dados[] = array('extensao' => 'aiff', 'mime_type' => 'audio/aiff', 'descricao' => 'audio/aiff');
            $dados[] = array('extensao' => 'asp', 'mime_type' => 'text/asp', 'descricao' => 'text/asp');
            $dados[] = array('extensao' => 'avi', 'mime_type' => 'video/msvideo', 'descricao' => 'video/msvideo');
            $dados[] = array('extensao' => 'bat', 'mime_type' => 'application/bat', 'descricao' => 'application/bat');
            $dados[] = array('extensao' => 'bin', 'mime_type' => 'application/octet-stream', 'descricao' => 'application/octet-stream');
            $dados[] = array('extensao' => 'dll', 'mime_type' => 'application/x-msdownload', 'descricao' => 'application/x-msdownload');
            $dados[] = array('extensao' => 'fla', 'mime_type' => 'application/x-shockwave-flash', 'descricao' => 'application/x-shockwave-flash');
            $dados[] = array('extensao' => 'hqx', 'mime_type' => 'application/binhex', 'descricao' => 'application/binhex');
            $dados[] = array('extensao' => 'midi', 'mime_type' => 'audio/mid', 'descricao' => 'audio/mid');
            $dados[] = array('extensao' => 'php', 'mime_type' => 'application/x-httpd-php', 'descricao' => 'application/x-httpd-php');
            $dados[] = array('extensao' => 'sit', 'mime_type' => 'application/stuffit', 'descricao' => 'application/stuffit');
            $dados[] = array('extensao' => 'sitx', 'mime_type' => 'application/x-sit', 'descricao' => 'application/x-sit');
            $dados[] = array('extensao' => 'jpg', 'mime_type' => 'image/jpeg', 'descricao' => 'image/jpeg');
            $dados[] = array('extensao' => 'gif', 'mime_type' => 'image/gif', 'descricao' => 'image/gif');
            $dados[] = array('extensao' => 'jpg', 'mime_type' => 'image/pjpeg', 'descricao' => 'image/pjpeg');
            $dados[] = array('extensao' => 'png', 'mime_type' => 'image/png', 'descricao' => 'image/png');
            $dados[] = array('extensao' => 'bmp', 'mime_type' => 'image/x-bitmap', 'descricao' => 'image/x-bitmap');
            $dados[] = array('extensao' => 'psd', 'mime_type' => 'image/photoshop', 'descricao' => 'image/photoshop');
            $dados[] = array('extensao' => 'psp', 'mime_type' => 'image/bmp', 'descricao' => 'image/bmp');
            foreach ($dados as $dado) {
				$item = $this->createRow();
				$item->setFromArray($dado);
				$item->save();
			}
        }
	}
}