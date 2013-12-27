<?php
/*
 * mysql数据库类 基本功能型
 * 2013.7.18 修改 
 * 作者 tim212@21cn.com
 */

class DbMysql
{
    protected $mpLink = null;    // 当前使用的数据库连接符（p=resource link_identifier，表示资源符变量）
	/*
	 * 获取数据库实例
	 */
	public static function getInstance($aDbConfig) {
		extract($aDbConfig);
			
		$oDb = new DbMysql();
		$oDb->connect($host, $user, $password, $dbname, $charset);
		
		return $oDb;
	}
	
    /*
     * @desc    连接数据库
     * @para    string  $cDbHost 数据库主机地址
     * @para    string  $cDbUser 登陆帐号
     * @para    string  $cDbPass   登陆密码
     * @para    string  $cDbName 数据库名
     * @para    string  $cDbCharset  数据库字符集
     * @para    bool    $bPconnect   是否进行长连接，默认false，普通连接
     * @return
     */
	public function connect($cDbHost, $cDbUser, $cDbPass, $cDbName = '', $cDbCharset = '', $bPconnect = false) {
        $func = empty($bPconnect) ? 'mysql_connect' : 'mysql_pconnect';
        if(!$this->mpLink = @$func($cDbHost, $cDbUser, $cDbPass, 1)) {
            $this->halt('Can not connect to MySQL server');
        } else {
			$cDbName && mysql_select_db($cDbName, $this->mpLink);
            $cDbCharset && mysql_query("SET NAMES " . $cDbCharset, $this->mpLink);
		}
	}

    /*
     * @desc    根据sql查询或执行，返回结果集
     * @para    string  $cSql    查询的sql语句
     * @return  resource-handler/bool   结果集资源符（针对SELECT，SHOW，EXPLAIN 或 DESCRIBE 语句），
                                        其他语句执行成功返回true，如果sql语句错误则返回false
     */
	public function query($cSql) {
		// 执行查询
		$pQuery = mysql_query($cSql, $this->mpLink);

		// 查询失败
		if (!$pQuery) {
			$this->halt('MySQL Query Error', $cSql);
		}
		
		return $pQuery;
	}

	/*
     * @desc    根据sql取第一条记录的数据
     * @para    string  $cSql    查询的sql语句
     * @return  bool/arr    有数据则返回记录的数据，无数据返回false（同方法fetchArray）
     */
	public function fetchFirstArray($cSql) {
		$pQuery = $this->query($cSql);
		
		$aResult = $this->fetchArray($pQuery);
		mysql_free_result($pQuery);
		
		return empty($aResult) ? array() : $aResult;
	}

    /*
     * @desc    根据sql取所有记录的数据，返回一个保存所有记录数据的二维数组
     * @para    string  $cSql    查询的sql语句
     * @return  bool/arr    有数据则返回所有记录数据的二维数组，无数据返回false
     */
	public function fetchAllArray($cSql) {
		$pQuery = $this->query($cSql);
		
        $aResults = array();
        while ($arr = $this->fetchArray($pQuery)) {
            $aResults[] = $arr;
        }
		mysql_free_result($pQuery);
		
        return empty($aResults) ? array() : $aResults;
	}
	
	/*
	 * 获取所有记录，并设置其中一个字段值作为数组键值，一般是id
	 */
	public function fetchAllArrayWithKey($cSql, $cKeyField) {
		$pQuery = $this->query($cSql);
		
        $aResults = array();
        while ($arr = $this->fetchArray($pQuery)) {
            $aResults[$arr[$cKeyField]] = $arr;
        }
		
		mysql_free_result($pQuery);
		
        return empty($aResults) ? array() : $aResults;
	}
	
	
	/**
	 * 获取多条记录同一个字段的值列表
	 */
	public function fetchFieldList($cSql, $cField) {
		$pQuery = $this->query($cSql);
		
		$aResults = array();
		while ($arr = $this->fetchArray($pQuery)) {
			$aResults[] = $arr[$cField];
		}
		mysql_free_result($pQuery);

		return $aResults;
	}
	
