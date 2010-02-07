<?php
/**
 * @package PhotoQ
 */



class PhotoQBatchProcessor extends PhotoQObject
{
	/**
	 * The queued batch sets that are waiting to be executed
	 * @var array
	 */
	var $_batchSets;
	
	var $_id;
	
	var $_context;
	
	
	/**
	 * Reference to PhotoQDB singleton
	 * @var object PhotoQDB
	 */
	var $_db;
	
	/**
	 * Reference to ErrorStack singleton
	 * @var object PEAR_ErrorStack
	 */
	var $_errStack;
	
	
	var $_totalSets;
	
	function __construct(&$context, $id = NULL){
		//get the PhotoQ error stack for easy access
		$this->_errStack = &PEAR_ErrorStack::singleton('PhotoQ');
		
		$this->_db =& PhotoQSingleton::getInstance('PhotoQDB');
		if(is_null($id)){ //create a new empty BatchProcessor
			//PhotoQHelper::debug('creating empty BatchProcessor');
			$this->_batchSets = array();
			$this->_totalSets = 0;
			$this->_id = $id; //no id yet
		}else{//load it from the database
			//PhotoQHelper::debug('creating BatchProcessor no:' . $id);
			$this->_context =& $context;
			$this->load($id);
			//PhotoQHelper::debug('sets from db: ' . print_r($this->_batchSets,true));
		}
		
		//at the end of script execution we need to make the batch persistent
		//such that execution can go on in the next request
		register_shutdown_function(array(&$this, '_makeBatchPersistent'));
	}
	
	/**
	 * Load a stored batch from the database
	 * @param $id
	 * @return unknown_type
	 */
	function load($id){
		$this->_id = $id;
		$this->_batchSets = $this->_db->getBatchSets($id);
		$this->_totalSets = count($this->_batchSets);
	}
	
	/**
	 * Register a new batch set of operations with the BatchProcessor
	 * @param $set object PhotoQBatchSet to be registered
	 * @return  object PhotoQStatusMessage indicating success or failure of registration.
	 */
	function registerSet(&$set){
		global $wpdb;
		$this->_batchSets[] =& $set;
		$this->_totalSets++;
		//if we do not yet have an id we will get one now
		if(is_null($this->_id)){
			//add to database
			if($id = $this->_db->insertBatch())
				$this->_id = $id;
			else{
				$this->_errStack->push(PHOTOQ_BATCH_REGISTER_FAILED, 'error');
				return false;
			}
		}
		//PhotoQHelper::debug('total sets registered: '. count($this->_batchSets));
		return true;
	}
	
	/**
	 * Indicates whether we have a batch to run
	 * @return boolean
	 */
	function haveBatch(){
		return !is_null($this->_id);
	}
	
	function getId(){
		return $this->_id;
	}
	
	/**
	 * Write current batch to database. We need it to be persistent such that
	 * execution of batch operations can continue at next execution.
	 * @return unknown_type
	 */
	function _makeBatchPersistent(){
		if(!is_null($this->_id)){
			$this->_db->updateBatch($this->_id, $this->_batchSets);
		}
	}
	
	function process(){
		//PhotoQHelper::debug('enter process()');
		//PhotoQHelper::debug('sets to be done: '. print_r($this->_batchSets,true));
		$result = new PhotoQBatchResult();
		if(empty($this->_batchSets)){
			//PhotoQHelper::debug('done');
			$result->done();
		}else{
			//PhotoQHelper::debug('processing batchSets');
			while($bs =& array_shift($this->_batchSets)){
				$currentResult = $bs->process($this->_context);

				if($currentResult->getError()){
					$result->setError(__('An error occured in batch processing.','PhotoQ'));
					break;
				}
				if(!$currentResult->isDone()){
					//it returned incomplete -> timer expired
					$result->setPercentage(1.0 - (count($this->_batchSets) + 1.0 - $currentResult->getPercentage()) / $this->_totalSets);
					// put it back and return
					array_unshift($this->_batchSets,$bs);
					break;
				}
			}
			//are we done with this batch?
			if(count($this->_batchSets) == 0){
				//yes we are -> remove from db
				$this->_db->deleteBatch($this->_id);
				$this->_id = null;
				$result->done();
			}
		}
		//PhotoQHelper::debug('leave process()');
		return $result;
	}

}

class PhotoQBatchSet extends PhotoQObject
{
	
	/**
	 * The operations that form this batch set
	 * @var array
	 */
	var $_operations;
	var $_totalOps;
	
	function __construct($ops){
		$this->_operations = $ops;
		$this->_totalOps = count($this->_operations);
	}
	
	
	
	function process(&$context){
		
		$result = new PhotoQBatchResult();
		$timer =& PhotoQSingleton::getInstance('PhotoQTimers');
		while($timer->read('batchProcessing') < 1000){	
			$op =& array_shift($this->_operations);
			
			if(is_null($op)){//we are done
				$result->done();
				break;
			}
			
			// Process the current operation.
			list($functionName, $args, $state) = $op;
			$status = call_user_func_array(array(&$context,$functionName), array_merge($args, array(&$state)));
			
			if($status->hasError()){
				$result->setError(__('An error occured.', 'PhotoQ'));
				break;
			}
			elseif($status->isDone()){
				//that's what is remaining to be done
				$result->setPercentage(1.0 - count($this->_operations)/$this->_totalOps);
			}else{
				$result->setPercentage(1.0 - (count($this->_operations) + 1.0 - $status->getPercentage()) / $this->_totalOps);
					
				//we are not yet done, put it back with updated state
				array_unshift($this->_operations,array($functionName,$args,$status->getState()));
			}
		}
		
		return $result;
	}
	
	
}

class PhotoQBatchResult extends PhotoQObject
{
	var $_percentage; 
	var $_error;
	var $_message;
	
	
	function __construct($perc = 0.0){
		$this->_percentage = $perc;	
		$this->_error = false;
		$this->_message = '';
	}
	
	function getError(){
		return $this->_error;
	}
	
	function getPercentage(){
		return $this->_percentage;
	}
	
	function getMessage(){
		return $this->_message;
	}
	
	//we can only reach 100% via the done() method
	function setPercentage($val){
		$this->_percentage =  (1.0 > $val) ? $val : 0.99;
	}
	
	function setMessage($msg){
		$this->_message = $msg;
	}
	
	function done(){
		$this->_percentage = 1.0;
	}
	
	function isDone(){
		return $this->_percentage >= 1.0;
	}
	
	function setError($msg){
		$this->_error = true;
		$this->_message = $msg;
	}
	
}


class PhotoQBatchStatus extends PhotoQObject
{
	var $_result;
	var $_state; 
	
	function __construct($perc = 0.0, $state = array()){
		$this->_result = new PhotoQBatchResult($perc);
		$this->_state = $state;	
	}
	
	function hasError(){
		return $this->_result->getError();
	}
	
	function isDone(){
		return $this->_result->getPercentage() >= 1.0;	
	}
	
	function getPercentage(){
		return $this->_result->getPercentage();
	}
	
	function getState(){
		return $this->_state;
	}
	
}


?>