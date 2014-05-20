<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
 
 
 {* Declare assets directory, relative to template base directory *}
{declare_assets directory='assets'}
{* Set the default translation domain, that will be used by {intl} when the 'd' parameter is not set *}
{default_translation_domain domain='fo.module_atos'}

 

{block name="no-return-functions"}{/block}
{assign var="store_name" value="{config key="store_name"}"}
{if not $store_name}{assign var="store_name" value="{intl l='Thelia V2'}"}{/if}

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang attr="code"}" lang="{lang attr="code"}"><head>
   {* Test if javascript is enabled *}
    <script>(function(H) { H.className=H.className.replace(/\bno-js\b/,'js') } )(document.documentElement);</script>
    
 
 
 {* Stylesheets *}
    {stylesheets file='assets/css/bootstrap.css'}
        <link rel="stylesheet" href="{$asset_url}" media="all" type="text/css">
    {/stylesheets}
    {stylesheets file='assets/css/bootstrap-theme.css'}
        <link rel="stylesheet" href="{$asset_url}" media="all" type="text/css">
    {/stylesheets}
  
 
 
 
 
 
 
 
 
<!-- JavaScript -->
<!--[if lt IE 9]><script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script> <!--<![endif]-->

<script>
    if (typeof jQuery == 'undefined') {
        {javascripts file='assets/js/jquery.js'}
            document.write(unescape("%3Cscript src='{$asset_url}' %3E%3C/script%3E"));
        {/javascripts}
    }
</script>

 
 {javascripts file='assets/js/bootstrap.min.js'}
    <script src="{$asset_url}"></script>
{/javascripts} 

 
 
 </head>
<body  itemscope itemtype="http://schema.org/WebPage">
 
    
     
     
 
	  
   
		 	 
        
       
       
       {block name="main-content"}{/block}
		 
    

 
</body>
</html>
