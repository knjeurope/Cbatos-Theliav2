-- Attention !! Avant d'installer ce module assurez vous que votre hébergeur autorise la fonction EXEC() sans quoi vous ne pourrez pas faire fonctionner le script ATOS

-- WARNING !! Before you install this module, pls check your hosting provider if function EXEC() is activate if not you can not use Atos SIPS



﻿### Module de paiement Carte Bancaire ATOS for Thelia V2
######Compatible Version 2.0.1 - Mise à jour le 26 Mai 2014######
###MODULE VERSION 1.6###


__Français (Fr_fr) :__

###Fonction du module###

* Encaissement
* Gestion du débit à l'expedition (ou paiement différé)
* Filtre Securité ATOS Email/Ip/Id du client
* Mode DEBUG (On / Off)
* Retour automatique arrière plan Atos (sans clique sur le bouton retour)
* Retour site client personnalisé en fonction de la réponse
*  ** Le client connaitra dans le cadre d'un retour vers le site la raison exact pour laquel le paiement est refusé
* En cas d'acceptation le numéro de transaction apparaitra dans la commande afin de vous permettre d'identifier la commande
* A chaque acceptation un fichier JSON est généré afin de stocker les informations de chaque transaction sur votre disque système, cela afin d'avoir tous les codes retournés par Atos lors du passage de la transaction .


###Sommaire###
    ## Français ##

* Pré-requis
* Installation
* Utilisation
* Intégration
* Variable Mail
* Carte de teste (simulation paiement)


`Pré-requis`
```
Finaliser un contrat de vente à distance auprès de votre banque et signer avec la solution ATOS proposer par votre banque, Votre banque vous enverra un certificat d'installation qui vous permettra d'installer ce module
```

`Installation`

```
Pour installer le module cbatos, téléchargez l'archive et décompressez la dans /local/modules
Ensuite vous devez Copier le répertoire présent dans templates/frontoffice du module dans le repertoire templates/frontoffice de Thelia
Uploader votre fichier xxx.certif que votre banque vous à communiquer dans le repertoire PARM du module
Uploader le fichier parmcom.idmachand que votre baque vous à fournis dans le répertoire PARM
il vous suffira ensuite d'indiquer le IDMARCHAND dans le menu configurer du module (depuis votre backoffice) . 
```

`Utilisation`
```
Pour utiliser le module cbatos, allez dans le back-office, onglet Modules, et activez-le, puis cliquez sur "Configurer" sur la ligne du module. puis renseigner :

* Votre numéro de marchand
* Nombre de jour pour la capture du paiement (Différé ou pas)
* Si vous souhaite envoyez à atos ou pas : Mail, Ip (Client)
* Mode DEBUG (En production obligatoirement à NON)
```
`Intégration`
```
Ce module utilise un template indépendant (présent dans le répertoire templates/FrontOffice du module ) :

Il vous faut UPLOADED le repertoire du template (module-atos) directement dans le répertoire templates/frontoffice de THELIA.
```
`PARAMETRE DE TESTE `
```
Par default le fichier:

certif.fr013044876511111
parmcom.013044876511111

sont fournis à titre d'exemple, ceci étant des fichiers fournis par le crédit agricole pour tester le fonctionnement de la plateforme

Numéro de marchand : 013044876511111
```


`VARIABLE MAIL`<br>
`Le ticket de la transaction est systématiquement envoyé au client cela est une obligation faisant partie du CODE MONAITAIRE.`

 Id Variable # | Variable Smarty | Utilisation | Modifiable
:-----------|------------:|-------------:|-------------:
 01       |        {$METHOD_PAID} | Nom de la méthode utilisé | OUI
 02     |      {$ETP} |        Technologie de passage utilisé |
 03       |        {$MESSAGE_HAUT_TICKET_ATOS} |        Message de bienvenue Ticket |OUI   
 04         |          {$MERCHANT} |        Identifiant marchand CB |   
 05       |       {$autorisation} |Autorisation de la transaction|
 06    |     {$LE} {$DATE_TRANS} {$A} {$TIME_TRANS} |          Date et heure de la transaction |  
 07    |     {$STORE_NAME} |          Récupération des infos boutique (nom de société) |  
  08    |     {$STORE_LINE1} |          Récupération des infos boutique (adresse de société) |  
   09    |     {$STORE_CP} |          Récupération des infos boutique (Codepostal ville norme AFD) |  
    10    |     {$CB_CRYPTE} |          Numéro crypté de la carte utilisé |  
     11    |     {$CERTIFICAT} |          Certificat de transaction |  
      12    |     {$FIN} |          Jamais communiqué (interdit) |  
      13    |     {$TRANS_ID} |          Transaction ID |  
      14    |     {$MONT} |          Texte indiquant le mot MONTANT | OUI  
      15    |     {$INFO} |          Texte indiquant le mot INFORMATION | OUI 
      16    |     {$MONTANT_TRANS_EUR} |          Montant en euros de la transaction |  
      17    |     {$MONTANT_TRANS_FRF} |          Montant convertie en Francs |  
      18    |     {$MONTANT_TRANS_USD} |          Montant convertie en USD (Relation BCE) |  
      19    |     {$MESSAGE_TICKET_CLIENT} |         Message Ticket Client  | OUI
       20    |     {$CONSERVE} |          Message indiquant de conserver le ticket | OUI
        21    |     {$BYE} |          Message indiquant le message de fin  | OUI
         22    |     {$order_ref} |          Référence commande |
          23    |     {$order_id} |          Id commande |
          
      
    

######Par soucis de sécurité et de norme interbancaire nous vous invitons à ne pas modifier le template mail du ticket envoyé, vous pouvez changer les couleurs ... mais pas le contenu ni l'ordre .

##`Carte de teste`

Type de carte | Numéro | Expiration | Cvv
:-----------|------------:|-------------:|-------------:
VISA|4974934125497800|09-2018|255

* * * 
 
















