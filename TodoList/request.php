<?php
/* 
 * Class request : send request to HandleListStorageDB object and send response to APIController
 * Author : Severine Donnay
 * POST VALUES ARE : [ACTIONTYPE] and FIELDS required for Actiontype
 * Actiontype is Type of Request : all types are defined in actionType class
 * 
 * [ACTIONTYPE] = CREATE_ACTION | MODIFY_ACTION | DELETE_ACTION | ADDITEM_ACTION | DELETEITEM_ACTION | GETLIST_ACTION | MODIFYITEM_ACTION
 *  
 */
namespace CakeMailTest\TodoList;
final class request {
	
	private $requestArr =null;
	private $actionType =null;
	private $handleLists;
	private $type_content = 'text/html'; 
	
	public function __construct($requestArr,$actionType) {
		$this->requestArr = $requestArr;
		$this->actionType = $actionType;
		
	}
	
	public function getResponse() {
		
		/*
		 *  call checkform in validRequest class to check if all required fields are in the request with format
		 *  Important to add validRequest traitment when add a new Action in API
		 */
		
		if (!validRequest::checkFormat($this->requestArr,$this->actionType)) throw new ExceptionTodoList (errorApi::POSTFORMAT_ERR); 
	    $this->handleLists = new handleListsStorageDB(DB_NAME,DB_USER,DB_PWD,DB_HOST); // objet to manage ToLists and storage to DB
	     
         switch ($this->requestArr['ACTIONTYPE']) {
				
			case actionType::CREATE_ACTION : // create new listname : 'NAMELIST' is required field
				$func_name='create';
				$args = array_values(array_intersect_key($this->requestArr, array_flip(array('NAMELIST')))); 
				break;

			case actionType::MODIFY_ACTION : // modify listname : 'NAMELIST','NEWNAMELIST' are required fields
				$func_name='modifyLst';
				$argsArr=array_intersect_key($this->requestArr, array_flip(array('NAMELIST','NEWNAMELIST')));
				ksort($argsArr); // args order by alphaNum : NAMELIST, NEWNAMELIST :: same order than arguments in modifyLst function
				$args = array_values($argsArr); 
				break;	
				
			case actionType::DELETE_ACTION : // delete new listname : 'NAMELIST' is required field
				$func_name='delete';
				$args = array_values(array_intersect_key($this->requestArr, array_flip(array('NAMELIST')))); 
				break;
				
			case actionType::ADDITEM_ACTION : // add new item to listname : 'NAMELIST','CONTENT','STATUS' is required field 
				$func_name='addItem';
				$argsArr = array_intersect_key($this->requestArr, array_flip(array('CONTENT','STATUS')));
				ksort($argsArr); // args by alphaNum : CONTENT, STATUS :: same order than item properties object
				$args = array($this->requestArr['NAMELIST'],$argsArr);
				break;	
				
			case actionType::DELETEITEM_ACTION : // delete items to listname : 'NAMELIST','CONTENT' OR 'STATUS' are required field :: Delete all items with the speciic CONTENT or specific STATUS in list ( several items can have same name)
				$func_name='delItem';
				$argsArr = array_intersect_key($this->requestArr, array_flip(array('CONTENT','STATUS')));
				ksort($argsArr); // args by alphaNum : CONTENT, STATUS :: same order than item properties object
				$args = array($this->requestArr['NAMELIST'],$argsArr);
				break;
			case actionType::GETLIST_ACTION : // delete items to listname : 'NAMELIST','CONTENT' OR 'STATUS' are required field :: Delete all items with the speciic CONTENT or specific STATUS in list ( several items can have same name)
				$func_name='getItem';
				$args = array($this->requestArr['NAMELIST'],array_intersect_key($this->requestArr, array_flip(array('STATUS'))));
				$this->type_content='text/xml';
				break;	
			case actionType::MODIFYITEM_ACTION : // modify items to listname : 'NAMELIST','CONTENT', 'STATUS' are required field
				$func_name='modify';
				$argsArr = array_intersect_key($this->requestArr, array_flip(array('CONTENT','STATUS','OLDCONTENT')));
				ksort($argsArr); // args by alphaNum : CONTENT, STATUS :: same order than item properties object
				$args = array($this->requestArr['NAMELIST'],$argsArr);
				break;				
         }		

         if (DEBBUGAGE_MODE) var_dump($args);
         $method = array ($this->handleLists,$func_name); //  method with args to manage request
       	 return self::sendResponse(call_user_func_array($method,$args),$this->type_content); // call this method and receive return response
	}

	 
	 private function sendResponse($content,$responseType) { 
	 	
	 	$Response = new response($content,$responseType);
	 	return $Response->send();
	 	
	 } 
	 
	 
}
			

?>