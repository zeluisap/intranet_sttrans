<?php

class Escola_Util
{

    public static function getBaseUrl()
    {
        $fc = Zend_Controller_Front::getInstance();
        return $fc->getBaseUrl();
    }

    public static function getBaseUrlPortal()
    {
        return Escola_Util::getBaseUrl() . "/portal";
    }

    public static function url($dados)
    {
        $view = new Zend_View();
        return $view->url($dados);
    }

    public static function listarDiaSemana()
    {
        return array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado");
    }

    public static function pegaDiaSemana($data)
    {
        $semanas = Escola_Util::listarDiaSemana();
        return $semanas[$data->get("e")];
    }

    public static function formatCpf($cpf)
    {
        $filter = new Zend_Filter_Digits();
        $cpf = $filter->filter($cpf);
        if ($cpf == "") {
            return $cpf;
        } else {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/i', '$1.$2.$3-$4', $cpf);
        }
    }

    public static function formatCep($cep)
    {
        $filter = new Zend_Filter_Digits();
        $cep = $filter->filter($cep);
        if ($cep == "") {
            return $cep;
        } else {
            return preg_replace('/(\d{2})(\d{3})(\d{3})/i', '$1.$2-$3', $cep);
        }
    }

    public static function formatCnpj($cnpj)
    {
        $filter = new Zend_Filter_Digits();
        $cnpj = $filter->filter($cnpj);
        if ($cnpj == "") {
            return $cnpj;
        } else {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/i', '$1.$2.$3/$4-$5', $cnpj);
        }
    }

    public static function formatData($data)
    {
        if ($data) {
            $data = new Zend_Date($data);
            return $data->get("dd/MM/yyyy");
        }
        return "";
    }

    public static function montaData($data)
    {
        if (trim($data)) {
            $dt = new Zend_Date($data);
            return $dt->get("y-MM-dd");
        }
        return null;
    }

    public static function getUploadedFile($filename)
    {
        $upload = new Zend_File_Transfer();
        $files = $upload->getFileInfo($filename);
        //$upload->receive(array($filename));
        if (isset($files[$filename])) {
            $info = $upload->getFileInfo($filename);
            if ($files[$filename]["size"]) {
                //	            $files[$filename]["tmp_name"] = $upload->getFileName($filename);
                return $files[$filename];
            }
        }
        return false;
    }

    public static function getUploadedFiles()
    {
        $uploads = array();
        $upload = new Zend_File_Transfer();
        $files = $upload->getFileInfo();
        //        $upload->receive();
        foreach ($files as $filename => $file) {
            if ($upload->isValid($filename)) {
                $uploads[$filename] = $file;
            }
            //$files[$filename]["tmp_name"] = $upload->getFileName($filename);
        }

        return $uploads;
    }

    public static function pegaExtensao($filename)
    {
        if ($filename) {
            return pathinfo($filename, PATHINFO_EXTENSION);
        }
        return "";
        /*
          $dados = explode(".", $filename);
          if (count($dados) > 1) {
          return $dados[count($dados) - 1];
          }
          return "";
         */
    }

    public static function maiuscula($texto)
    {
        $filter = new Zend_Filter_StringToUpper();
        return $filter->filter($texto);
    }

    public static function minuscula($texto)
    {
        $filter = new Zend_Filter_StringToLower();
        return $filter->filter($texto);
    }

    public static function validaEmail($email)
    {
        $filter = new Zend_Validate_EmailAddress();
        return $filter->isValid($email);
    }

    public static function validaData($data)
    {
        $filter = new Zend_Validate_Date("yyyy-MM-dd");
        return $filter->isValid($data);
    }

    public static function carregaArquivo($filename)
    {
        $handle = fopen($filename, "r");
        if ($handle) {
            $conteudos = array();
            while (($data = fgets($handle)) !== FALSE) {
                $conteudos[] = $data;
            }
            fclose($handle);
            if (count($conteudos)) {
                return $conteudos;
            }
        }
        return false;
    }

