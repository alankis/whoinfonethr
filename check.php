<?php
//header('Content-Type: text/html; charset=utf-8');
/*
 *
 *@todo check if registrant, tech and admin id is same => info domain => https://registrar-test2.carnet.hr/doc/epp#changelog
 *@todo(RIJEŠENO) => undefined index notice, we wont suppress notice what is really bad. Instead we will test if isset() and then initialize it with empty string :))
 *@todo(RIJEŠENO, ne vraća id registra, kontaktirani) => registrar? Carnet je kontaktiran clid => domain:clID client ID (login name) of the registrar, “sponsoring” the domain
 *@todo(RIJEŠENO => ako je tip osobe "-" nemoj prikazivati, admin i teh osoba na domena, nije bitno niti ne sprema taj podatak
 *@todo (RIJEŠENO=> ako je registrant person nemoj prikazivati tip administrativne i tehničke osobe, na org taj node ne postoji
 *@TODO => ako nije upisan telefon, onda prazno
 */
if ($_POST['robotest'])
{
    echo "We here at InfoNET don't like crawlers and bots. We don't like to expose your privacy to third party crawlers and bots.";
    exit();
}
// damir.com.hr, ostale nisu dobre jer su generiran kroz carnetov sustav pa nema contact:name nodeasd
if (isset($_POST['domain']))
{
  $domain = $_POST['domain'];
} 
else if (isset($_GET['domain']))
{
  $domain = $_GET['domain'];
}
//$domain = 'dfizicka.tst.hr';
//echo "Podaci domene: <b>$domain</b>";
// set error reporting so we can easily debug
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
// create EPP request over https 
function eppRequest($xml){
    static $ch = null;
    
    if(!$ch){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://registrar.carnet.hr/epp/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_COOKIE, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, tmpfile());
    }
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    return curl_exec($ch);
}
// EPP server login
/*
 * Put your CarNET API username and password instead xxxx down bellow
 *  <clID>xxxx</clID>
 *  <pw>xxxx</pw>
 */
