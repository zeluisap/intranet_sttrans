<?php
class Escola_Navegacao {
    
    protected $items = array();
    protected static $navegacao = false;
    
    public function __construct() {
        $this->add("Início", Escola_Util::url(array("controller" => "portal", "action" => "index")));
    }
    
    public function getInstance() {
        if (self::$navegacao !== false)
            return self::$navegacao;
        $class = __CLASS__;
        self::$navegacao = new $class();
        return self::$navegacao;
    }
    
    public function pegaItems() {
        return $this->items;
    }
    
    public function add($titulo, $url) {
        $item = new stdClass();
        $item->titulo = $titulo;
        $item->url = $url;
        $this->items[] = $item;
    }
    
    public function render() {
        $contador = 0;
        if (count($this->items) > 1) {
            ob_start();
?>
<ul class="breadcrumb">
<?php foreach ($this->items as $item) { ?>
    <li><?php if ($contador) { ?><span class="divider">/</span><?php } ?><a href="<?php echo $item->url; ?>"><?php echo $item->titulo; ?></a></li>
<?php $contador++; } ?>
</ul>
<?php
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
        return "";
    }
}