<?php
require_once(dirname(__FILE__)."/../common.inc.php");

set_time_limit(0);
ini_set('memory_limit', '64M');

$dir = dirname(__FILE__);//document root path
$tmp_dir = dirname(__FILE__);//temp path
$base_url = 'http://mysite.ru/';//url with sitemaps (http://mysite.ru/sitemap.xml)
$gzip = true;
$config = array('path' => $dir , 'tmp_dir'=>$tmp_dir,'base_url'=>$base_url,'gzip'=>$gzip, 'gzip_level'=>9);

$builder = new SitemapBuilder($config);

$time = time();
    
$builder->start();
$builder->addUrl(array('loc'=>$base_url,'lastmod'=>$time,'priority'=>1.0,'changefreq'=>'weekly'));
$builder->addUrl(array('loc'=>$base_url."news",'lastmod'=>$time,'priority'=>1.0,'changefreq'=>'weekly'));

/*
//this is example adding url 
$documents = News::find(array('criteria'=>'is_published=1'));
foreach($documents as $document)
  $builder->addUrl(array('loc'=>$document->getUrl(),'lastmod'=>$document->getUtime(),'priority'=>0.8,'changefreq'=>'weekly'))
*/    
$builder->commit();