function eppServerLogin(){
  eppRequest('
  <?xml version="1.0" encoding="utf-8"?>
    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
      <command>
        <login>
          <clID>xxxx</clID>
          <pw>xxxx</pw>
          <options>
            <version>1.0</version>
            <lang>en</lang>
          </options>
          <svcs>
            <objURI>urn:ietf:params:xml:ns:domain-1.0</objURI>
            <objURI>urn:ietf:params:xml:ns:contact-1.0</objURI>
            <svcExtension>
              <extURI>http://www.dns.hr/epp/hr-1.0</extURI>
            </svcExtension>
          </svcs>
        </login>
        <clTRID>85874813-55215878</clTRID>
      </command>
    </epp>
    ');
};
/* do server login now */
eppServerLogin();
/*
 *
 * get domain info
 */
function eppCheckDomain($domain){
  $data = eppRequest('
    <?xml version="1.0" encoding="UTF-8"?>
      <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <command>
          <check>
            <domain:check>
              <domain:name>'.$domain.'</domain:name>
            </domain:check>
          </check>
          <clTRID>ABC-12345</clTRID>
        </command>
      </epp>
    ');
  $xmldata = new DOMDocument();
  $xmldata->loadXML($data);
  $data = xml_to_array($xmldata);
 
  if(!isset($data['epp']['response']['resData']['domain:chkData']['domain:cd']['domain:name']['@attributes']['avail']))
  {
    $data = 2;
  }
  else if(isset($data['epp']['response']['resData']['domain:chkData']['domain:cd']['domain:name']['@attributes']['avail']) 
    && $data['epp']['response']['resData']['domain:chkData']['domain:cd']['domain:reason'] == "Invalid top level domain")
  {
    $data = 3;
  }
  else
  {
    $data = $data['epp']['response']['resData']['domain:chkData']['domain:cd']['domain:name']['@attributes']['avail'];
  }
  return $data;
};
$data = eppCheckDomain($domain);
/*
$doc = new DOMDocument();
$doc->loadXML($data);
$data = xml_to_array($doc);
*/
$domainavailable = $data;
//DEBUG
//print_r($domainavailable);
//echo "Tip varijable $domainavailable je " . gettype($domainavailable);
function checkDomainAvailability($domainavailable, $domain) {
  echo "<br>";
  //echo "FUNCTION data #############################################################3";
  echo "<br>";
  if($domainavailable == "3"){
    echo "Neispravan naziv vrsne domene!";
    exit();
  }
  if($domainavailable == "2") {
    echo "Unesite ispravan naziv domene!";
    exit();
  }
  if($domainavailable == "1") {
    echo "Domena <b>$domain</b> nije registrirana.";
    echo "<br>";
    exit();
  } else {
    echo "Domena <b>$domain</b> je registrirana.";
    
// output domain data
    $data = eppGetDomainInfo($domain);
    $doc = new DOMDocument();
    $doc->loadXML($data);
    $data = xml_to_array($doc);
    // debug output data
    /* filter only needed data */
    $data = $data['epp']['response']['resData'];
    /*
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    */
    $domaindata['domain:name'] = $data['domain:infData']['domain:name'];
    $domaindata['domain:registrar'] = "will fetch this later";
    $domaindata['domain:creation'] = $data['domain:infData']['domain:crDate'];
    //$domaindata['domain:creation'] = substr($data['domain:infData']['domain:crDate'], 0, 10);
    $domaindata['domain:expiry'] = $data['domain:infData']['domain:exDate'];
    $ns = $data['domain:infData']['domain:ns']['domain:hostAttr'];
  
    $ownerid                      = $data['domain:infData']['domain:registrant'];
    if (!isset($data['domain:infData']['0']['_value'])) {
      $administrativeid = $ownerid;
    } else {
      $administrativeid             = $data['domain:infData']['domain:contact']['0']['_value'];
    }
    if (!isset($data['domain:infData']['1']['_value'])) {
      $techid = $ownerid;
    } else {
      $techid                       = $data['domain:infData']['domain:contact']['1']['_value'];
    }
    $domaindata['owner']          = eppGetDomainUserData($ownerid);
    $domaindata['administrative'] = eppGetDomainUserData($administrativeid);
    
    $domaindata['tech']           = eppGetDomainUserData($techid);
    $lookup['ns']       = $ns;
    $domaindata['registrar:type'] = $domaindata['owner']['type'];
  }
  echo "<br>"; 
  //echo "<br>";
  //echo "FUNCTION data #############################################################3";
  //echo "<br>";
  //echo "DOMAIN DATA*****************************************************************";
  //echo "<br>";
  
  //echo "<br><b>Naziv domene je:</b><br> $domain<br>";
  
  
  echo "<br><b>Podaci o korisniku domene:</b><br>";
  /**/
  if(isset($domaindata['owner']['type'])) unset($domaindata['owner']['type']);
  
  foreach ($domaindata['owner'] as $key => $value) {
    if ($value == "")
      $value = "-";
    //echo "<b>$key: </b>$value<br>";
  }
  
  echo $domaindata['owner']['name'] . '<br>';
  echo $domaindata['owner']['address'] . ', ' . $domaindata['owner']['post:number'] . ', ' . $domaindata['owner']['city'] . '-' . $domaindata['owner']['country'] . '<br>';
  if ($domaindata['owner']['telephone'])
  {
    echo $domaindata['owner']['telephone'] . '<br>';
  }
  echo $domaindata['owner']['email'] .  '<br>';
  echo "<br><b>Podaci o administrativnoj osobi:</b><br>";
  /**/
  if(isset($domaindata['administrative']['type'])) unset($domaindata['administrative']['type']);
  if($domaindata['owner']['type'] = "person") unset($domaindata['administrative']['user:type']);
  foreach ($domaindata['administrative'] as $key => $value) {
    
    if ($value == "") {
      $value = "-";
    } 
    /*else if ($key == "type") {
      unset($domaindata['administrative'][$key]);
    }
    */
    //echo "<b>$key: </b>$value<br>";
    /**/
    
    /*
     * fetch registrant type => person/org
     */
     //echo $domaindata['registrar:type'];
     /*
      * ako je tip registraray person ne ispisuj key user:type, type
      */
   }
  echo $domaindata['administrative']['name'] . '<br>';
  echo $domaindata['administrative']['address'] . ', ' . $domaindata['owner']['post:number'] . ', ' . $domaindata['owner']['city'] . '-' . $domaindata['owner']['country'] . '<br>';
  if($domaindata['administrative']['telephone'])
  {
    echo $domaindata['administrative']['telephone'] . '<br>';
  }
  echo $domaindata['administrative']['email'] .  '<br>';
  
  echo "<br><b>Podaci o tehničkoj  osobi:</b><br>";
  /**/
  
  if(isset($domaindata['tech']['type'])) unset($domaindata['tech']['type']);
  if($domaindata['owner']['type'] = "person") unset($domaindata['tech']['user:type']);
  foreach ($domaindata['tech'] as $key => $value) {
    if ($value == "")
      $value = "n/a";
    //echo "<b>$key: </b>$value<br>";
  }
  echo $domaindata['tech']['name'] . '<br>';
  echo $domaindata['tech']['address'] . ', ' . $domaindata['owner']['post:number'] . ', ' . $domaindata['owner']['city'] . '-' . $domaindata['owner']['country'] . '<br>';
  if($domaindata['tech']['telephone'])
  {
    echo $domaindata['tech']['telephone'] . '<br>';
  }
  echo $domaindata['tech']['email'] .  '<br>';
  //echo "<br>";
  //echo "END DOMAIN DATA*****************************************************************";
  //echo "<br>";
  /**/
  /*
  echo "test";
  echo "<br>";
   */
  /* debug array 
  echo "<br>TEST*******************************************************";
  echo "<pre>";
  print_r($domaindata['tech']);
  echo "</pre>";
   end of debug array */
echo "<br><b>Domenski poslužitelji:</b><br>";
  /**/
  if(isset($lookup['ns']))
  {
    foreach($lookup['ns'] as $key => $ns)
    {
      echo $ns['domain:hostName'].'<br>';
    }
  } else {
    echo "Na domeni nisu upisani domenski poslužitelji.<br>";
  }
   
  echo "<br><b>Datum registracije:</b><br>" . formatDate($domaindata['domain:creation']) . "<br>";
 
  echo "<br><b>Datum isteka:</b><br>" . formatDate($domaindata['domain:expiry']) . "<br>";
}
/*
if (!isset($domain)) {
  echo "Unesite domenu.....";
} else {
  checkDomainAvailability($domainavailable, $domain);
}
*/
if(isset($domain) && $domain != "")
{
  checkDomainAvailability($domainavailable, $domain);
}
else if($domain == "") 
  {
    echo "Naziv domene ne može biti prazan!";
  }
  else 
  {
    echo "Unesite naziv domene za provjeru!";
  }
/*
echo "<br>";
echo "Domain avaliable...........................................................................";
echo "<pre>";
print_r($data);
echo "</pre>";
*/
function eppGetDomainInfo($domain) {
  $data = eppRequest('
    <?xml version="1.0" encoding="utf-8"?>
      <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <command>
          <info>
            <domain:info>
              <domain:name>'.$domain.'</domain:name>
            </domain:info>
          </info>
          <clTRID>05106558-94309643</clTRID>
        </command>
      </epp>
    ');
 
  
  return $data;
};
$data = eppGetDomainInfo($domain);
$doc = new DOMDocument();
$doc->loadXML($data);
$data = xml_to_array($doc);
/*
//print_r($domainInfo);
echo "Domain info data...........................................................................";
echo "<pre>";
print_r($data);
echo "</pre>";
//print_r($data);
//$domaininfo = eppGetDomainInfo($domain);
// echo $domaininfo;
*/
# because we unfortunatelly has a lot of nested arrays php bulit-in function really sucks, so we have to built our own, which is really boooring
function xml_to_array($root) {
    $result = array();
    if ($root->hasAttributes()) {
        $attrs = $root->attributes;
        foreach ($attrs as $attr) {
            $result['@attributes'][$attr->name] = $attr->value;
        }
    }
    if ($root->hasChildNodes()) {
        $children = $root->childNodes;
        if ($children->length == 1) {
            $child = $children->item(0);
            if ($child->nodeType == XML_TEXT_NODE) {
                $result['_value'] = $child->nodeValue;
                return count($result) == 1
                    ? $result['_value']
                    : $result;
            }
        }
        $groups = array();
        foreach ($children as $child) {
            if (!isset($result[$child->nodeName])) {
                $result[$child->nodeName] = xml_to_array($child);
            } else {
                if (!isset($groups[$child->nodeName])) {
                    $result[$child->nodeName] = array($result[$child->nodeName]);
                    $groups[$child->nodeName] = 1;
                }
                $result[$child->nodeName][] = xml_to_array($child);
            }
        }
    }
    return $result;
}
function eppGetDomainUserData($domainUserId="") {
  if(!$domainUserId) {
    // die();
  }
  $requestedData = eppRequest('
    <?xml version="1.0" encoding="utf-8"?>
      <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:contact="urn:ietf:params:xml:ns:contact-1.0">
        <command>
          <info>
            <contact:info>
              <contact:id>'.$domainUserId.'</contact:id>
            </contact:info>
          </info>
          <clTRID>89845498-45440972</clTRID>
        </command>
      </epp> 
    ');
  /**/             
  $xmldata = new DOMDocument();
  $xmldata->loadXML($requestedData);
  $responseData = xml_to_array($xmldata);
  $domainData = $responseData['epp']['response'];
  
  //$data['name']         = $domainData['resData']['contact:infData']['contact:postalInfo']['contact:name'];
  if (!isset($domainData['resData']['contact:infData']['contact:postalInfo']['contact:name'])) {
    $domainData['resData']['contact:infData']['contact:postalInfo']['contact:name'] = "";
    
  }
  $data['name'] = $domainData['resData']['contact:infData']['contact:postalInfo']['contact:name'];
  $data['address']      = $domainData['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:street'];
  $data['post:number']  = $domainData['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:pc'];
  $data['city']         = $domainData['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:city'];
  $data['country']      = $domainData['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:cc'];
if(!isset($domainData['resData']['contact:infData']['contact:voice'])) {
  $domainData['resData']['contact:infData']['contact:voice'] = "";
}
  $data['telephone']    = $domainData['resData']['contact:infData']['contact:voice'];
  //$data['fax']          = $domainData['resData']['contact:infData']['contact:fax'];
  if(!isset($domainData['resData']['contact:infData']['contact:fax'])) {
    $domainData['resData']['contact:infData']['contact:fax'] = "";
    $data['fax'] = $domainData['resData']['contact:infData']['contact:fax'];
  }
  $data['email']        = $domainData['resData']['contact:infData']['contact:email'];
  //$data['type']         = $domainData['extension']['hr:info']['hr:contact']['hr:type'];
  if (!isset($domainData['extension']['hr:info']['hr:contact']['hr:type'])) {
    $domainData['extension']['hr:info']['hr:contact']['hr:type'] = "";
    //$data['type'] = $domainData['extension']['hr:info']['hr:contact']['hr:type'];
  }
  //$data['type'] = "TEST";
  $data['type'] = $domainData['extension']['hr:info']['hr:contact']['hr:type'];
  /**/
  if($data['type'] == "org") {
      $data['user:type'] = "Pravna osoba";
  } else if($data['type'] == "person") {
      $data['user:type'] = "Fizička osoba";
  } else {
      $data['type'] == "";
  }
  /**/
  /*
   * we will save domain registrant type here 
   */
  //$data['domainRegistrantType'] = $domainData['extension']['hr:info']['hr:contact']['hr:type'];
  return $data;
};
function formatDate($date)
{
  //$date = '2015-11-02T23:00:00Z';
  $original_timezone = new DateTimeZone('UTC');
  $datetime = new DateTime($date, $original_timezone);
  $target_timezone = new DateTimeZone('Europe/Moscow');
  $datetime->setTimeZone($target_timezone);
  //print_r($datetime);
  $new_date = $datetime->format('d-m-Y');
  $new_date = str_replace('-', '.', $new_date);
  //echo $new_date;
  return $new_date;
}
/*
$doc = new DOMDocument();
$doc->loadXML($domaininfo);
$answer = xml_to_array($doc);
// go to domain:infData array to collect all domain relative data
$domainData = $answer['epp']['response']['resData']['domain:infData'];
*/
echo $ownerid;
