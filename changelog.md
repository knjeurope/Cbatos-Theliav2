-- V 1.6
++ Change payment call instance
++ Simplify admin setup
++ Change Template model now just upload order-payment.html in your template file activated in your thelia
++ All return url and Path for requested file ... is auto define just check chmod and your certificat file
++ Page choose credit card is now full translated ... Use thelia translator for add language or modify
-- Now ip and mail customer is still sent to atos that for you secure and for one secure transaction .
++ Fixed major bug
++ Now you can refresh page payment not create new order, install call is routed

-- V 1.5
++ Code optimization 
++ More simplified installation 
++ autodetection API Request and Responses need to specify the path 
++ autodetection Pathfile need to specify the path 
++ autodetection of the return URL and cancellation (no need to indicate) 
++ autodetection ipn url of atos (no need to indicate) 
++ Creating a new template for displaying ATOS payment options, a completely independent in your template (to be uploaded in the templates / directory frontoffice) 
++ Using the failed-page order in case of refusal or cancellation 
++ Var : ${refusmotif} available to display the reason for the refusal or cancellation to indicate that just himself. 
++Using the Placed-order page  in the context of acceptance of payment. 

-- V 1.2
++ Add transaction receipt directly on admin (order edit, Modules)


-- V 1.1
++ Compatibility with Thelia v2.0.1
-- delete mail to mysql
++ Create Model mail directly to TXT and HTML I18 Directory
++ Add Sherlocks Lcl support
++ Add Etransactions Crédit agricole support

