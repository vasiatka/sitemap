<?php
class SitemapIndex extends SitemapBase
{
  protected $root_tag = 'sitemapindex';
  protected $item_tag = 'sitemap';
  protected $optional_item_parts = array('lastmod');

  function addSitemap(Sitemap $sitemap, $lastmod=null)
  {
    $url = array('loc' => $sitemap->getSitemapUrl());
    
    if($lastmod)
      $url['lastmod'] = $lastmod;

    $this->addUrl($url);
  }
  
  function getTmpFilePath($p='ind',$tmp_dir=null)
  {
    return parent::getTmpFilePath($p,$tmp_dir);
  }
  
}
