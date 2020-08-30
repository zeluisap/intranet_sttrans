<?php
class Arquivo extends Escola_Entidade {
    protected $arquivo = false;
    
    public function init() {
        if (!$this->data_upload) {
            $this->data_upload = date("Y-m-d");
            $this->hora_upload = date("H:i:s");
        }
        if (!$this->id_arquivo_tipo) {
            $tb = new TbArquivoTipo();
            $at = $tb->getPorMimeType("application/octet-stream");
            if ($at) {
                $this->id_arquivo_tipo = $at->getId();
            }
        }
        parent::init();
    }
    
    public function toString() {
        return $this->legenda;
    }
    
    public function eImagem() {
        $at = $this->findParentRow("TbArquivoTipo");
        if ($at) {
            return $at->eImagem();
        }
    }
    
    public function eJpeg() {
        $at = $this->findParentRow("TbArquivoTipo");
        if ($at) {
            return $at->eJpeg();
        }
    }
    
    public function ePng() {
        $at = $this->findParentRow("TbArquivoTipo");
        if ($at) {
            return $at->ePng();
        }
    }
    
    public function eTexto() {
        $at = $this->findParentRow("TbArquivoTipo");
        if ($at) {
            return $at->eTexto();
        }
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["arquivo"]["size"]) && $dados["arquivo"]["size"]) {
            $this->arquivo = $dados["arquivo"];
            $this->tamanho = $this->arquivo["size"];
            $tb = new TbArquivoTipo();
            $at = $tb->getPorMimeType($this->arquivo["type"]);
            if ($at) {
                $this->id_arquivo_tipo = $at->getId();
            } else {
                $at = $tb->getPorMimeType("application/octet-stream");
                if ($at) {
                    $this->id_arquivo_tipo = $at->getId();
                }                
            }
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
        $msgs = array();
        if (!$this->tamanho) {
            $msgs[] = "ARQUIVO SEM TAMANHO ESPECIFICADO!";
        }
        if (!$this->existe()) {
            $msgs[] = "NENHUM ARQUIVO ESPECIFICADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function pegaNomeCompleto() {
        return ROOT_DIR . "/application/file/" . $this->nome_fisico;
    }
    
    public function save() {
        if ($this->arquivo && $this->arquivo["size"]) {
            $tb = new TbArquivoTipo();
            $at = $tb->getPorMimeType($this->arquivo["type"]);
            if ($at) {
                $this->id_arquivo_tipo = $at->getId();
            }
            if ($this->arquivo && $this->arquivo["size"]) {
                $this->tamanho = $this->arquivo["size"];
            }
        }

        $id = parent::save();
        if ($id && $this->arquivo && $this->arquivo["size"]) {

            if ($this->eImagem()) {
                $max_width = 1000;
                $img = WideImage::load($this->arquivo["tmp_name"]);
                if ($img->getWidth() > $max_width) {
                    $img = $img->resize($max_width);
                    // está ocorrendo uma falha ao tentar redimensionar uma imagem muito grande
                    // $img->saveToFile($this->arquivo["tmp_name"]);
                }
            }

            $extensao = Escola_Util::pegaExtensao($this->arquivo["name"]);
            if (!$extensao) {
                $extensao = $at->extensao;
            }

            $this->nome_fisico = "arquivo" . $id  . "." . Escola_Util::minuscula($extensao);

            $flag = copy($this->arquivo["tmp_name"], $this->pegaNomeCompleto());
            if ($flag) {
                $this->tamanho = filesize($this->pegaNomeCompleto());
            } else {
                $this->tamanho = 0;
            }

            $this->arquivo = false;
            $this->save();
        }
        return $id;
    }
    
    public function existe() {
        if ($this->getId() && $this->nome_fisico) {
            if (file_exists($this->pegaNomeCompleto())) {
                return true;
            }            
        }
        if (isset($this->arquivo["tmp_name"]) && $this->arquivo["tmp_name"] && file_exists($this->arquivo["tmp_name"])) {
            return true;
        }
        return false;
    }
    
    public function resize($w, $h = 0) {
        if ($this->existe()) {
            if (!$h) {
                $h = $w;
            }        
            $img = $this->getWideImage();
            $img->resize($w, $h);
            return $img;
        }
        return false;
    }
    
    public function getWideImage() {
        return WideImage::load($this->pegaNomeCompleto());
    }
    
    public function miniatura($dados = array()) {
        $width = 90;
        $height = 0;
        $align = "";
        $link = false;
        $mode = "inside";
        $class = "";
        if (isset($dados["width"]) && $dados["width"]) {
            $width = $dados["width"];
        }
        if (isset($dados["height"]) && $dados["height"]) {
            $height = $dados["height"];
        }
        if (isset($dados["align"]) && $dados["align"]) {
            $align = " align='{$dados["align"]}' ";
        }
        if (isset($dados["link"]) && $dados["link"]) {
            $link = $dados["link"];
        }
        if (isset($dados["mode"]) && $dados["mode"]) {
            $mode = $dados["mode"];
        }
        if ($this->existe()) {
            $class = "";
            if ($this->eImagem()) {
                $url = Escola_Util::url(array("controller" => "arquivo", "action" => "show", "id" => $this->getId(), "width" => $width, "height" => $height, "mode" => $mode));
                //$class = ' class="show_imagem" ';
            } else {
                $url = Escola_Util::url(array("controller" => "arquivotipo", "action" => "show", "id" => $this->id_arquivo_tipo, "width" => $width, "height" => $height, "mode" => $mode));
            }
            if (isset($dados["class"]) && $dados["class"]) {
                $class = " class='{$dados["class"]}' ";
            }
            //$url_src = $this->getLink();
            $url_src = Escola_Util::url(array("controller" => "arquivo", "action" => "view", "id" => $this->getId()));
            ob_start();
?>
        <div class="imagem">
<?php if ($link) { ?>
            <a href="<?php echo $url_src; ?>" <?php echo $class; ?> title="<?php echo $this->legenda; ?>" <?php if ($this->eImagem()) { ?>class="fancybox"<?php } ?> target="__blank">
<?php } ?>
                <img src="<?php echo $url; ?>" alt="<?php echo $this->legenda; ?>" <?php echo $align; ?> <?php echo $class; ?> />
<?php if ($link) { ?>
            </a>
<?php } ?>
        </div>
<?php
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
        return "";
    }
    
    public function getLink() {
        if ($this->eImagem()) {
            return Escola_Util::url(array("controller" => "arquivo", "action" => "show", "id" => $this->getId()));
        }
        return $this->pegaLinkFisico();
    }
    
    public function pegaLinkFisico() {
    	return Escola_Util::getBaseUrl() . "/../application/file/" . $this->nome_fisico;
    }
    
    public function delete() {
        $tb = new TbPessoaRef();
        $prs = $tb->listar(array("tipo" => "F", "chave" => $this->getId()));
        if ($prs && $prs->count()) {
            foreach ($prs as $pr) {
                $pr->delete();
            }
        }        
        $tb = new TbArquivoRef();
        $ars = $tb->listar(array("id_arquivo" => $this->getId()));
        if ($ars) {
            foreach ($ars as $ar) {
                $ar->delete();
            }
        }
        parent::delete();
    }

    public function mostrarTamanho() {
        if ($this->tamanho) {
            if ($this->tamanho < 1000) {
                return $this->tamanho . " Bytes";
            } elseif ($this->tamanho < 1000000) {
                return round($this->tamanho / 1000 , 2) . " KBytes";
            } elseif ($this->tamanho < 1000000000) {
                return round($this->tamanho / 1000000 , 2) . " MBytes";
            }
        }
        return "--";
    }    
}