	/*
	 * fetchFirstField() - 根据sql取第一条记录第一个字段内容
	 *
	 * @since 1.0
	 *
	 * @param string $cSql 查询的sql语句
	 * @return mixed 
	 */
	public function fetchFirstField($cSql) {
		$pQuery = $this->query($cSql);
		
		if ($pQuery) {
			$aRow = mysql_fetch_row($pQuery);
			$mResult = $aRow[0];
		} else {
			$mResult = false;
		}
		mysql_free_result($pQuery);
		
		return $mResult;
	}

    /*
     * @desc    取结果集中的一条记录的数据
     * @para    resource result     $pQuery 结果集资源符（query方法返回的值）
     * @para    int     设置返回结果的类型 MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
     * @return  bool/arr    结果集中有数据，则返回记录的数组，结果中无数据返回false
     */
	public function fetchArray($pQuery, $cResultType = MYSQL_ASSOC) {
		return mysql_fetch_array($pQuery, $cResultType);
	}

	/**
	 * insert() - 插入一条记录
	 *
	 * @param string $cTable 
	 * @param array $aData 字段名对应字段值
	 * @return mixed(resource|boolean)
	 */
	public function insert($cTable, $aData) {
		$aFields = array_keys($aData);
		$cSql = "INSERT INTO `$cTable` (`" . implode('`,`',$aFields) . "`) VALUES ('".implode("','",$aData)."')";
		$pQuery = $this->query($cSql);
		
		return $pQuery ? $this->getInsertId() : false;
	}
	
	/**
	 * update() - 更新记录
	 * 
	 * @param string $cTable 
	 * @param array $aSet 字段名对应字段值
	 * @param string $cWhere 
	 * @return mixed(resource|boolean)
	 */
	public function update($cTable, $aSet, $cWhere){
		$aData = array();
		if (is_numeric($cWhere) || true === $cWhere) {
			die("UPDATE ALL ROWS IS NOT ALLOWED.");
		}
		foreach ( $aSet as $k => $v) {
			$aData[] = "`$k` = '$v'";
		}
		
		$cSql = "UPDATE `$cTable` SET " . implode( ', ', $aData ) . " WHERE $cWhere";
		
		return $this->query($cSql);
	}
	
	/**
	 * delete() - 删除记录
	 * 
	 * @param string $cTable 
	 * @param string $cWhere 
	 * @return mixed(resource|boolean)
	 */
	public function delete($cTable, $cWhere){
		if (is_numeric($cWhere) || true === $cWhere) {
			die("DELETE ALL ROWS IS NOT ALLOWED.");
		}
		
		$cSql = "DELETE FROM `$cTable` WHERE $cWhere";
		
		return $this->query($cSql);
	}
	
	/*
     * @desc    取得上一步 INSERT 操作产生的 ID
     * @para
     * @return  int     返回上一步 INSERT 操作产生的 ID
     */
	public function getInsertId() {
		return ($id = mysql_insert_id($this->mpLink)) >= 0 ? $id : $this->fetchFirstField("SELECT last_insert_id()");
	}

	/*
     * @desc    取得上一个sql操作产生的文本错误信息
     * @para
     * @return  string  返回上一个sql操作产生的文本错误信息
     */
	public function getError() {
		return (($this->mpLink) ? mysql_error($this->mpLink) : mysql_error());
	}

    /*
     * @desc    取得上一个sql操作中的错误信息的数字编码
     * @para
     * @return  int     返回上一个sql操作中的错误信息的数字编码
     */
	public function getErrno() {
		return intval(($this->mpLink) ? mysql_errno($this->mpLink) : mysql_errno());
	}

    /*
     * @desc    错误信息处理
     * @para    string  $cMessage    错误信息字符串
     * @para    string  $cSql        如果是运行sql出错，则传递sql语句参数
     * @return
     */
	public function halt($cMessage = '', $cSql = '') {
        $cSql = $cSql ? ", sql: ".$cSql : '';
        $cErr = ", ErrMsg: ".$this->getError() . " , ErrNo:" . $this->getErrno();
		
		die($cMessage.$cSql.$cErr);
	}
}

/*
 * Log:
 */
?>