<?php
abstract class SitemapBase
{
  const MAX_SITEMAP_SIZE = 10485760;//10*1024*1024
  const MAX_URL_COUNT = 50000;
  const MAX_URL_LEN = 2048;
  protected $valid_changefreq =  array('always','hourly','daily','weekly','monthly','yearly','never');

  protected $is_started;
  protected $tmp_file;
  protected $tmp_file_ptr;
  protected $config;
  
  protected $size = 0;
  protected $urls_count = 0;
  protected $name = 'sitemap.xml';

  protected $path;
  protected $protocol;
  protected $host;

  protected $logger;

  protected $root_tag;
  protected $item_tag;
  protected $optional_item_parts;

  function __construct($config = array())
  {
    $this->config = $config;
    $this->validateAndSetConfig();
  }
 
  function validateAndSetConfig()
  {
    $this->is_started = false;
    
    if(isset($this->config['logger']))
      $this->logger = $this->config['logger'];
    else
      $this->logger = new SitemapLog();

    if (isset($this->config['name']))
      $this->setName($this->config['name']);
    
    if(!isset($this->config['path']))
      $this->logger->error('Your must set config["path"]');
    $this->setPath($this->config['path']);
    
    if(!isset($this->config['base_url']))
      $this->logger->error('Your must set config["base_url"]');
    
    $base_parts = @parse_url($this->config['base_url']);

    if(!isset($base_parts['scheme']))
      $this->logger->error('No protocol in base_url');
    $this->protocol =  $base_parts['scheme'];

    if(!isset($base_parts['host']))
      $this->logger->error('No host in base_url');
    $this->host =  $base_parts['host'];

    if(isset($this->config['tmp_dir']))
      if(!file_exists($this->config['tmp_dir']))
      { 
        $this->warning('Path config["tmp_dir"] is not exists');
        unset($this->config['tmp_dir']);
      }
  }

 
  function start()
  {
    $this->is_started = true;
    $this->logger->message('Start tmp_ file "'.$this->getTmpFilePath().'"');
    if(!$this->tmp_file_ptr = fopen($this->getTmpFilePath(),'w'))
      $this->logger->error('tmp_file "'.$this->getTmpFilePath().'" is not writable');

    $this->addData($this->getXmlHeader());
    $this->addData($this->getOpenRootTag());
  }
  
  function commit()
  {
    $this->addData($this->getClosingRootTag());
    fclose($this->tmp_file_ptr);
    $result = $this->moveTmpFile();

    if(isset($this->config['gzip']) && $this->config['gzip'])
      $this->createGzipFile();

    $this->is_started = false;
    return $result;
  }

  function moveTmpFile()
  {
    $this->logger->message('Coping "'.$this->getTmpFilePath().'" to "'.$this->getSitemapPath().'"');
    $result = copy($this->getTmpFilePath(),$this->getSitemapPath());
    chmod($this->getSitemapPath(), 0644);
    unlink($this->getTmpFilePath());
    unset($this->tmp_file); 
    return $result;
  }
  
  function createGzipFile()
  {
    if(isset($this->config['gzip_level']))
      $gzip_level =  $this->config['gzip_level'];
    else
    {
      $this->logger->message('Using gzip_level=3');
      $gzip_level = 3;
    }
    $file_source = $this->getSitemapPath();
    $file_gz = $file_source . '.gz';
    $this->logger->message('Compressing "'.$file_source.'" to "'.$file_gz.'"');
    file_put_contents($file_gz, gzencode(file_get_contents($file_source), $gzip_level, FORCE_GZIP));
    chmod($file_gz, 0644);
  }

  function getTmpFilePath($p='map',$tmp_dir=null)
  {
    if(isset($this->tmp_file))
      return $this->tmp_file;
    
    if(isset($this->config['tmp_dir']))
      $tmp = $this->config['tmp_dir'];
    elseif($path = session_save_path())
    {
      if(($pos = strpos($path, ';')) !== false)
        $path = substr($path, $pos+1);
      $tmp = $path;
    }
    elseif($path = getenv('TMP') || $path = getenv('TEMP') || $path = getenv('TMPDIR'))
      $tmp = $path;
    else
      $tmp = '/tmp';

    $this->tmp_file=tempnam($tmp, $p);

    return $this->tmp_file;
  }
  
