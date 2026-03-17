<?php
class AccesoDB{
	private static $cnPDO = null;
	
	public static function getConnectionPDO(){
		if( self::$cnPDO == null){
			try{
				$url = 'mysql:host=localhost;dbname=unironlinecybercafe';
				$user = 'root';
				$pass = '';
				self::$cnPDO = new PDO($url,$user,$pass);
				self::$cnPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$cnPDO->SetAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
				self::$cnPDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				} catch (Exception $exc){
						throw $exc;
					}
			}
			return self::$cnPDO;
		}
	}
?>