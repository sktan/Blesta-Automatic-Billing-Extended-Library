# Blesta's Automatic Billing Extended Library
A library to integrate your Blesta non-merchant gateway with the automatic billing extended plugin

## Introduction
"Automatic Billing Extended" is a plugin that allows for Blesta non-merchant gateways to easily implement methods in order to support automatic-billing via tokens.
Currently, blesta only supports automatic billing for merchant gateways (credit-card payments) which is why the Autoamtic Billing Extended plugin was built.
Non-merchant gateawys should also have the ability to support automatic billing as most payment-processors (e.g. Paypal) does support reoccuring and future payments via the use of tokens.

## Including the Library
Add the library to your non-merchant gateway by downloading and including the .php file into your project.
` /example_payment/libs/automatic_billing_extended_lib.php `

Here is a code-example to show how the library can be included in your project
```
class ExamplePayment extends NonmerchantGateway {
    public function getAutomaticBillingExtendedLibrary() {
        Loader::load(dirname(__FILE__) . DS . "libs" . DS . "automatic_billing_extended_lib.php");
        // We will be using the class-name as our identifier for this class
        $automatic_billing_extended = new AutomaticBillingExtendedLibrary(__CLASS__);
        rteturn $automatic_billing_extended;
    }
}
```

## Utilizing the Library ##
To utilize the library, you can use the included functions in order to add / remove / modify / retrieve entries related to your customer.

### Adding a new customer's information ###
```
$automatic_billing_extended = $this->getAutomaticBillingExtendedLibrary();
// Feel free to just use the payment token as the gateway_customer_id if you you can't determine their identifier after an API response
$autoamtic_billing_extended->addBillingMethod($blesta_customer_id, $payment_token, $gateway_customer_id);
```

### Checking if a billing-method for your customer already exists ###
```
$automatic_billing_extended = $this->getAutomaticBillingExtendedLibrary();
$automatic_billing_extended->billingMethodExists($blesta_customer_id);
```

### Modifying already-existing customer information ###
```
$automatic_billing_extended = $this->getAutomaticBillingExtendedLibrary();
// Feel free to just use the payment token as the gateway_customer_id if you you can't determine their identifier after an API response
$automatic_billing_extended->modifyBillingMethod($blesta_customer_id, $new_payment_token, $new_gateway_customer_id);
```

### Deleting already-existing customer information ###
```
$automatic_billing_extended = $this->getAutomaticBillingExtendedLibrary();
$automatic_billing_extended->removeBillingMethod($blesta_customer_id);
```
