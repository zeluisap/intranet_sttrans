<?php
class OnibusParada extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_onibus_parada_tipo)) {
            $msgs[] = "CAMPO TIPO DE PARADA OBRIGATÓRIO!";
        }
        if (!trim($this->descricao)) {
            $msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
        }
        if (!trim($this->endereco)) {
            $msgs[] = "CAMPO ENDEREÇO OBRIGATÓRIO!";
        }
        if (!$this->possuiMapa()) {
            $msgs[] = "CAMPO LOCALIZAÇÃO DO MAPA OBRIGATÓRIO!";
        }
        $rg = $this->getTable()->fetchAll(" descricao = '{$this->descricao}' and id_onibus_parada <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "PONTO DE ÔNIBUS JÁ CADASTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbRotaParada");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function possuiMapa() {
        return (!(empty($this->latitude) || empty($this->longitude)));
    }
    
    public function mostrarMapa($dados = array()) {
        $height = "100%";
        if (isset($dados["height"]) && $dados["height"]) {
            $height = $dados["height"];
        }
        $zoom = "14";
        if (isset($dados["zoom"]) && $dados["zoom"]) {
            $zoom = $dados["zoom"];
        }
        if ($this->possuiMapa()) {
            ob_start();
?>
<div id="map" style="height: <?php echo $height; ?>"></div>
<script type="text/javascript">
    var styles = [{
        featureType: "poi",
        elementType: "all",
        stylers: [
            { visibility: "off" }
        ]
    },{
        featureType: "transit.station",
        elementType: "all",
        stylers: [
            { visibility: "off" }
        ]
    }];

    var map;
    var marker = false;
    
    var contentString = '<div id="content"><h5><?php echo $this->descricao; ?></h5><div><?php echo $this->endereco; ?></div></div>';

    var infowindow = new google.maps.InfoWindow({
        content: contentString
    });
    
    function initialize() {
        var mapOptions = {
            zoom: 15,
            streetViewControl: false,
            mapTypeControl: false,
            disableDoubleClickZoom: true,
            panControl: false,
            zoomControl: false,
            draggable: false,
            center: new google.maps.LatLng(<?php echo $this->latitude; ?>, <?php echo $this->longitude; ?>)
        };
      
        map = new google.maps.Map(document.getElementById('map'), mapOptions);

        marker = new google.maps.Marker({
            title: '<?php echo $this->descricao; ?>',
            map: map,
            position: new google.maps.LatLng(<?php echo $this->latitude; ?>, <?php echo $this->longitude; ?>)
        });
        infowindow.open(map,marker);
        map.setOptions( { "styles" : styles } );
    }

    google.maps.event.addDomListener(window, 'load', initialize);
</script>
<?php /*
<script type="text/javascript">
    L.mapbox.accessToken = 'pk.eyJ1IjoiemVsdWlzYXAiLCJhIjoiME45VDdEbyJ9.AzAZGZWdq8EzpRCL7EOmtg';
    
    var map = L.mapbox.map('map', 'examples.map-i86nkdio', { "minZoom" : <?php echo $zoom; ?>, "closePopupOnClick": false })
    .setView([<?php echo $this->latitude; ?>, <?php echo $this->longitude; ?>], <?php echo $zoom; ?>);
    var marker = L.mapbox.featureLayer({
        'type': 'Feature',
        'geometry': {
            type: 'Point',
            coordinates: [<?php echo $this->longitude; ?>, <?php echo $this->latitude; ?>]
        },
        'properties': {
            'title': '<?php echo $this->descricao; ?>',
            'description': '<?php echo $this->endereco; ?>.',
            'marker-color': '#000',
            'marker-size': 'large',
            'marker-symbol': 'bus'
        }
    }).addTo(map);
        
    marker.openPopup();
    
    marker.on("click", function(ev) {
        marker.openPopup();
    });
        
    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();

    if (map.tap) map.tap.disable();    
    
</script>
<?php
 */
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}