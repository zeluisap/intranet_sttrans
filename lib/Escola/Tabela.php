<?php

class Escola_Tabela extends Zend_Db_Table_Abstract {

    public static function getLegenda() {
        $class = get_called_class();
        $tb = new $class;
        return ucfirst($tb->_name);
    }

    public static function pegaPorId($id) {
        $class = get_called_class();
        $tb = new $class;
        return $tb->getPorId($id);
    }

    public function getPorId($id) {
        $rg = $this->find($id);
        if (count($rg)) {
            return $rg[0];
        }
        return false;
    }

    public function getSql($dados = array()) {
        return $this->select();
    }

    public function listar($dados = array()) {
        $select = $this->getSql($dados);
        $rgs = $this->fetchAll($select);
        if ($rgs->count()) {
            return $rgs;
        }
        return false;
    }

    public function listar_por_pagina($dados = array()) {
        $sql = $this->getSql($dados);
        $adapter = new Zend_Paginator_Adapter_DbTableSelect($sql);
        $paginator = new Zend_Paginator($adapter);
        if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
            $paginator->setCurrentPageNumber($dados["pagina_atual"]);
        }
        $qtd_por_pagina = 20;
        if (isset($dados["qtd_por_pagina"]) && $dados["qtd_por_pagina"]) {
            $qtd_por_pagina = $dados["qtd_por_pagina"];
        }
        $paginator->setItemCountPerPage($qtd_por_pagina);
        return $paginator;
    }

    public static function getTabelaPorEntidade($entidade) {
        if (Escola_Util::vazio($entidade)) {
            return null;
        }
        return "Tb" . implode("", array_map("ucfirst", explode("_", $entidade)));
    }

}