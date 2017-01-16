<?php 

namespace Wager\Controller;

use Wager\Controller\AbstractWagerController;
use Zend\Db\Adapter\AdapterInterface;
use Wager\Form\DepositForm;
use Wager\Form\WithdrawForm;
use Wager\Models\Deposit;
use Wager\Models\Withdraw;
use Application\Models\The\TheCredits;
use Application\Models\The\TheTransactions;
use Application\Models\The\TheWithdrawal;
use Application\Models\Paypal\Business;
use Zend\View\Model\JsonModel;
use Application\Models\Pusher\Pusher;

class WalletController extends AbstractWagerController
{
  
  protected $_dbAdapter;


  public function __construct(AdapterInterface $dbAdapter) 
  {
      parent::__construct($dbAdapter);
  }

  public function indexAction()
  {
    $paypalReturn = $this->flashMessenger()->setNamespace('paypalReturn')->getMessages();
    if($paypalReturn) {
        $this->_view->setVariable('messages', $paypalReturn[0]['message']);
    }  
    
    return $this->_view;
  }
  
  
  public function depositpaypalAction()
  {
      $this->validateUser();

      $request = $this->getRequest();
      if ($request->isPost()) {
            $DepositForm = new DepositForm();
            $model = new Deposit();
            $DepositForm->setInputFilter($model->getInputFilter());
            $DepositForm->setData($request->getPost());
            $deposit =array();
            if ($DepositForm->isValid()) {
                $data = $DepositForm->getData();

                //Paypal redirect Payment Page //
                $uri = $this->getRequest()->getUri();
                $scheme = $uri->getScheme();
                $myhost = $uri->getHost();
                $urlSuccessful = $scheme.'://'.$myhost .'/wager/wallet/paypalsuccess/';
                $urlCancel = $scheme.'://'.$myhost .'/wager/wallet/paypalcancel/';
                //----
                $business = new Business();
                $paypalRequest = $business->getPaypalRequest();
                $item = $business->getDetail($data['deposit']);
                $this->flashMessenger()->setNamespace('flashData')->addMessage($item);

                $paymentDetails = new \SpeckPaypal\Element\PaymentDetails([
                    'amt' => $item->getAmt()
                ]);
                $paymentDetails->setItems([ $item ]);

                $express = new \SpeckPaypal\Request\SetExpressCheckout(['paymentDetails' => $paymentDetails]);
                $express->setReturnUrl($urlSuccessful);
                $express->setCancelUrl($urlCancel);

                $response = $paypalRequest->send($express);
                // Check if we are using a sandbox
                $host = ( strpos($paypalRequest->getConfig()->getEndPoint(), 'sandbox') !== false ) ? 'sandbox.paypal' : 'paypal';

                if($response->isSuccess()) {
                    // Redirect to Paypal!
                    return $this->redirect()->toUrl(sprintf('https://www.%s.com/cgi-bin/webscr?cmd=_express-checkout&token=%s', $host, $response->getToken()));              
                }            
            }  
        } 
        
        $paypalReturn['message'] = 'Please fill out all fields correctly.';
        $this->flashMessenger()->setNamespace('paypalReturn')->addMessage($paypalReturn);
                  
        return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'wallet'));
  }
  
    public function paypalsuccessAction()
    {
        // PayPal Request
        $business = new Business();
        $paypalRequest = $business->getPaypalRequest();
                
                
        // GetExpressCheckoutDetails
        $details = new \SpeckPaypal\Request\GetExpressCheckoutDetails(array(
            'token' => $this->params()->fromQuery('token')
        ));

        // Do a request
        $response = $paypalRequest->send($details);

        // Paypal PaymentItem
        $flashData = $this->flashMessenger()->setNamespace('flashData')->getMessages();
        if(!$flashData) { 
            $paypalReturn['message'] = 'Paypal Transaction not successfully process.';
            $this->flashMessenger()->setNamespace('paypalReturn')->addMessage($paypalReturn);

            return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'wallet'));
         }
         
        $item = $flashData[0];
        // Set the PaymentDetails (MaxCost of all Items)
        $paymentDetails = new \SpeckPaypal\Element\PaymentDetails([
            'amt' => $item->getAmt()
        ]);

        // Set the items
        $paymentDetails->setItems([ $item ]);

        // DoExpressCheckoutPayment
        $captureExpress = new \SpeckPaypal\Request\DoExpressCheckoutPayment([
            'token'             => $this->params()->fromQuery('token'),
            'payerId'           => $response->getPayerId(),
            'paymentDetails'    => $paymentDetails
        ]);

        $response = $paypalRequest->send($captureExpress);
        if($response->isSuccess()) {
            // Now increase player credits //
            $theCredits = new TheCredits($this->_dbAdapter);

            $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
            if($myCredits) {
                $myCredits = get_object_vars($myCredits);
            }
            
            $availableCredits = $theCredits->adjustUserCreditsDeposit($myCredits,$item->getAmt(),
                         $this->getUserSession()->user->id);
            
            
            // Create transaction
            $paymentInfo = $response->getPaymentInfo();
            $trandata = Array(
                'description' => 'Paypal Express Checkout Payment',
                'paymentId' => $paymentInfo[0]['TRANSACTIONID'],
                'paymentmethod' => 'Paypal',
//                'feeamt' => $paymentInfo[0]['FEEAMT'],
                'type' => 'D',
                'amount'=> $item->getAmt(),
                'balance' => $availableCredits
            );
            $this->saveTransaction($trandata, 'd');
            
            $paypalReturn['message'] = 'Paypal Transaction successfully process.';
            $this->flashMessenger()->setNamespace('paypalReturn')->addMessage(true);

        } else {
            $paypalReturn['message'] = 'Paypal Transaction not successfully process.';
            $this->flashMessenger()->setNamespace('paypalReturn')->addMessage($paypalReturn);
        }
        return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'wallet'));
    }
    
    public function paypalcancelAction()
    {
        $paypalReturn['message'] = 'Paypal Transaction Canceled.';
        $this->flashMessenger()->setNamespace('paypalReturn')->addMessage($paypalReturn);
                  
        return $this->redirect()->toRoute('wager', array('action' => 'index', 'controller' => 'wallet'));
    }
    
    public function depositccAction()
    {
      $this->validateUser();

      $request = $this->getRequest();
      if ($request->isPost()) {
            $DepositForm = new DepositForm();
            $theCredits = new TheCredits($this->_dbAdapter);
            $model = new Deposit();
            $DepositForm->setInputFilter($model->getInputFilter());
            $DepositForm->setData($request->getPost());

            if ($DepositForm->isValid()) {
                $data = $DepositForm->getData();

                $expDate = $data['month'].$data['year'];
                //----
                $business = new Business();
                $paypalRequest = $business->getPaypalRequest();
                
                $paymentDetails = new \SpeckPaypal\Element\PaymentDetails(array(
                    'amt' => $data['deposit']
                ));

                $payment = new \SpeckPaypal\Request\DoDirectPayment(array('paymentDetails' => $paymentDetails));
                $payment->setCardNumber($data['cardnumber']);
                $payment->setExpirationDate($expDate);
                $payment->setFirstName('-');
                $payment->setLastName('-');
                $payment->setIpAddress('255.255.255.255');
                $payment->setCreditCardType($data['cardtype']);
                $payment->setCvv2($data['cvv']);

                $address = new \SpeckPaypal\Element\Address;
                $address->setStreet('-');
                $address->setStreet2('-');
                $address->setCity('-');
                $address->setState('-');
                $address->setZip('-');
                $address->setCountryCode('US');
                $payment->setAddress($address);

                $response = $paypalRequest->send($payment);

                if($response->isSuccess()) {
                    $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
                    if($myCredits) {
                        $myCredits = get_object_vars($myCredits);
                    }   
                    
                    $availableCredits = $theCredits->adjustUserCreditsDeposit($myCredits,$data['deposit'],
                         $this->getUserSession()->user->id);

                        // Create transaction
                        $trandata = Array(
                            'description' => 'Paypal Credit Card processing',
                            'paymentId' => $response->getTransactionId(),
                            'paymentmethod' => $data['cardtype'],
                            'type' => 'D',
                            'amount'=> $data['deposit'],
                            'balance' => $availableCredits
                        );
                        $this->saveTransaction($trandata, 'd');
                        
                        $return = Array(
                          'success' => true,
                          'messages' => 'Transaction was Succesful.',
                          'credits' => $availableCredits,
                        );
                        return new JsonModel($return);
                    //}
                } else {
                    $errormsg = $response->getErrorMessages();
                    $return = Array(
                       'success' => false,
                       'messages' => $errormsg[0],
                    );  
                    return new JsonModel($return);                   
                }          
            }  
        }
        $errormsg = $response->getErrorMessages();
        $return = Array(
            'success' => false,
            'messages' => 'Transaction was not process.',
        );  
        return new JsonModel($return);
  }

    public function withdrawalAction()
    {
      $this->validateUser();

      $request = $this->getRequest();
      if ($request->isPost()) {
            $WithdrawForm = new WithdrawForm();
            $theCredits = new TheCredits($this->_dbAdapter);
            $model = new Withdraw();
            $WithdrawForm->setInputFilter($model->getInputFilter());
            $WithdrawForm->setData($request->getPost());

            if ($WithdrawForm->isValid()) {
                $data = $WithdrawForm->getData();


                $myCredits = $theCredits->getCreditsByUserId($this->getUserSession()->user->id);
                if($myCredits) {
                    $myCredits = get_object_vars($myCredits);
                }   
                
                $theWithdrawal = new TheWithdrawal($this->_dbAdapter);
                $withdraw = $theWithdrawal->makeTheWithdrawal($data);
                
                if($withdraw) {
                    $availableCredits = $theCredits->adjustUserCreditsWithdrawal($myCredits, $data['withdrawal']);

                    // Create transaction
                    $trandata = Array(
                        'description' => 'Withdrawal processing',
                        'paymentId' => $withdraw->transactionId,
                        'paymentmethod' => $withdraw->paytype,
                        'type' => 'T',
                        'amount'=> $data['withdrawal'],
                        'balance' => $availableCredits
                    );
                    $this->saveTransaction($trandata, 't');

                    $return = Array(
                      'success' => true,
                      'messages' => 'Transaction was Succesful.',
                      'credits' => $availableCredits,
                    );
                    return new JsonModel($return);
                }
            }  
        }
        $return = Array(
            'success' => false,
            'messages' => 'Transaction was not process.',
        );  
        return new JsonModel($return);
  }
  
  

    protected function saveTransaction($trandata, $prefix = null) {
        $theTransactions = new TheTransactions($this->_dbAdapter);
        $theTransactions->makeTheTransaction($trandata, $prefix);
    }  
}