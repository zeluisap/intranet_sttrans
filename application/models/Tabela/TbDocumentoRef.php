<?php
class TbDocumentoRef extends Escola_Tabela {
	protected $_name = "documento_ref";
	protected $_rowClass = "DocumentoRef";
	protected $_referenceMap = array("Documento" => array("columns" => array("id_documento"),
															 "refTableClass" => "TbDocumento",
															 "refColumns" => array("id_documento")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("dr" => "documento_ref"));
        $sql->join(array("d" => "documento"), "d.id_documento = dr.id_documento", array());

        if (isset($dados["tipo"]) && $dados["tipo"]) {
            $sql->where("dr.tipo = '{$dados["tipo"]}'");
        }

        if (isset($dados["chave"]) && $dados["chave"]) {
            $sql->where("dr.chave = '{$dados["chave"]}'");
        }

        if (isset($dados["id_documento"]) && $dados["id_documento"]) {
            $sql->where("dr.id_documento = '{$dados["id_documento"]}'");
        }

        if (isset($dados["ano"]) && $dados["ano"]) {
            $sql->where("d.ano = '{$dados["ano"]}'");
        }

        return $sql;
    }
    
    public function baixar($dados) {
        $prefixo = "";
        if (isset($dados["prefixo"]) && $dados["prefixo"]) {
            $prefixo = $dados["prefixo"];
        }
        $chave = 0;
        if (isset($dados["chave"]) && $dados["chave"]) {
            $chave = $dados["chave"];
        }
        $registros = $this->listar($dados);
        if ($registros && count($registros)) {
            $path_tmp = ROOT_DIR . "/application/file/tmp/{$chave}/";
            if (!file_exists($path_tmp)) {
                $flag = mkdir($path_tmp);
                if (!$flag) {
                    return "FALHA AO EXECUTAR OPERAÇÃO, IMPOSSÍVEL CRIAR PASTA TEMPORÁRIA!";
                }
            }
            $files = glob($path_tmp . "*.*");
            if ($files && is_array($files) && count($files)) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            $arquivos = array();
            foreach ($registros as $registro) {
                $doc = $registro->findParentRow("TbDocumento");
                if ($doc) {
                    $arquivo = $doc->pega_arquivo();
                    if ($arquivo && $arquivo->existe()) {
                        $at = $arquivo->findParentRow("TbArquivoTipo");
                        if ($at) {
                            $nome_completo = $arquivo->pegaNomeCompleto();
                            $filter = new Zend_Filter_CharConverter();
                            $filename = str_replace(" ", "_", $filter->filter(Escola_Util::minuscula($prefixo . "__" . $doc->resumo)));
                            $filename_new = $path_tmp . $filename . "." . $at->extensao;
                            $flag = copy($nome_completo, $filename_new);
                            if ($flag) {
                                $arquivos[] = $filename_new;
                            }
                        }
                    }
                }
            }
            $zip = new Zend_Filter_Compress_Zip();
            $filename = str_replace(" ", "_", $filter->filter(Escola_Util::minuscula($prefixo)));
            $zip->setArchive($path_tmp . "{$filename}.zip");
            //$zip->setTarget(ROOT_DIR . PATH_SEPARATOR . "application" . PATH_SEPARATOR . "file" . PATH_SEPARATOR);
            $arquivoZipado = $zip->compress($path_tmp);
            if ($arquivoZipado && file_exists($arquivoZipado)) {
                header("Content-Type: " . mime_content_type($arquivoZipado));
                header("Content-Disposition: attachment; filename={$filename}.zip");
                $f = fopen($arquivoZipado, "r");
                $buffer = fread($f, filesize($arquivoZipado));
                fclose($f);
                echo $buffer;
                die();
            }
        } else {
            return "NENHUM ARQUIVO DISPONÍVEL PARA IMPORTAÇÃO!";
        }
        return false;
    }
}