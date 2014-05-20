<?php
namespace Cbatos\Model;

interface ConfigInterface
{
    // Data access
    public function write($file=null);
    public static function read($file=null);

    // variables setters

    /*
     * @return Cbatos\Model\ConfigInterface
     */
    public function setCBATOSMERCHANTID($CBATOS_MERCHANTID);
    public function setCBATOSSIPSSOLUTIONS($CBATOS_SIPSSOLUTIONS);
  
 public function setCBATOSCAPTUREDAYS($CBATOS_CAPTUREDAYS);
public function setCBATOSDEVISES($CBATOS_DEVISES);
public function setCBATOSCUSTOMERMAIL($CBATOS_CUSTOMERMAIL);
 public function setCBATOSCUSTOMERIP($CBATOS_CUSTOMERIP);
  
public function setCBATOSMODEDEBUG($CBATOS_MODEDEBUG);

}
