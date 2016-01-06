<?php
require_once(dirname(__FILE__).'/Sitemap.class.php');
require_once(dirname(__FILE__).'/SitemapIndex.class.php');

class SitemapBuilder
{
  protected $config;
  protected $sitemaps = array();
  protected $current;
  protected $is_started;
  
  function __construct($config)
  {
    $this->config = $config;
    $this->is_started = false;
    $this->current = -1;
  }
  
  function start()
  {
    $this->createAndStartSitemap();
    $this->is_started = true;
  }
  
  function commit()
  { 
    $this->is_started = false;
    if($this->current==0)
      return $this->sitemaps[$this->current]->commit();
    
    return $this->createSitemapIndex();
  }
  
  function createSitemapIndex()
  {  
    $result = true;
    $index = new SitemapIndex($this->config);
    $index->start();
    
    for ( $i=0; $i<=$this->current; $i++)
    {
      $this->sitemaps[$i]->setName($this->getName($i));
      $result = $this->sitemaps[$i]->commit() && $result;
      $index->addSitemap($this->sitemaps[$i]);
    }

    return $index->commit() && $result; 

  }

  function getName($n)
  {
    return "sitemap$n.xml";
  }
  
  function addUrl($url,$lastmod,$priority=0.8,$changefreq = 'weekly')
  {
    if(!$this->is_started)
      $this->start();
    
    if(!$this->sitemaps[$this->current]->addUrl($url, $lastmod, $priority, $changefreq))//try add url
    {
      $this->createAndStartSitemap();
      $this->addUrl($url, $lastmod, $priority, $changefreq);
    }
  }
  
  function createAndStartSitemap()
  {
    $this->current++;
    $this->sitemaps[$this->current] = new Sitemap($this->config);
    $this->sitemaps[$this->current]->start();
  }
  
  function join($items)
  {
    foreach($items as $item)
      $this->addUrl ($item['url'], $item['lastmod'], $item['priority'], $item['changefreq']);
  }
  
  static function buildSitemap($config,$items)
  {
    $builder = new SitemapBuilder($config);
    $builder->start();
    $builder->join($items);
    return $builder->commit();
  }
  
}
