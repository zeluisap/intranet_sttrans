<?php
class TbIcone extends Escola_Tabela {
	protected $_name = "icone";
	protected $_rowClass = "Icone";
	protected $_referenceMap = array("IconeTipo" => array("columns" => array("id_icone_tipo"),
												   "refTableClass" => "TbIconeTipo",
												   "refColumns" => array("id_icone_tipo")));	
	
	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
	
	public function getSql($dados = array()) {
		$select = $this->select();
		$select->order("descricao");
		return $select;
	}
    
    public function importar_arquivo($arquivo) {
        $linhas = Escola_Util::carregaArquivoDados($arquivo["tmp_name"]);
        $filename = $arquivo["name"];
        $dados = array();
        $flag = explode(".", $filename);
        if ($flag && is_array($flag) && count($flag)) {
            $tb = new TbIconeTipo();
            $it = $tb->getPorChave(trim($flag[0]));
            if ($it) {
                $dados["id_icone_tipo"] = $it->getId();
            }
        }
        if ($linhas) {
            $tb = new TbIcone();
            foreach ($linhas as $linha) {
                $icone = $tb->createRow();
                $dados["descricao"] = $linha["icone"];
                $icone->setFromArray($dados);
                $errors = $icone->getErrors();
                if (!$errors) {
                    $icone->save();
                }
            }
        }
    }
}