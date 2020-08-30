<?php
class ArquivoTipo extends Escola_Entidade {
    
    public function setFromArray(array $dados) {
        $maiuscula = new Zend_Filter_StringToUpper();
        if (isset($dados["descricao"])) {
            $dados["descricao"] = $maiuscula->filter($dados["descricao"]);
        }
        parent::setFromArray($dados);
    }
    
    public function toString() {
        return $this->descricao;
    }
    
    public function eJpeg() {
        return (($this->mime_type == "image/jpeg") || ($this->mime_type == "image/pjpeg"));
    }
    
    public function eGif() {
        return ($this->mime_type == "image/gif");
    }
    
    public function ePng() {
        return ($this->mime_type == "image/png");
    }
    
    public function eBitmap() {
        return (($this->mime_type == "image/bmp") || ($this->mime_type == "image/x-bitmap"));
    }
    
    public function eImagem() {
        return ($this->eJpeg() || $this->eGif() || $this->ePng() || $this->eBitmap());
    }
    
    public function eTexto() {
        return ($this->mime_type == "text/plain");
    }
    
    public function pegaNomeCompleto() {
        return ROOT_DIR . "/public/img/file/" . $this->extensao . ".gif";
    }    
    
    public function getWideImage() {
        return WideImage::load($this->pegaNomeCompleto());
    }
}