  function getXmlHeader()
  {
    return '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
  }

  function getOpenRootTag()
  {
    return "<{$this->root_tag} xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
  }
  
  function getClosingRootTag()
  {
    return "</{$this->root_tag}>";
  }

  function addData($data)
  {
    if(!$this->is_started)
      $this->logger->error('Writing data before starting');
    $this->size+=fwrite($this->tmp_file_ptr,$data);
  }

  function setName($name)
  {
    $this->name = $name;  
  }
  
  function getName()
  {
    return $this->name;  
  }
  
  function getSize()
  {
    return $this->size;
  }
  
  function getUrlsCount()
  {
    return $this->urls_count;
  }
  
  //path to www
  function setPath($path)
  {
    $this->path = rtrim($path,'/');
  }
  
  function getPath()
  {
    return $this->path;
  }

  function getSitemapPath()
  {
    return $this->getPath().'/'.$this->getName();
  }
  
  function getSitemapUrl()
  {
    $url = rtrim($this->config['base_url'],'/')."/".$this->getName();  
    return $url;
  }

  function wrap($data,$tag)
  {
    return "<$tag>$data</$tag>";
  }

  function isAllowedAddingUrls()
  {
    if(self::MAX_URL_COUNT>$this->getUrlsCount())
      return true;
    return false;
  }

  function isAllowedAddingString($item)
  {
    $string_size = strlen($item); //strlen returns length of string in bytes
    $total_size = $this->getSize() + $string_size + strlen($this->getClosingRootTag());
    if($total_size>self::MAX_SITEMAP_SIZE)
      return false;
    return true;
  }

  function addUrl($url)
  {

    if(!$this->isAllowedAddingUrls())
      return false;

    $item = $this->addRequiredParts($url);
    $item .= $this->addOptionalParts($url);
    $item  = $this->wrap($item,$this->item_tag);

    if(!$this->isAllowedAddingString($item))
      return false;
    $this->addData($item);
    $this->urls_count++;
    return true;
  }

  function addRequiredParts($item)
  {  
    if(!isset($item['loc']))
      $this->logger->error("Url is required");
    
    $url = $item['loc'];

    if(strlen($url)>self::MAX_URL_LEN)
      $this->logger->warning("Url is too long: $url");
    
    $base = @parse_url($url);
    
    if(!isset($base['scheme']) || ($base['scheme']!=$this->protocol))
      $this->logger->warning("Url has no protocol: $url");
    
    if(!isset($base['host']) || ($base['host']!=$this->host))
      $this->logger->warning("Url has no host: $url");

    return $this->wrap($url,'loc');
  }

  function addOptionalParts($item)
  {
    $parts = '';
    if(isset($item['lastmod']) && in_array('lastmod',$this->optional_item_parts))
    {
      $lastmod = $item['lastmod'];
      if( !is_integer($lastmod)  )
        $this->logger->warning("Lastmod is not valid timestamp: $lastmod");
      
      $parts .= $this->wrap(date(DATE_RFC3339,$lastmod),'lastmod');
    }
    
    if(isset($item['priority']) && in_array('priority',$this->optional_item_parts))
    {
      $priority = $item['priority'];
      if( (0.0 > $priority) || ($priority > 1) )
        $this->logger->warning("Bad priority value: $priority");
      
      $parts .= $this->wrap($priority,'priority');
    }
    
    if(isset($item['changefreq']) && in_array('changefreq',$this->optional_item_parts))
    {
      $changefreq = $item['changefreq'];
      if(!in_array($changefreq,$this->valid_changefreq))
        $this->logger->warning("Bad changefreq value: $changefreq");
      
      $parts .= $this->wrap($changefreq,'changefreq');
    }

    return $parts;
  }

}
