<?php

//Transaction.class.php
/**
 * This holds the geoTransaction class.
 *
 * @package System
 * @since Version 4.0.4
 */


require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

/**
 * This is the object used for a transaction in the order and invoice system.
 *
 * A transaction will be assigned to an invoice, that invoice will be assigned
 * to an order that the invoice is "paying" for.
 *
 * There will be many transactions to one invoice.  Since this is an object
 * that represents a single transaction in the system, there can be many
 * instances of it.  If you are creating a new transaction, you would start
 * off by creating a new geoTransaction object, then assigning settings for it.
 *
 * A transaction with a negative "amount" means it is a charge, that is what
 * is "owed" from the "buyer" to the seller (which in most cases will be the "site").  A positive
 * amount represents a payment from the "buyer" to the "seller" (seller and buyer
 * are set for the invoice that the transaction is attached to)
 *
 * @package System
 * @since Version 4.0.4
 */
class geoTransaction
{
    /**
     * Used internally
     * @internal
     */
    private $id, $invoice, $recurringBilling, $amount, $description, $date, $user, $gateway, $gatewayTransaction;
    /**
     * Status of transaction.  Either 1 for verified, or 0 for "do not apply to invoice yet".
     *
     * @var int
     */
    private $status;
    /**
     * The registry
     * @var geoRegistry
     */
    private $registry;
    /**
     * Array of all the transactions retrieved for page load
     * @var array
     */
    private static $transactions;
    /**
     * Used internally to remember whether there has been changes to the order since it was last
     *  serialized.  If there is not changes, when serialize is called, nothing will be done.
     *
     * @var boolean
     */
    private $_pendingChanges;

    /**
     * Constructor, sets up stuff
     *
     */
    public function __construct()
    {
        //set status to be on by default
        $this->status = 1;

        //set up blank registry
        $this->registry = new geoRegistry();
        $this->registry->setName('transaction');
        $this->_pendingChanges = true;
    }

    /**
     * Get the ID for this transaction.  Returns 0 if this is a new transaction w/o an ID yet.
     *
     * @return int
     *
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID for this transaction.  Only used internally.
     *
     * @param int $val
     */
    private function setId($val)
    {
        $this->touch(); //there are now pending changes
        $this->id = $val;
    }

    /**
     * Gets the invoice object that this transaction is attached to, or null if not attached to
     * anything
     *
     * @return geoInvoice|null
     */
    public function getInvoice()
    {
        if (!is_object($this->invoice) && $this->invoice > 0) {
            $this->invoice = geoInvoice::getInvoice($this->invoice);
        }
        return $this->invoice;
    }
    /**
     * Sets the invoice this transaction is attached to, can be attached to object or by invoice ID.
     *
     * @param geoInvoice|int $invoice Either the invoice object, or the invoice ID.
     */
    public function setInvoice($invoice)
    {
        if (!is_object($invoice)) {
            $invoice = intval($invoice);
        }
        if ($this->invoice === $invoice) {
            //no changes...
            return;
        }
        $this->invoice = $invoice;

        $this->touch(); //there are now pending changes
    }

    /**
     * Gets the recurringBilling object that this transaction is attached to, or null if not attached to
     * recurringBilling
     *
     * @return geoRecurringBilling|null
     * @since Version 4.1.0
     */
    public function getRecurringBilling()
    {
        if (!is_object($this->recurringBilling) && $this->recurringBilling > 0) {
            $this->recurringBilling = geoRecurringBilling::getRecurringBilling($this->recurringBilling);
        }
        return $this->recurringBilling;
    }
    /**
     * Sets the recurring billing object this transaction is attached to, can
     * be attached to object or by recurring billing ID.
     *
     * @param geoRecurringBilling|int $recurringBilling Either the recurring billing
     *   object, or the recurring billing ID.
     * @since Version 4.1.0
     */
    public function setRecurringBilling($recurringBilling)
    {
        if (!is_object($recurringBilling)) {
            $recurringBilling = intval($recurringBilling);
        }
        if ($this->recurringBilling === $recurringBilling) {
            //no changes...
            return;
        }
        $this->recurringBilling = $recurringBilling;

        $this->touch(); //there are now pending changes
    }

    /**
     * Get the amount for this transaction
     *
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }
    /**
     * Set the amount for this transaction
     *
     * @param double $amount
     */
    public function setAmount($amount)
    {
        //force to be float 4 decimal points, to fix any floating point bugs
        $amount = round(floatval($amount), 4);
        if ($this->amount === $amount) {
            //nothing has changed
            return;
        }
        $this->amount = $amount;

        $this->touch(); //there are now pending changes
    }