    public static function carregaArquivoDados($filename, $separador = ";")
    {
        $handle = fopen($filename, "r");
        if ($handle) {
            $titulos = $data = fgetcsv($handle, 1000, $separador);
            $conteudos = array();
            while (($data = fgetcsv($handle, 1000, $separador)) !== FALSE) {
                $tmp = array();
                for ($y = 0; $y < count($titulos); $y++) {
                    $tmp[$titulos[$y]] = str_replace("\"", "", $data[$y]);
                }
                $conteudos[] = $tmp;
            }
            if (count($conteudos)) {
                return $conteudos;
            }
        }
        fclose($handle);
        return false;
    }

    public static function zero($numero, $tamanho)
    {
        if ($numero) {
            return str_pad($numero, $tamanho, "0", STR_PAD_LEFT);
        }
        return "";
    }

    public static function tamanho_fixo($txt, $tamanho, $texto = " ")
    {
        $txt = trim($txt);

        if (strlen($txt) == $tamanho) {
            return $txt;
        }

        if (strlen($txt) > $tamanho) {
            return substr($txt, 0, $tamanho);
        }

        return str_pad($txt, $tamanho, $texto);
    }

    public static function validaCPF($cpf)
    {
        $val = new Escola_Validate_Cpf();
        return $val->isValid($cpf);
    }

    public function mostrarTamanho($tamanho)
    {
        if ($tamanho) {
            $tipos = array("Bytes", "KB", "MB", "GB");
            foreach ($tipos as $tipo) {
                if ($tamanho < 1024) {
                    return $tamanho . " {$tipo}";
                }
                $tamanho = (int) ($tamanho / 1024);
            }
            return $this->tamanho;
        }
        return "";
    }

    public static function limparNumero($numero)
    {
        $filter = new Zend_Filter_Digits();
        return $filter->filter($numero);
    }

    public static function pegaMeses()
    {
        return array(
            1 => "Janeiro",
            2 => "Fevereiro",
            3 => "Março",
            4 => "Abril",
            5 => "Maio",
            6 => "Junho",
            7 => "Julho",
            8 => "Agosto",
            9 => "Setembro",
            10 => "Outubro",
            11 => "Novembro",
            12 => "Dezembro"
        );
    }

    public static function pegaMes($mes)
    {
        $meses = Escola_Util::pegaMeses();
        if (isset($meses[$mes])) {
            return $meses[$mes];
        }
        return "";
    }

    public static function number_format($valor)
    {
        $valor = round($valor, 2);
        $curr = Zend_Locale_Format::toNumber($valor, array("number_format" => "#,##0.00"));
        return $curr;
    }

    public static function montaNumero($numero)
    {
        if ($numero) {
            $numero = str_replace('.', '', $numero);
            $numero = str_replace(',', '.', $numero);
        }
        return $numero;
    }

    public static function consulta_ibase($sql)
    {
        $dbibase = Zend_Registry::get("dbibase");

        $sth = ibase_query($dbibase, $sql);
        $items = array();
        while ($row = ibase_fetch_object($sth)) {
            $items[] = $row;
        }
        ibase_free_result($sth);
        if (count($items)) {
            return $items;
        }
        return false;
    }

    public static function limpaNumero($numero)
    {
        $filter = new Zend_Filter_Digits();
        return $filter->filter($numero);
    }

