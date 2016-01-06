<?php
require_once(dirname(__FILE__)."/SitemapBase.class.php");
class Sitemap extends SitemapBase
{
  function getHeader()
  {
    $header =  '<?xml version="1.0" encoding="UTF-8"?>';
    $header .= PHP_EOL;
    $header .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    return $header;
  }
  
  function getFooter()
  {
    return '</urlset>';
  }

  function addUrl($url,$lastmod,$priority=0.5,$changefreq = 'weekly')
  {
    if(!$this->isAllowedAddingUrls())
      return false;

    if(!$this->checkPriority($priority))
      $this->logger->warning("Bad priority value: $priority");

    if(!$this->checkChangefreq($changefreq))
      $this->logger->warning("Bad changefreq value: $changefreq");
    
    if(!$this->checkUrl($url))
      $this->logger->warning("Bad url value: $url");

    $item = $this->wrap($url,'loc');
    $item .= $this->wrap(date(DATE_RFC3339,$lastmod),'lastmod');
    $item .= $this->wrap($changefreq,'changefreq');
    $item .= $this->wrap($priority,'priority');
    $item  = $this->wrap($item,'url');

    if(!$this->isAllowedAddingString($item))
      return false;
    $this->addData($item);
    $this->urls_count++;
    return true;
  }
  
}