    /**
     * Get the description for this transaction
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description for this transaction
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        //force it to be a string
        $description = '' . $description;
        if ($this->description === $description) {
            //already set to this!  no changes needed
            return;
        }

        $this->description = $description;
        $this->touch(); //there are now pending changes
    }

    /**
     * Get the date for this transaction
     *
     * @return int unix timestamp
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the date for this transaction
     *
     * @param int $timestamp unix timestamp
     */
    public function setDate($timestamp)
    {
        //clean it, make sure it's an int.
        $timestamp = intval($timestamp);

        if ($this->date === $timestamp) {
            //no changes needed...
            return;
        }

        $this->date = $timestamp;
        $this->touch(); //there are now pending changes
    }

    /**
     * Get the user (id) for this transaction, or 0 if created by system.
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user (id) for this transaction (0 if created by system)
     *
     * @param int $userId
     */
    public function setUser($userId)
    {
        //clean value, make sure it's an int.
        $userId = intval($userId);
        if ($this->user === $userId) {
            //no changes needed
            return;
        }

        $this->user = $userId;

        $this->touch(); //there are now pending changes
    }


    /**
     * Get the gateway object for this transaction
     *
     * @return geoPaymentGateway
     */
    public function getGateway()
    {
        if (!is_object($this->gateway) && strlen($this->gateway) > 0) {
            //if it's set to the gateway name, get the gateway object for that name.
            $this->gateway = geoPaymentGateway::getPaymentGateway($this->gateway);
        }
        return $this->gateway;
    }

    /**
     * Set the gateway used for this transaction.
     *
     * @param geoPaymentGateway|string $paymentGateway Either the payment gateway
     *  object, or the payment gateway's unique name identifier.
     */
    public function setGateway($paymentGateway)
    {
        //make sure it is a valid gateway
        if (!is_object($paymentGateway) && strlen($paymentGateway) > 0 && !geoPaymentGateway::gatewayExists($paymentGateway)) {
            //gateway not valid!
            return ;
        }
        if ($paymentGateway === $this->gateway) {
            //no changes..
            return;
        }
        $this->gateway = $paymentGateway;

        $this->touch(); //there are now pending changes
    }

    /**
     * Can be used by gateways, to have an easily searchable field that goes with the transaction
     * id that the gateway expects.
     *
     * Max length is 255, but if a gatway needs a longer ID length it
     * can store the first 255 chars here to be able to do a quick search, then store the full transaction
     * id in the registry as trans_id_full or something.
     *
     * @return string
     */
    public function getGatewayTransaction()
    {
        return $this->gatewayTransaction;
    }
    /**
     * Can be used by gateways, to have an easily searchable field that goes with the transaction
     * id that the gateway expects.
     *
     * Max length is 255, but if a gatway needs a longer ID length it
     * can store the first 255 chars here to be able to do a quick search, then store the full transaction
     * id in the registry as trans_id_full or something.
     *
     * @param string $value
     */
    public function setGatewayTransaction($value)
    {
        $value = '' . $value;//make sure it's a string

        if ($this->gatewayTransaction === $value) {
            //no changes!
            return;
        }

        $this->gatewayTransaction = $value;
        $this->touch(); //there are now pending changes
    }

    /**
     * Returns status of invoice, either 1 for status is cool, 0 for "do not apply to invoice yet"
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * Sets status of invoice, either 1 for status is cool, 0 for "do not apply to invoice yet"
     *
     * @param int $status Either a 1 or 0, this is only an on/off status.
     */
    public function setStatus($status)
    {
        //status is 1 or 0.
        $status = ($status) ? 1 : 0;

        if ($this->status === $status) {
            //No changes!
            return;
        }

        $this->status = $status;

        $this->touch(); //there are now pending changes
    }

    /**
     * Gets the specified item from the registry, or if item is one of the "main" items it gets
     * that instead.
     *
     * @param string $item
     * @param mixed $default What to return if the item is not set.
     * @return Mixed the specified item, or false if item is not found.
     */
    public function get($item, $default = false)
    {
        if (method_exists($this, 'get' . ucfirst($item))) {
            $methodName = 'get' . ucfirst($item);
            return $this->$methodName();
        }

        return $this->registry->get($item, $default);
    }

    /**
     * Sets the given item to the given value.  If item is one of built-in items, it sets that instead
     * of something from the registry.
     *
     * @param string $item
     * @param mixed $value
     */
    public function set($item, $value)
    {
        if (method_exists($this, 'set' . ucfirst($item))) {
            $methodName = 'set' . ucfirst($item);
            return $this->$methodName($value);
        }
        if (!is_object($this->registry)) {
            throw new Exception('WTF??');
        }
        $this->touch(); //there are now pending changes

        return $this->registry->set($item, $value);
    }

