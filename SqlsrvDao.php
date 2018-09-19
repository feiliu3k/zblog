<?php

class SqlsrvDao{

    protected $dbh = NULL; 

    public function __construct($dbh) {
       $this->dbh=$dbh;      
    }

    public function getADInfoBygetADInfoByItemID($strItemID)
    {        
        $sql='select top 1 STRITEMID, STRDESCRIPTION, LDURATION, STRSCEDULEPLAYTIME, STRPARENTITEMID, STRCHID from TMP_PLAYINGLIST where STRITEMID=?';     
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(1, $strItemID, PDO::PARAM_STR);
        $stmt->execute();       
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;             
    }

    public function getADInfoByItemIDs($strItemIDs)
    {        
        try { 
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//异常模式  
            $this->dbh->beginTransaction();//开启事物  
            $sql = "select top 1 STRITEMID, STRDESCRIPTION, LDURATION, STRSCEDULEPLAYTIME, STRPARENTITEMID, STRCHID from TMP_PLAYINGLIST where STRITEMID=?";

            // $clipFiles->map(function($item, $key) use ($sql) {
            //     $stmt = $this->dbh->prepare($sql);
            //     $stmt->bindValue(1, $item->clipFile, PDO::PARAM_STR);
            //     $stmt->execute();       
            //     $result = $stmt->fetch(PDO::FETCH_ASSOC);
            //     $item->put('strdescription', $result['strdescription']);
            //     return $item;     
            // }

            foreach ($strItemIDs as $strItemID) {                               
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(1, $strItemID['strItemID'], PDO::PARAM_STR);
                $stmt->execute();       
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                //$clipFile=array_merge($clipFile, $result) ;
                $strItemID->put('strdescription', $result['STRDESCRIPTION']);
                $strItemID->put('lduration', $result['LDURATION']);  
            }

            $this->dbh->commit();//执行事物的提交操作*/  
            return $strItemIDs;
        }catch (PDOException $e){  
            die("Error!: ".$e->getMessage().'<br>');  
            $this->dbh->rollBack();//执行事物的回滚操作  
        }  
        return false;            
    }

    public function getADInfoByCH($d_date, $ch)
    {
        try { 
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//异常模式  
            $this->dbh->beginTransaction();//开启事物  
            $sql = "select a.STRITEMID, a.STRDESCRIPTION, a.LDURATION, a.STRSCEDULEPLAYTIME, a.STRPARENTITEMID, a.STRCHID, b.STRITEMNAME,c.strCHName from BMI_PLAYEDLIST a left join BMI_PLAYEDLIST b on a.STRPARENTITEMID=b.STRITEMID left join CFG_CHANNEL c on a.STRCHID=c.strCHID where a.STRSCEDULEPLAYTIME between ? and ? and a.STRCHID=? and (a.STRPARENTITEMID<>'None' and a.STRPARENTITEMID<>'Group') order by a.STRSCEDULEPLAYTIME asc";
                                     
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(1, $d_date.' 00:00:00:00', PDO::PARAM_STR);
            $stmt->bindValue(2, $d_date.' 23:59:59:24', PDO::PARAM_STR);
            $stmt->bindValue(3, $ch, PDO::PARAM_STR);
            $stmt->execute();       
            $result = $stmt->FetchAll(PDO::FETCH_ASSOC);                       
            $this->dbh->commit();//执行事物的提交操作*/  
            return $result;
        }catch (PDOException $e){  
            die("Error!: ".$e->getMessage().'<br>');  
            $this->dbh->rollBack();//执行事物的回滚操作  
        }

        return false;

    }
}