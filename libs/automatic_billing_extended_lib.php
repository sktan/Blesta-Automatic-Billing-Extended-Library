<?php
/**
 * Automatic Billing Extended Library
 *
 * A library to integrate your Blesta non-merchant gateway with the automatic billing extended plugin
 *
 * @author Steven Tan
 * @license https://github.com/sktan/Blesta-Automatic-Billing-Extended-Library/blob/master/LICENSE The MIT License
 * @link https://github.com/sktan/Blesta-Automatic-Billing-Extended-Library
 */
class AutomaticBillingExtendedLibrary {
	/**
	 * @var string The gateway's identifier in order to identify gateway-specific billing methods
	 */
	private $gateway_identifier;
	/**
	 * @var string The gateway's hashed identifier in order to identify gateway-specific billing methods
	 */
	private $gateway_hash;
	/**
	 * @var string The name of the database table
	 */
	private $database_name = "nonmerchant_payment_tokens"; 
	
    /**
	 * Initializes the Automatic Billing Extended library.
	 *
	 * @param string $gateway_identifier The gateway's chosen identifier
	 * @param string $gateway_hash The gateway's chosen hash (if null, it will use the gateway_identifier's sha256 hash as the gateway hash)
	 */ 
	public function __construct($gateway_identifier, $gateway_hash=null) {
		Loader::loadComponents($this, array("Record"));
		
		$this->gateway_identifier = $gateway_identifier;
		
		if($gateway_hash == null) {
			$this->gateway_hash = hash("sha256", $this->gateway_identifier);
		}
		else {
			$this->gateway_hash = $gateway_hash;
		}
	}
	
	/**
	 * Checks whether the plugin is installed by running a simple fetch query on the "nonmerchant_payment_tokens"
	 *
	 * return bool Whether the plugin is installed or not
	 */
	public function checkPluginInstalled() {
		try{
			return $this->Record->select()->from($this->database_name)->numResults() !== false;
		}
		catch (Exception $ex) {
			return false;
		}
	}
	
	/**
	 * Checks whether the billing method already exists
	 * 
	 * @param int $blesta_customer_id The customer ID given by Blesta
	 * return bool Whether a billing method already exists for this customer (for the given gateway_identifier)
	 */
	public function billingMethodExists($blesta_customer_id) {
		return $this->Record->select()->from($this->database_name)->
			where('blesta_customer_id', '=', $blesta_customer_id)->
			where('gateway_name', '=', $this->gateway_identifier)->numResults() >= 1;
	}
	
	/**
	 * Add a new billing-method to be automatically billed
	 *
	 * @param int $blesta_customer_id The customer ID given by Blesta
	 * @param string $payment_token The payment token given by the payment gateway
	 * @param string $gateway_customer_id The customer ID given by the payment gateway
	 */
	public function addBillingMethod($blesta_customer_id, $payment_token, $gateway_customer_id) {
		if($this->billingMethodExists($blesta_customer_id) == false) {
			$this->Record->insert($this->database_name, array(
				'blesta_customer_id' => $blesta_customer_id,
				'gateway_customer_id' => $gateway_customer_id,
				'gateway_token' => $payment_token,
				'gateway_hash' => $this->gateway_hash,
				'gateway_name' => $this->gateway_identifier,
				'last_updated' => time(),
			));
		}
		// If this billing method already exists, then we will throw an error
		else {
			Throw new Exception("Billing method already exists. Please use the modifyBillingMethod method instead");
		}
	}
	
	/**
	 * Modifies an already-existing billing method for the given Blesta customer ID
	 *
	 * @param int $blesta_customer_id The customer ID given by Blesta
	 * @param string $new_payment_token The new payment token to be used
	 * @param string $new_gateway_customer_id The new gateawy customer ID
	 */
	public function modifyBillingMethod($blesta_customer_id, $new_payment_token, $new_gateway_customer_id) {
		if($this->billingMethodExists($blesta_customer_id)) {
			$this->Record->
				where('blesta_customer_id', '=', $client_id)->
				where('gateway_name', '=', $this->gateway_identifier)->
				update($this->database_name, array(
					'gateway_customer_id' => $new_gateway_customer_id,
					'gateway_token' => $new_payment_token,
					'last_updated' => time(),
				));
		}
		// If a billing method does not exist for the given Blesta customer ID, we will throw an error
		else {
			Throw new Exception("Billing method does not exist for given customer");
		}
	}
	
	/**
	 * Updates the billing method to the current time in order to become the "most recently used" billing method
	 * 
	 * @param int $blesta_customer_id The customer ID given by Blesta
	 */ 
	public function updateBillingMethodTime($blesta_customer_id) {
		if($this->billingMethodExists($blesta_customer_id)) {
			$this->Record->
				where('blesta_customer_id', '=', $client_id)->
				where('gateway_name', '=', $this->gateway_identifier)->
				update($this->database_name, array(
					'last_updated' => time(),
				));
		}
		// If a billing method does not exist for the given Blesta customer ID, we will throw an error
		else {
			Throw new Exception("Billing method does not exist for given customer");
		}
	}
	
	/**
	 * Removes the customers billing method from our table
	 *
	 * @param int $blesta_customer_id The customer ID given by Blesta
	 */
	public function removeBillingMethod($blesta_customer_id) {
		if($this->billingMethodExists($blesta_customer_id)) {
			$this->Record->from($this->database_name)->
				where('blesta_customer_id', '=', $blesta_customer_id)->
				where('gateway_name', '=', $this->gateway_identifier)->
				delete();
		}
		// If a billing method does not exist for the given Blesta customer ID, we will throw an error
		else {
			Throw new Exception("Billing method does not exist for given customer");
		}
	}
	
	/**
	 * Retrieves the billing information of a customer by their Blesta Customer ID
	 *
	 * @param int $blesta_customer_id The customer ID given by Blesta
	 */
	public function getBillingMethod($blesta_customer_id) {
		if($this->billingMethodExists($blesta_customer_id)) {
			$token_details = $this->Record->select()->from($this->database_name)->
				where('blesta_customer_id', '=', $blesta_customer_id)->
				where('gateway_name', '=', $this->gateway_identifier)->fetch();
		}
		// Return null if the billing method doesn't exist for the given Blesta Customer ID
		else {
			return null;
		}
	}
	
	public function removeAllBillingMethods() {
		$this->Record->from($this->database_name)->
			where('gateway_name', '=', $this->gateway_identifier)->
			delete();
	}
}