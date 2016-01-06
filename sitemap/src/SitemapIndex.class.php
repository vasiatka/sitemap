<?php
require_once(dirname(__FILE__)."/SitemapBase.class.php");
require_once(dirname(__FILE__)."/Sitemap.class.php");

class SitemapIndex extends SitemapBase
{
  function getHeader()
  {
    $item = '<?xml version="1.0" encoding="UTF-8"?>';
    $item .= PHP_EOL;
    $item .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    return $item;
  }
  
  function getFooter()
  {
    return '</sitemapindex>';
  }
  
  function addSitemap(Sitemap $sitemap)
  {
    if(isset($this->config['sitemapindex_creation_time']))
      $time = $this->config['sitemapindex_creation_time'];
    else
      $time = time();
    $this->addUrl($sitemap->getUrl(), $time);
  }
  
  function addUrl($url,$lastmod,$priority=null,$changefreq = null)
  {
    if(!$this->isAllowedAddingUrls())
      return false;

    $item = $this->wrap($url,'loc');
    $item .= $this->wrap(date(DATE_RFC3339,$lastmod),'lastmod');
    $item = $this->wrap($item,'sitemap');

    if(!$this->isAllowedAddingString($item))
      return false;
 
    $this->addData($item);
    $this->urls_count++;
    return true;
  }  
  
  function getTmpFilePath($p='ind',$tmp_dir=null)
  {
    return parent::getTmpFilePath($p,$tmp_dir);
  }
  
  function validateAndSetConfig()
  {
    parent::validateAndSetConfig();

    if(isset($this->config['sitemapindex_creation_time']))
      if(!is_integer($this->config['sitemapindex_creation_time']))
        $this->logger->error('config["sitemapindex_creation_time"] is not integer');
  }
 
  
}