    /**
     * Gets the transaction specified by the ID and returns the geoTransaction object for
     * that transaction, or a new blank transaction item if the id is 0 or not a valid ID.
     *
     * @param int|string $identify Either int for ID of transaction, or string for gateway_transaction
     *  match instead.  If 0 or invalid, Object returned is for a new blank transaction.
     * @return geoTransaction
     */
    public static function getTransaction($identify = 0)
    {
        //see if transaction exists in array of transactions.
        if (is_numeric($identify) && isset(self::$transactions[$identify])) {
            return self::$transactions[$identify];
        }
        //see if it is a gateway
        if (isset(self::$transactions['gatewayTransaction'][$identify])) {
            return self::$transactions['gatewayTransaction'][$identify];
        }
        //see if transaction exists in db
        $transaction = new geoTransaction();
        //Note: unserialize method should add itself to the static array of transactions itself.
        $transaction->unSerialize($identify);

        //If they specified 0 or an invalid ID, they will get a blank transaction back
        //from the unSerialize function.
        return $transaction;
    }

    /**
     * Serializes the current transaction (saves changes in the database, or creates new transaction if the
     *  id is not set.  If it is a new transaction, it will set the transaction ID after it has been
     *  inserted into the database.
     *
     * Also automatically serializes any objects attached to it that are not already serialized,
     * for instance the registry object attached to this transaction.
     *
     */
    public function serialize()
    {
        //trigger_error('DEBUG TRANSACTION: Top of serialize()');
        if (!$this->_pendingChanges) {
            //no pending changes, no need to serialize.
            return;
        }
        $db = DataAccess::getInstance();

        $id = $this->id;
        $invoice = intval((is_object($this->invoice)) ? $this->invoice->getId() : $this->invoice);
        $recurringBilling = intval((is_object($this->recurringBilling)) ? $this->recurringBilling->getId() : $this->recurringBilling);
        //be sure to round the amount to kill any potential floating point problems
        $amount = round(floatval($this->amount), 4);
        $description = geoString::toDB($this->description);
        $date = intval($this->date);
        $user = intval($this->user);
        $gateway = '' . ((is_object($this->gateway)) ? $this->gateway->getName() : $this->gateway);
        $gateway_transaction = '' . $this->gatewayTransaction;
        $status = intval($this->status);
        if (isset($this->id) && $this->id > 0) {
            //update info
            $sql = "UPDATE " . $db->geoTables->transaction . " SET `invoice` = ?, `recurring_billing` = ?, `amount` = ?, `description` = ?, `date` = ?, `user` = ?, `gateway` = ?, `gateway_transaction` = ?, `status` = ? WHERE `id`=? LIMIT 1";
            $query_data = array($invoice, $recurringBilling, $amount, $description, $date, $user, $gateway, $gateway_transaction, $status, $id);

            $result = $db->Execute($sql, $query_data);
            if (!$result) {
                trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: ' . $db->ErrorMsg());
                return false;
            }
        } else {
            //Insert into DB
            $sql = "INSERT INTO " . $db->geoTables->transaction . " (`id`, `invoice`, `recurring_billing`, `amount`, `description`, `date`, `user`, `gateway`, `gateway_transaction`, `status`) 
					VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $query_data = array($invoice, $recurringBilling, $amount, $description, $date, $user, $gateway, $gateway_transaction, $status);


            $result = $db->Execute($sql, $query_data);
            if (!$result) {
                trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: ' . $db->ErrorMsg());
                return false;
            }
            //set id
            $this->id = $db->Insert_Id();
            //add to transactions registry
            self::$transactions[$this->id] = $this;
            if (strlen($this->gatewayTransaction) > 0) {
                //add to transactions by gateway transaction
                $already_set = false;
                if (!isset(self::$transactions['gatewayTransactions']) || !is_array(self::$transactions['gatewayTransactions'])) {
                    self::$transactions['gatewayTransactions'] = array();
                }
                self::$transactions['gatewayTransactions'][$this->gatewayTransaction][$this->id] = $this;
            }
        }

        //Serialize transaction registry
        if (!isset($this->registry) || !is_object($this->registry)) {
            $this->registry = new geoRegistry();
        }
        $this->registry->setId($this->id);
        $this->registry->setName('transaction');//make sure name did not get lost or something
        $this->registry->serialize();//serialize registry

        //we just serialized, so there are no longer pending changes.
        $this->_pendingChanges = false;
    }

