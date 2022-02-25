# Sirio PrestaShop module Developing Features

* ##sirioCustomObject
la variabile sirioCustomObject rappresenta una variabile javascript in formato JSON che contiene le informazioni relative alla pagina
in cui è definito. 

#Gestione pages :
le pageType gestite si dividono in :
* home => la homePage del sito

* product => la pagina relativa alla scheda di un prodotto
  
* search => la pagina conseguente all'effettuazione di una ricerca prodotto 
    
* category => la pagina relativa ad una categoria prodotti
    
* checkout => la pagina relativa alla procedura di checkout
    
* checkout_success => la pagina conseguente ad checkout avvenuto con successo

* ##Gestione HomePage 
    * RETURN : Script da innestare in pagina in formato JSON
    
Lo script presenta la seguente struttura:

    sirioCustomObject.headers =            //contenuto headers
    sirioCustomObject.ip =                 //ip client
    sirioCustomObject.locale =             //lingua dello store       
    sirioCustomObject.pageType = "home";   //attributo pagetype del sirioCustomObject


* ##Gestione scheda prodotto
    * RETURN : Script da innestare in pagina in formato JSON
    
Lo script presenta la seguente struttura:


                      sirioCustomObject.headers =               //contenuto headers
                      sirioCustomObject.ip =                    //ip client
                      sirioCustomObject.locale =                //lingua dello store
                      sirioCustomObject.pageType = "product";   //attributo pagetype del sirioCustomObject
                      sirioCustomObject.productDetails = {
                        "sku":                                  //sku del prodotto o ean
                        "name":                                 //nome del prodotto
                        "image":                                //immagine del prodotto
                        "description":                          //descrizione del prodotto
                        "price":                                //prezzo originale del prodotto
                        "special_price":                        //prezzo del prodotto scontato
                      }


* ##Gestione pagina ricerca
  * RETURN : Script da innestare in pagina in formato JSON

Lo script presenta la seguente struttura:

                  sirioCustomObject.headers =               //contenuto headers
                  sirioCustomObject.ip =                    //ip client
                  sirioCustomObject.locale =                //lingua dello store
                  sirioCustomObject.pageType = "search";   //attributo pagetype del sirioCustomObject
                  sirioCustomObject.query : ;               //query di ricerca 
                  sirioCustomObject.numProducts = ;         //numero prodotti in pagina
                  sirioCustomObject.pages = ;               //numero pagine di visualizzazione
                  sirioCustomObject.currentPage = ' ;       //pagina di visualizzazione corrente



* ##Gestione categoria
  * RETURN : Script da innestare in pagina in formato JSON

Lo script presenta la seguente struttura:

                  sirioCustomObject.headers =                 //contenuto headers
                  sirioCustomObject.ip =                      //ip client
                  sirioCustomObject.locale =                  //lingua dello store
                  sirioCustomObject.categoryDetails = {
                                    "name":                   //nome categoria
                                    "image":                  //immagine categoria
                                    "description":            //descrizione categoria
                                    }
                  sirioCustomObject.pageType = "category";    //attributo pagetype del sirioCustomObject
                  sirioCustomObject.numProducts = ;           //numero prodotti in pagina
                  sirioCustomObject.pages = ;                 //numero pagine di visualizzazione
                  sirioCustomObject.currentPage = ' ;  

* ##Gestione pagina cart
  * RETURN : Script da innestare in pagina in formato JSON

Lo script presenta la seguente struttura:
                  
                  -OGNI PRODOTTO DEL CART HA LA SEGUENTE STRUTTURA

                  product = {
                      "item-id" : Id identificativo del prodotto
                      "options" : dizionario delle combinazioni chiave valore del prodotto (passare "" se prodotto non configurabile o senza combinazioni)
                      "sku": result[key].sku,
                      "price": result[key].price,
                      "qty": result[key].count,
                      "name": decodeHtml(result[key].title),
                      "discount_amount": result[key].listing_price - result[key].source_price
                  }

                  -STRUTTURA DEL CART  

                  var sirioCart = {                       
                      "cart_total":         //totale carrello con tasse e spedizione                 
                      "cart_subtotal":      //subtotale del carrello 
                      "shipping":           //costi di spedizione
                      "coupon_code":        //codice coupon se applicato
                      "discount_amount":    //sconto coupon se applicato
                      "cart_products":      //array dei prodotti all'intenro del carrello
                  }
                  sirioCustomObject.cart =  sirioCart

* ##Gestione pagina checkout success
  * RETURN : Script da innestare in pagina in formato JSON

Lo script presenta la seguente struttura:

                  sirioCustomObject.pageType = 'checkout_success';

* ##Gestione pagina checkout failure
  * RETURN : Script da innestare in pagina in formato JSON

Lo script presenta la seguente struttura:

                  sirioCustomObject.pageType = 'checkout_failure';

* ##Tracking for UX (beta)


* ##ActionType su push carrello => tipo azione che scaturisce push carrello:
  * login (NO STOREDEN)
  * addToCart
  * removeFromCart
  * updateCart (NO STOREDEN)
  * viewCart -> Pageload
  * externalLink
  * changeQuantity
  * applyCoupon  
  
