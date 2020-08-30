<?php

class Escola_Tabela_Log extends Escola_Tabela {
    
    public function verify() {

        try {
            $dblog = $this->getAdapter(); //throws exception
        } catch (Exception $e) {
            var_dump($e); die();
        }
        
        $dbname = "";
        $db = Zend_Registry::get("db");
        $config = $db->getConfig();
        if (isset($config["dbname"]) && $config["dbname"]) {
            $dbname = $config["dbname"] . "_log";
        }
        
        $flag = false;
        $objs = $db->fetchAll("show databases like '{$dbname}'");
        if ($objs && count($objs)) {
            $flag = true;
        }
        
        if (!$flag) {
            try {
                $obj = $db->query("create database {$dbname}");
            } catch (Exception $ex) {
                var_dump("Erro Gerando Banco: " . $ex->getMessage()); die();
            }
        }
        
        try {
            // log_operacao
            $objs = $db->fetchAll("show tables like 'log_operacao'"); //throws exception
            $sql = "
                
CREATE TABLE IF NOT EXISTS `log_operacao` (
  `id_log_operacao` INT NOT NULL AUTO_INCREMENT,
  `chave` VARCHAR(3) NULL,
  `descricao` VARCHAR(50) NULL,
  PRIMARY KEY (`id_log_operacao`))
ENGINE = InnoDB;

";
            $obj = $dblog->query($sql);

            //log
            $objs = $db->fetchAll("show tables like 'log'"); //throws exception
            $sql = "
                
CREATE TABLE IF NOT EXISTS `log` (
  `id_log` INT NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(20) NULL,
  `cpf` VARCHAR(11) NULL,
  `nome` VARCHAR(100) NULL,
  `tabela` VARCHAR(50) NULL,
  `data` TIMESTAMP NULL,
  `validador` VARCHAR(50) NULL,
  `id` INT NULL,
  `id_log_operacao` INT NOT NULL,
  PRIMARY KEY (`id_log`),
  INDEX `fk_log_log_operacao_idx` (`id_log_operacao` ASC) ,
  CONSTRAINT `fk_log_log_operacao`
    FOREIGN KEY (`id_log_operacao`)
    REFERENCES `intranet_sttrans_log`.`log_operacao` (`id_log_operacao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
";
            $obj = $dblog->query($sql);

            //log
            $objs = $db->fetchAll("show tables like 'log_campo'"); //throws exception
            $sql = "
                
CREATE TABLE IF NOT EXISTS `log_campo` (
  `id_log_campo` INT NOT NULL AUTO_INCREMENT,
  `id_log` INT NOT NULL,
  `nome_campo` VARCHAR(50) NULL,
  `valor_anterior` VARCHAR(100) NULL,
  `valor_depois` VARCHAR(100) NULL,
  PRIMARY KEY (`id_log_campo`) ,
  INDEX `fk_log_campo_log1_idx` (`id_log` ASC) ,
  CONSTRAINT `fk_log_campo_log1`
    FOREIGN KEY (`id_log`)
    REFERENCES `intranet_sttrans_log`.`log` (`id_log`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

";
            $obj = $dblog->query($sql);
            
        } catch (Exception $e) {
            var_dump($e); die();
        }
        
    }

    public function init() {
        parent::init();

        $dblog = Zend_Registry::get("dblog");
        $this->_setAdapter($dblog);

        $this->verify();
    }
    
}