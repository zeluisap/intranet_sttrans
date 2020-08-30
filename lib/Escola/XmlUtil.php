<?php

class Escola_XmlUtil
{

    public static function arrayToXml($array, $indent = 0)
    {
        if (!($array && is_array($array) && count($array))) {
            return '';
        }

        $xml = [];
        foreach ($array as $key => $array_item) {
            $xml[$key] = self::getXml([
                "key" => $key,
                "array_item" => $array_item,
                "indent" => $indent
            ]);
        }

        return implode("", $xml);
    }

    public static function getXml($options)
    {

        $indent = Escola_Util::valorOuCoalesce($options, "indent", 0);
        $tab = Escola_Util::valorOuCoalesce($options, "tab", '');
        $key = Escola_Util::valorOuCoalesce($options, "key", null);
        $array_item = Escola_Util::valorOuCoalesce($options, "array_item", null);
        $attrs = Escola_Util::valorOuCoalesce($options, "attrs", null);

        if ($indent) {
            for ($i = 0; $i < $indent; $i++) {
                $tab .= "\t";
            }
        }

        $xml[$key] = $array_item;

        if (is_array($array_item)) {
            return self::getXmlArray([
                "key" => $key,
                "array_item" => $array_item,
                "tab" => $tab,
                "indent" => $indent,
                "attrs" => $attrs
            ]);
        }

        $atributos = self::getAtributos($options);

        return PHP_EOL . "{$tab}<{$key} {$atributos}>" . $xml[$key] . "</{$key}>";
    }

    public static function getXmlArray($options)
    {
        $indent = Escola_Util::valorOuCoalesce($options, "indent", 0);
        $tab = Escola_Util::valorOuCoalesce($options, "tab", '');
        $key = Escola_Util::valorOuCoalesce($options, "key", null);
        $array_item = Escola_Util::valorOuCoalesce($options, "array_item", null);
        $attrs = Escola_Util::valorOuCoalesce($options, "attrs", null);

        if (!is_array($array_item)) {
            return '';
        }

        $params = [
            "key" => $key,
            "array_item" => $array_item,
            "tab" => $tab,
            "indent" => $indent,
            "attrs" => $attrs
        ];

        if ($xml = self::getXmlArrayAttributos($params)) {
            return $xml;
        }

        if ($xml = self::getXmlArrayObjeto($params)) {
            return $xml;
        }

        return self::getXmlArrayLista($params);
    }

    public static function getXmlArrayAttributos($options)
    {

        $indent = Escola_Util::valorOuCoalesce($options, "indent", 0);
        $tab = Escola_Util::valorOuCoalesce($options, "tab", '');
        $key = Escola_Util::valorOuCoalesce($options, "key", null);
        $array_item = Escola_Util::valorOuCoalesce($options, "array_item", null);
        $attrs = Escola_Util::valorOuCoalesce($options, "attrs", null);

        $attrs = Escola_Util::valorOuNulo($array_item, "attrs");
        if (!($attrs && is_array($attrs) && count($attrs))) {
            return '';
        }

        $params = [
            "key" => $key,
            "array_item" => Escola_Util::valorOuNulo($array_item, "valor"),
            "tab" => $tab,
            "indent" => $indent,
            "attrs" => $attrs
        ];

        return self::getXmlArray($params);
    }

    public static function getXmlArrayLista($options)
    {

        $tab = Escola_Util::valorOuCoalesce($options, "tab", '');
        $key = Escola_Util::valorOuCoalesce($options, "key", null);
        $array_item = Escola_Util::valorOuCoalesce($options, "array_item", null);
        $indent = Escola_Util::valorOuCoalesce($options, "indent", 0);

        if (!self::isArrayLista($array_item)) {
            return '';
        }

        $atributos = '';
        // $atributos = self::getAtributos($options);

        $xml = [];
        foreach ($array_item as $valor) {
            $params = [
                "key" => $key,
                "array_item" => $valor,
                "tab" => $tab,
                "indent" => $indent,
            ];

            $xml[] = self::getxml($params);
        }

        return implode("", $xml);
    }

    public static function getXmlArrayObjeto($options)
    {

        $indent = Escola_Util::valorOuCoalesce($options, "indent", 0);
        $tab = Escola_Util::valorOuCoalesce($options, "tab", '');
        $key = Escola_Util::valorOuCoalesce($options, "key", null);
        $array_item = Escola_Util::valorOuCoalesce($options, "array_item", null);

        if (self::isArrayLista($array_item)) {
            return '';
        }

        $xml = [];
        foreach ($array_item as $chave =>  $valor) {

            $params = [
                "key" => $chave,
                "array_item" => $valor,
                "tab" => $tab,
                "indent" => $indent + 1,
                "attrs" => null
            ];

            $xml[] = self::getXml($params);
        }

        $atributos = self::getAtributos($options);

        return PHP_EOL . "{$tab}<{$key} {$atributos}>" . implode("", $xml) . PHP_EOL . "{$tab}</{$key}>";
    }

    public static function isArrayLista($array)
    {

        if (!is_array($array)) {
            return false;
        }

        foreach ($array as $chave => $valor) {
            if (is_numeric($chave)) {
                return true;
            }
        }

        return false;
    }

    public static function getAtributos($options)
    {
        $attrs = Escola_Util::valorOuNulo($options, "attrs");
        if (!($attrs && is_array($attrs) && count($attrs))) {
            return '';
        }

        $txt = [];
        foreach ($attrs as $chave => $valor) {
            $txt[] = "{$chave}=\"{$valor}\"";
        }

        return implode(" ", $txt);
    }
}
