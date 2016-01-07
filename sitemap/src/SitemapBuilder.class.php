<?php

class SitemapBuilder
{
  protected $config;
  protected $sitemaps = array();
  protected $current;
  protected $is_started;
  protected $creation_time;

  function __construct($config)
  {
    $this->reset($config);
  }
  
  function start()
  {
    $this->createAndStartSitemap();
    $this->is_started = true;
    $this->creation_time=time();
  }
  
  function createAndStartSitemap()
  {
    $this->current++;
    $this->sitemaps[$this->current] = new Sitemap($this->config);
    $this->sitemaps[$this->current]->start();
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
      $index->addSitemap($this->sitemaps[$i],$this->creation_time);
    }

    return $index->commit() && $result; 
  }

  function addUrl($item)
  {
    if(!$this->is_started)
      $this->start();

    if(!$this->sitemaps[$this->current]->addUrl($item))//try add url
    {
      $this->createAndStartSitemap();
      $this->addUrl($item);
    }
  }

  function getCreationTime()
  {
    return $this->creation_time;
  }

  function join($items)
  {
    foreach($items as $item)
      $this->addUrl ($item);
  }
  
  function getName($n)
  {
    return "sitemap$n.xml";
  }
  
  function reset($config)
  {
    $this->config = $config;
    $this->is_started = false;
    $this->current = -1;
    $this->sitemaps = array();
    $this->creation_time = null;
  }
  
  static function buildSitemap($config,$items)
  {
    $builder = new SitemapBuilder($config);
    $builder->start();
    $builder->join($items);
    return $builder->commit();
  }
  
}
