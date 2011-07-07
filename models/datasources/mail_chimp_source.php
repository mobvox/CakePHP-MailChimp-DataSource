<?php
/**
 * MailChimp
 * 
 * Implementação da API do MailChimp para o CakePHP.
 *
 * @package mailchimp.models.datasources
 * @author Daniel Luiz Pakuschewski
 * @author MobVox Soluções Digitais - www.mobvox.com.br
 * @version 0.1
 **/

App::import('Vendor', 'MailChimp.MCAPI', array('file' => 'MCAPI.class.php'));
class MailChimpSource extends DataSource {
	
	private $MailChimp;
	
	/**
	* Os schemas do data source.
	*/
	protected $_schema = array(
		'list' => array()
	);
	
	public function __construct($config) {
		extract($config);
		if(!isset($apiKey)){
			trigger_error(__('É necessário informar sua chave de API no arquivo config/database.php', true), E_USER_ERROR);
		}
		if(!isset($secure)){
			$secure = false;
		}
		$this->MailChimp = new MCAPI($apiKey, $secure);
		if(isset($timeout)){
			$this->MailChimp->setTimeout($timeout);
		}
	}
	
	public function listSources() {
		return array('list');
	}
	
	public function describe() {
		return $this->_schema['list'];
	}
	
	public function create($model, $fields = array(), $values = array()) {
		
	}
	
	public function read($model, $queryData = array()) {
		return $this->__readList($model, $queryData);
	}
	
	public function update($model, $fields = array(), $values = array()) {
		
	}
	
	public function delete($model, $id) {
		
	}
	
	private function __readList($model, $queryData){
		if(isset($model->id)){
			$id = $model->id;
		}
		$since = NULL;
		$start = 0;
		$limit = 100;
		$status='subscribed';
		
		if(!empty($queryData['conditions'])){
			extract($queryData['conditions']);
		}
		if (!empty($queryData['limit'])) {
			$limit = $queryData['limit'];
		}
		if ($queryData['page'] > 1) {
			$start = $page - 1; //MailChimp start at page zero.
		}

		$data = $this->MailChimp->listMembers($id, $status, $since, $start, $limit);
		$data = array_chunk($data['data'], 50);
		$result = array();
		foreach($data as $emails){
			$emails = Set::extract('/email', $emails);
			$membersInfo = $this->MailChimp->listMemberInfo($id, $emails);
			foreach($membersInfo['data'] as $member){
				array_push($result, array($model->alias => $member));
			}
		}
		return $result;
	}
		
}