    /**
     * Unserializes the object for the given ID and applies parameters to this object.
     *
     * @param int|string $id Either the id for the transaction, or the gatewayTransaction string.
     */
    public function unSerialize($id = 0)
    {
        if (!$id && isset($this->id)) {
            //id set using setId()
            $id = $this->id;
        } elseif (!$id && isset($this->gatewayTransaction)) {
            //allow to unserialize by the gateway transaction string "transparently"
            $id = $this->gatewayTransaction;
        }
        if (!$id) {
            //can't unserialize without an id!
            return;
        }
        //figure out what column to search
        if (is_numeric($id) && $id > 0) {
            $column = '`id`';
        } else {
            //if it's in gateway_transaction, it MUST be a string!  If you want to use this and store int value,
            //prepend it with a string like myGateway_### or something
            $column = '`gateway_transaction`';
        }

        $db = DataAccess::getInstance();

        //Get the main data

        $sql = "SELECT * FROM " . $db->geoTables->transaction . " WHERE $column = ? LIMIT 1";
        $result = $db->Execute($sql, array($id));
        if (!$result) {
            trigger_error('ERROR SQL: ERror unserializing transaction: ' . $db->ErrorMsg());
            return ;
        }
        if ($result->RecordCount() != 1) {
            //nothing by that id...
            return ;
        }
        //reset all settings except for ID
        self::$transactions = array();
        $settings = get_class_vars(__class__);
        $skip_vars = array('id','transactions');
        foreach ($settings as $var => $default_val) {
            if (!in_array($var, $skip_vars)) {
                $this->$var = $default_val;
            }
        }
        $row = $result->FetchRow();
        $fromDb = array('description');

        $translation = array (
            'gateway_transaction' => 'gatewayTransaction',
            'recurring_billing' => 'recurringBilling',
        );
        foreach ($row as $key => $value) {
            if (!is_numeric($key)) {
                //only process non-numeric rows
                if (isset($translation[$key])) {
                    $key = $translation[$key];
                }

                if (in_array($key, $fromDb)) {
                    $value = geoString::fromDB($value);
                }
                $this->$key = $value;
            }
        }
        if (!$this->id) {
            //something went wrong with unserializing main values
            return ;
        }
        //add to transaction list
        self::$transactions[$this->id] = $this;
        if (strlen($this->gatewayTransaction) > 0) {
            //add to transactions by gateway transaction
            $already_set = false;
            if (!isset(self::$transactions['gatewayTransactions']) || !is_array(self::$transactions['gatewayTransactions'])) {
                self::$transactions['gatewayTransactions'] = array();
            }
            self::$transactions['gatewayTransactions'][$this->gatewayTransaction][$this->id] = $this;
        }
        //Unserialize registry
        if (!is_object($this->registry)) {
            $this->registry = new geoRegistry();
        }
        $this->registry->setName('transaction');
        $this->registry->setId($this->id);
        $this->registry->unSerialize();
        //we just serialized, so there are no longer pending changes.
        $this->_pendingChanges = false;
    }

    /**
     * Alias of geoTransaction::serialize() - see that method for details.
     *
     */
    public function save()
    {
        return $this->serialize();
    }

    /**
     * Static function that removes a transaction as specified by ID.
     *
     * @param int $id
     */
    public static function remove($id)
    {
        $id = intval($id);
        if (!$id) {
            return false;
        }
        $db = DataAccess::getInstance();

        //first, remove the main transaction
        $sql = 'DELETE FROM ' . $db->geoTables->transaction . ' WHERE `id` = ?';
        $result = $db->Execute($sql, array($id));
        if (!$result) {
            trigger_error('ERROR SQL: Error trying to remove order for id: ' . $id . ' - error: ' . $db->ErrorMsg());
            //do not hault on db error, keep going
        }
        if (isset(self::$transactions[$id])) {
            //remove it from list of transactions if it is there
            unset(self::$transactions[$id]);
        }

        //last, remove all registry for this transaction
        geoRegistry::remove('order', $id);
    }

    /**
     * Use when this object, or one of it's child objects, has been changed, so that when it is serialized, it
     * will know there are changes that need to be serialized.
     *
     * This also recursevly touches all "parent" objects that this one is attached to.
     *
     * Note that this is automatically called internally when any of the set functions are used.
     *
     */
    public function touch()
    {
        $this->_pendingChanges = true; //there are now pending changes

        //touch anything this object is "attached" to
        if (is_object($this->invoice)) {
            $this->invoice->touch();
        }
        if (is_object($this->recurringBilling)) {
            $this->recurringBilling->touch();
        }
    }
}