    /**
     * isCnpjValid
     *
     * Esta funï¿½ï¿½o testa se um Cnpj ï¿½ valido ou nï¿½o. 
     *
     * @author	Raoni Botelho Sporteman <raonibs@gmail.com>
     * @version	1.0 Debugada em 27/09/2011 no PHP 5.3.8
     * @param	string		$cnpj			Guarda o Cnpj como ele foi digitado pelo cliente
     * @param	array		$num			Guarda apenas os nï¿½meros do Cnpj
     * @param	boolean		$isCnpjValid	Guarda o retorno da funï¿½ï¿½o
     * @param	int			$multiplica 	Auxilia no Calculo dos Dï¿½gitos verificadores
     * @param	int			$soma			Auxilia no Calculo dos Dï¿½gitos verificadores
     * @param	int			$resto			Auxilia no Calculo dos Dï¿½gitos verificadores
     * @param	int			$dg				Dï¿½gito verificador
     * @return	boolean						"true" se o Cnpj ï¿½ vï¿½lido ou "false" caso o contrï¿½rio
     *
     */
    public static function isCnpjValid($cnpj)
    {
        //Etapa 1: Cria um array com apenas os digitos numï¿½ricos, isso permite receber o cnpj em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
        $j = 0;
        for ($i = 0; $i < (strlen($cnpj)); $i++) {
            if (is_numeric($cnpj[$i])) {
                $num[$j] = $cnpj[$i];
                $j++;
            }
        }
        //Etapa 2: Conta os dï¿½gitos, um Cnpj vï¿½lido possui 14 dï¿½gitos numï¿½ricos.
        if (count($num) != 14) {
            $isCnpjValid = false;
            return false;
        }
        //Etapa 3: O nï¿½mero 00000000000 embora nï¿½o seja um cnpj real resultaria um cnpj vï¿½lido apï¿½s o calculo dos dï¿½gitos verificares e por isso precisa ser filtradas nesta etapa.
        if ($num[0] == 0 && $num[1] == 0 && $num[2] == 0 && $num[3] == 0 && $num[4] == 0 && $num[5] == 0 && $num[6] == 0 && $num[7] == 0 && $num[8] == 0 && $num[9] == 0 && $num[10] == 0 && $num[11] == 0) {
            $isCnpjValid = false;
        }
        //Etapa 4: Calcula e compara o primeiro dï¿½gito verificador.
        else {
            $j = 5;
            for ($i = 0; $i < 4; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $j = 9;
            for ($i = 4; $i < 12; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
            if ($resto < 2) {
                $dg = 0;
            } else {
                $dg = 11 - $resto;
            }
            if ($dg != $num[12]) {
                $isCnpjValid = false;
            }
        }
        //Etapa 5: Calcula e compara o segundo dï¿½gito verificador.
        if (!isset($isCnpjValid)) {
            $j = 6;
            for ($i = 0; $i < 5; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $j = 9;
            for ($i = 5; $i < 13; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
            if ($resto < 2) {
                $dg = 0;
            } else {
                $dg = 11 - $resto;
            }
            if ($dg != $num[13]) {
                $isCnpjValid = false;
            } else {
                $isCnpjValid = true;
            }
        }
        //Trecho usado para depurar erros.
        /*
          if($isCnpjValid==true)
          {
          echo "<p><font color=\"GREEN\">Cnpj ï¿½ Vï¿½lido</font></p>";
          }
          if($isCnpjValid==false)
          {
          echo "<p><font color=\"RED\">Cnpj Invï¿½lido</font></p>";
          }
         */
        //Etapa 6: Retorna o Resultado em um valor booleano.
        return $isCnpjValid;
    }

    function agrupaTransporte($stmt)
    {
        $db = Zend_Registry::get("db");
        $tb_ss = new TbServicoSolicitacao();
        if ($stmt && $stmt->rowCount()) {
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $db->beginTransaction();
                try {
                    $transportes = array();
                    $sql1 = "select a.*
                            from transporte a, transporte_pessoa b
                            where (a.id_transporte = b.id_transporte)
                            and (a.id_transporte_grupo = {$obj->id_transporte_grupo})
                            and (b.id_pessoa = {$obj->id_pessoa})
                            order by a.id_transporte";
                    $stmt1 = $db->query($sql1);
                    if ($stmt1 && $stmt1->rowCount()) {
                        while ($obj1 = $stmt1->fetch(Zend_Db::FETCH_OBJ)) {
                            $transportes[] = $obj1;
                        }
                    }
                    if (count($transportes) > 1) {
                        $tra_pessoa = array();
                        $transporte_principal = $transportes[0];
                        unset($transportes[0]);
                        $tra_principal = TbTransporte::pegaPorId($transporte_principal->id_transporte);

                        $tvs = $tra_principal->findDependentRowset("TbTransporteVeiculo");
                        $sss = $tb_ss->listar(array("id_transporte" => $transporte_principal->id_transporte));
                        if ($sss && count($sss)) {
                            foreach ($sss as $ss) {
                                if ($tvs && (count($tvs) == 1)) {
                                    $tv = $tvs->current();
                                    $stg = $ss->findParentRow("TbServicoTransporteGrupo");
                                    if ($stg) {
                                        $servico = $stg->findParentRow("TbServico");
                                        if ($servico) {
                                            $sr = $servico->findParentRow("TbServicoReferencia");
                                            if ($sr && $sr->veiculo()) {
                                                $ss->tipo = "TV";
                                                $ss->chave = $tv->getId();
                                            }
                                        }
                                    }
                                }
                                if ($ss->tipo == "TR") {
                                    $ss->chave = $transporte_principal->id_transporte;
                                }
                                $ss->id_transporte = $transporte_principal->id_transporte;
                                $ss->save();
                            }
                        }

                        $tps = $tra_principal->findDependentRowset("TbTransportePessoa");
                        if ($tps && count($tps)) {
                            foreach ($tps as $tp) {
                                $tra_pessoa[] = $tp->id_pessoa;
                            }
                        }
                        foreach ($transportes as $transporte) {
                            $tra = TbTransporte::pegaPorId($transporte->id_transporte);
                            if ($tra) {
                                $tvs = $tra->findDependentRowset("TbTransporteVeiculo");
                                $sss = $tb_ss->listar(array("id_transporte" => $transporte->id_transporte));
                                if ($sss && count($sss)) {
                                    foreach ($sss as $ss) {
                                        if ($tvs && (count($tvs) == 1)) {
                                            $tv = $tvs->current();
                                            $stg = $ss->findParentRow("TbServicoTransporteGrupo");
                                            if ($stg) {
                                                $servico = $stg->findParentRow("TbServico");
                                                if ($servico) {
                                                    $sr = $servico->findParentRow("TbServicoReferencia");
                                                    if ($sr && $sr->veiculo()) {
                                                        $ss->tipo = "TV";
                                                        $ss->chave = $tv->getId();
                                                    }
                                                }
                                            }
                                        }
                                        if ($ss->tipo == "TR") {
                                            $ss->chave = $transporte_principal->id_transporte;
                                        }
                                        $ss->id_transporte = $transporte_principal->id_transporte;
                                        $ss->save();
                                    }
                                }
                                if ($tvs && count($tvs)) {
                                    foreach ($tvs as $tv) {
                                        $tv->id_transporte = $transporte_principal->id_transporte;
                                        $errors = $tv->getErrors();
                                        if (!$errors) {
                                            $tv->save();
                                        }
                                    }
                                }
                            }
                            $tps = $tra->findDependentRowset("TbTransportePessoa");
                            if ($tps && count($tps)) {
                                foreach ($tps as $tp) {
                                    if (!in_array($tp->id_pessoa, $tra_pessoa)) {
                                        $tp->id_transporte = $transporte_principal->id_transporte;
                                        $errors = $tp->getErrors();
                                        if (!$errors) {
                                            $tp->save();
                                        }
                                    }
                                }
                            }
                            $derrors = $tra->getDeleteErrors();
                            if (!$derrors) {
                                $tra->delete();
                            } else {
                                Zend_Debug::dump($derrors);
                                die();
                            }
                        }
                    }
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    die($e->getMessage());
                }
            }
        }
    }

    public function format_moeda($valor)
    {
        $items = array();
        if (!is_numeric($valor)) {
            $valor = 0;
        }
        $tb = new TbMoeda();
        $moeda = $tb->pega_padrao();
        if ($moeda) {
            $items[] = $moeda->simbolo;
        }
        $items[] = Escola_Util::number_format($valor);
        return implode(" ", $items);
    }

    function validaHora($hora)
    {
        if ($hora) {
            $array_hora = explode(":", $hora);
            if (count($array_hora) == 3) {
                $hour = $array_hora[0];
                $min = $array_hora[1];
                $sec = $array_hora[2];
                if (!is_numeric($hour) || ($hour < 0) || ($hour > 23)) {
                    return false;
                }
                if (!is_numeric($min) || ($min < 0) || ($min > 59)) {
                    return false;
                }
                if (!is_numeric($sec) || ($sec < 0) || ($sec > 59)) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    public static function carregaArquivoDadosExcel($filename)
    {

        include_once("PHPExcel/Classes/PHPExcel.php");

        $inputFileType = PHPExcel_IOFactory::identify($filename);

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);

        $objPHPExcel = $objReader->load($filename);

        $qtd = $objPHPExcel->getSheetCount();

        $retorno = array();

        for ($i = 0; $i < $qtd; $i++) {
            $planilha = $objPHPExcel->getSheet($i);
            $highestRow = $planilha->getHighestRow();

            $retorno[$planilha->getTitle()] = array();
            $titulos = array();

            $coluna = 0;
            do {
                $titulo = trim($planilha->getCellByColumnAndRow($coluna, 1)->getValue());
                if (!$titulo) {
                    break;
                }
                $titulos[$coluna] = $titulo;
                $coluna++;
            } while ($titulo);

            for ($linha = 2; $linha <= $highestRow; $linha++) {

                $items = array();
                for ($coluna = 0; $coluna < count($titulos); $coluna++) {

                    if (!isset($titulos[$coluna])) {
                        throw new Exception("Nenhum Tï¿½tulo para a Coluna: {$coluna}.");
                    }

                    $items[$titulos[$coluna]] = $planilha->getCellByColumnAndRow($coluna, $linha)->getValue();
                }

                $retorno[$planilha->getTitle()][] = $items;
            }
        }

        //$retorno = utf8_decode_array($retorno);

        return $retorno;
    }

    public static function importa_excel($filename)
    {

        //echo " ... Processando Planilha ... " . PHP_EOL . PHP_EOL;

        if (!file_exists($filename)) {
            return;
        }

        $planilhas = self::carregaArquivoDadosExcel($filename);

        if (count($planilhas) == 1) {
            return current($planilhas);
        }

        $idc = 0;
        while (!$idc) {
            $contador = 0;
            $dados_filtro = array();
            foreach ($planilhas as $chave => $valor) {
                $contador++;

                $dados_filtro[$contador] = $chave;

                echo "{$contador} - {$chave}." . PHP_EOL;
            }
            echo "Qual Planilha Deseja Trabalhar?: ";
            $idc = trim(fgets(STDIN));

            if (!$idc) {
                continue;
            }

            if (!array_key_exists($idc, $dados_filtro)) {
                continue;
            }

            return $planilhas[$dados_filtro[$idc]];
        }

        return false;
    }

    public static function log($obj = "", $ln = true)
    {
        if (!$obj) {
            return;
        }

        if (is_array($obj)) {
            $obj = array_filter($obj, function ($item) {
                if (is_array($item)) {
                    if (count($item)) {
                        return $item;
                    }
                    return false;
                }
                return $item;
            });

            $obj = array_map(function ($item) {
                if (!is_array($item)) {
                    return trim($item);
                }

                $txt = $item[0];
                if (!(isset($item[1]) && $item[1])) {
                    return trim($txt);
                }

                $tamanho = $item[1];
                $preencher_com = " ";
                if (isset($item[2]) && $item[2]) {
                    $preencher_com = $item[2];
                }

                return self::tamanho_fixo(trim($txt), $tamanho, $preencher_com);
            }, $obj);

            $obj = implode(" - ", $obj);
        }

        if ($ln) {
            $obj .= PHP_EOL;
        }

        echo $obj;
    }

    public static function isResultado($objs)
    {
        return ($objs && is_array($objs) && count($objs));
    }

    public static function hasParametro($param)
    {
        global $argv;

        for ($i = 0; $i < count($argv); $i++) {
            $valor = $argv[$i];
            if (strtolower($valor) == strtolower($param)) {
                return true;
            }
        }

        return false;
    }

    public static function getParametro($param)
    {
        global $argv;

        $index = null;
        for ($i = 0; $i < count($argv); $i++) {
            $valor = $argv[$i];
            if (strtolower($valor) == strtolower($param)) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return null;
        }

        if (!isset($argv[$index + 1])) {
            return null;
        }

        $valor = $argv[$index + 1];

        if (!$valor) {
            return null;
        }

        return $valor;
    }

    public static function iniciaCom($texto_original, $procurar_por)
    {
        if (!$texto_original || !$procurar_por) {
            return false;
        }

        return (strpos($texto_original, $procurar_por) === 0);
    }

    public static function array_search($objs, $callback)
    {
        if (!self::isResultado($objs)) {
            return [];
        }

        $array = [];
        foreach ($objs as $obj) {
            if ($callback($obj)) {
                $array[] = $obj;
            }
        }

        return $array;
    }

    public static function array_some($objs, $callback)
    {
        $array = self::array_search($objs, $callback);

        return self::isResultado($array);
    }

    public static function trataErro($ex, $die = true)
    {

        $erro = new stdClass();
        $erro->message = $ex->getMessage();
        if (self::hasParametro("--trace")) {
            $erro->trace = $ex->getTraceAsString();
        }
        print_r($erro);
        if ($die) {
            die();
        }
    }

    public static function getController()
    {
        try {
            $params = func_get_args();

            if (!self::isResultado($params)) {
                return null;
            }

            $params = array_map(function ($item) {
                $partes = explode("_", $item);
                if (count($partes) <= 1) {
                    return ucfirst($item);
                }

                $partes = array_map("ucfirst", $partes);

                return implode("", $partes);
            }, $params);

            $controller_filename = implode(DIRECTORY_SEPARATOR, $params) . "Controller";
            $controller_name = implode("_", $params) . "Controller";

            include_once "controllers/{$controller_filename}.php";

            return new $controller_name(new Zend_Controller_Request_Simple(), new Zend_Controller_Response_Cli());
        } catch (Exception $ex) {
            return null;
        }
    }

    public static function progresso($percentual, $tamanho_total = 10)
    {
        $percentual = floor($percentual);
        $perc_tabela = $percentual / 100;

        $perc_tabela = floor($tamanho_total * $perc_tabela);

        if ($perc_tabela > $tamanho_total) {
            $perc_tabela = $tamanho_total;
        }

        return str_pad($percentual, 4, " ", STR_PAD_LEFT) . "% [" . str_pad("", $perc_tabela, "#") . str_pad("", $tamanho_total - $perc_tabela, " ") . "]";
    }


    /**
     * Testa se $val é vazio, caso deseje-se verificar uma propriedades dentro de $val pode se usar o 
     * segundo argumento para especificar o nome da propriedade ou o path usando a sintaxe ->
     * @param string $val valor a ser testado se é vazio
     * @param array $props nome ou path -> da propriedade de $val a ser testada se é vazio 
     * @return string <bool> Um boleano indicando ou não a presença do valor
     */
    public static function vazio($val, $props = null)
    {
        $vazio = ($val == null || empty($val) || (is_string($val) && trim($val) == ""));

        if ($vazio) {
            return true;
        }

        if (!((is_array($val) || is_object($val)) && isset($props))) {
            return $vazio;
        }

        $valor = self::valorOuNulo($val, $props);
        return self::vazio($valor);
    }

    public static function _valorOuNulo($obj, $key = null)
    {
        if (!isset($obj)) {
            return null;
        }

        if (empty($key)) {
            return self::coalesce($obj, null);
        }

        if (is_array($obj)) {
            return (array_key_exists($key, $obj) ? $obj[$key] : null);
        }

        if ($obj instanceof \StdClass) {
            return (property_exists($obj, $key) ? $obj->$key : null);
        }

        if (is_object($obj)) {
            $method_name = "get" . ucfirst($key);
            return (method_exists($obj, $method_name) ? $obj->$method_name() : null);
        }

        return self::coalesce($obj, null);;
    }

    public static function valorOuNulo($obj, $key = null)
    {
        if (!isset($obj)) {
            return null;
        }

        $isPathInObject = (strpos($key, '->') !== false);

        if (!$isPathInObject) {
            return self::_valorOuNulo($obj, $key);
        }

        $parts = explode('->', $key);

        $lastPiece = $obj;

        for ($i = 0; $i < count($parts); $i++) {
            $lastPiece = self::_valorOuNulo($lastPiece, $parts[$i]);
            if (!$lastPiece) {
                return null;
            }
        }

        return $lastPiece;
    }


    public static function valorOuCoalesce($obj, $key = null, $valor)
    {
        $val = self::valorOuNulo($obj, $key);
        return self::coalesce($val, $valor);
    }

    public static function coalesce(...$val)
    {
        if (!count($val)) {
            return null;
        }

        foreach ($val as $v) {
            if (is_null($v)) {
                continue;
            }
            return $v;
        }

        return null;
    }

    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }


    // http://forum.imasters.com.br/topic/502244-remover-acentos-em-strings-php/
    public static function removerAcentos($nome)
    {
        // o uso disso estava dando algum erro com o retorno de Cícero Duarte esse í retornava ?
        // era alguma configuração de server... mas preferi não entrar no mérito
        // $nomeSemAcentos = preg_replace( '/[`^~\'"]/', null, iconv( 'UTF-8', 'ASCII//TRANSLIT', $nome ) );          
        // return $nomeSemAcentos;

        // TODO: isso é potencialmente problematico já que estou mapeando manualmente os caracteres...
        // talvez uma solução seja usar o unacent() do postgres ?

        $str = $nome;
        $from = "áàãâéêíîóôõúüûçÁÀÃÂÉÊÍÎÓÔÕÚÜÛÇ";
        $to = "aaaaeeiiooouuucAAAAEEIIOOOUUUC";

        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);

        $nomeSemAcentos = strtr($str, $mapping);
        return $nomeSemAcentos;
    }

    public static function textoParaFieldName($textos)
    {

        if (!$textos) {
            return '';
        }

        if (!is_array($textos)) {
            $textos = [$textos];
        }

        $convertidos = [];
        foreach ($textos as $texto) {

            if (!$texto) {
                continue;
            }

            $texto = self::removerAcentos(strtolower(trim($texto)));

            if (!$texto) {
                continue;
            }

            $texto = preg_replace("/([^a-z0-9]+)/", "_", $texto);
            if (!$texto) {
                continue;
            }

            $convertidos[] = $texto;
        }

        if (Escola_Util::vazio($convertidos)) {
            return '';
        }

        return implode("_", $convertidos);
    }

    public static function formatCpfCnpj($cpf_cnpj)
    {
        if (!$cpf_cnpj) {
            return $cpf_cnpj;
        }

        $numero = self::limpaNumero($cpf_cnpj);
        if (!$numero) {
            return $cpf_cnpj;
        }

        if (strlen($numero) == 11) {
            return self::formatCpf($numero);
        } elseif (strlen($numero) == 14) {
            return self::formatCnpj($numero);
        }

        return $cpf_cnpj;
    }

    public static function tamanhoMenorOuCorta($txt, $tamanho)
    {
        if (!$tamanho) {
            return $txt;
        }

        if (strlen($txt) <= $tamanho) {
            return $txt;
        }

        return substr($txt, 0, $tamanho);
    }
}
