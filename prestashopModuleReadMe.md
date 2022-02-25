# Sirio PrestaShop module Developing Features

* ##variabile script
La variabile $script rappresenta il contenuto dello script che verrà innestato in pagina, tramite quest'ultimo
è possibile ricavare le informazioni che Sirio necessità per svolgere le analisi.

* ##Costruttore
Il costruttore della classe di Sirio inserisce sulla variabile privata $script la
definizione della variabile sirioCustomObject.
Successivamente richiama quattro metodi :
-getHeaders();
-getIpAddress();
-getCurrency();
-getLocale();

* ##sirioCustomObject
la variabile sirioCustomObject rappresenta una variabile javascript in formato JSON che contiene le informazioni relative alla pagina
in cui è definito.


* ##Gestione Headers => getHeaders()
Recuperare gli headers della pagina

* ##Get Ip Address => getIpAddress()
Recupera l'indirizzo ip del client settando l'attributo ip alla variabile sirioCustomObject -> sirioCustomObject.ip

* ##get moneta dello store => getCurrency()
Recupera la currency del negozio settando l'attributo currency alla variabile sirioCustomObject -> sirioCustomObject.currency

* ##get lingua store => getLocale()
Recupera la lingua del negozio settando l'attributo locale alla variabile sirioCustomObject -> sirioCustomObject.locale

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

    '<script type="text/javascript">        
    //<![CDATA[                             
    '.$this->script.'                      //variabile di classe $script
    sirioCustomObject.pageType = "home";   //attributo pagetype del sirioCustomObject
    //]]>
    </script>'

* ##Gestione scheda prodotto
    * RETURN : Script da innestare in pagina in formato JSON

Lo script presenta la seguente struttura:

    '<script type="text/javascript">
                     //<![CDATA[
                     '.$this->script.'                        //variabile di classe $script
                     sirioCustomObject.pageType = "product";  //attributo pagetype del sirioCustomObject
                     sirioCustomObject.productDetails = {
                        "sku":                                //sku del prodotto o ean
                        "name":                               //nome del prodotto
                        "image":                              //immagine del prodotto
                        "description":                        //descrizione del prodotto
                        "price":                              //prezzo originale del prodotto
                        "special_price":                      //prezzo del prodotto scontato
                        }
                     //]]>
                 </script>''

* ##Gestione pagina search
    * RETURN : Script da innestare in pagina in formato JSON

Lo script presenta la seguente struttura:

    $page_type_script = 'sirioCustomObject.pageType = "search";
                 sirioCustomObject.query = "' . $params["expr"] . '";';


    '<script type="text/javascript">
                 //<![CDATA[
                 [[SCRIPT]]
                 '.$page_type_script.'
                 sirioCustomObject.numProducts = ;  //numero prodotti in pagina
                 sirioCustomObject.pages = ;                      //numero pagine di visualizzazione
                 sirioCustomObject.currentPage = ';                 //pagina di visualizzazione corrente
                 //]]>
             </script>''

* ##Gestione pagina categoria
    * RETURN : Script da innestare in pagina in formato JSON

Lo script presenta la seguente struttura:


    $page_type_script = 'sirioCustomObject.categoryDetails = 
                        {   
                            "name":                                             //nome categoria
                            "image":                                            //immagine categoria
                            "description":                                      //descrizione categoria
                        };
                        sirioCustomObject.pageType = "category";'               //attributo pagetype del sirioCustomObject

    '<script type="text/javascript">
                 //<![CDATA[
                 [[SCRIPT]]
                 '.$page_type_script.'
                 sirioCustomObject.numProducts = ' . $products_count_page . ';  //numero prodotti in pagina
                 sirioCustomObject.pages = ' . $pages . ';                      //numero pagine di visualizzazione
                 sirioCustomObject.currentPage = ' . $page . ';                 //pagina di visualizzazione corrente
                 //]]>
             </script>''




