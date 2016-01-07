<?php

class SitemapTest extends UnitTestCase
{
  function setUp() {}

  function tearDown()
  {
    $files = glob(VAR_DIR.'/*');
    foreach($files as $file)
      unlink($file);
  }

  function testAddUrl()
  {
    $map = $this->createSitemap();

    $url = 'http://sample.org/index.php';
    $lastmod = time();
    $date = date(DATE_RFC3339,$lastmod);
    $priority = 1;
    $changefreq = 'weekly';

    $map->start();
    $map->addUrl(array('loc'=>$url,'lastmod' => $lastmod,'priority'=>$priority,'changefreq'=>$changefreq));
    $map->commit();
    $this->assertEqual($map->getUrlsCount(), 1);
    $content = file_get_contents($map->getSitemapPath());
    $sample = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>$url</loc><lastmod>$date</lastmod><priority>1</priority><changefreq>weekly</changefreq></url></urlset>
EOD;
    $this->assertEqual($content, $sample);
  }
      
  function testStartCommit()
  {
    $map = $this->createSitemap();
    $map->start();
    $map->commit();
    $content = file_get_contents($map->getSitemapPath());
    $sample = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
EOD;
    $this->assertEqual($content, $sample);
  } 
   
  function createSitemap()
  {
    $config = array('path' => VAR_DIR, 'tmp_dir'=>VAR_DIR,'base_url'=>'http://sample.org/');
    $map = new Sitemap($config);
    return $map;
  }


}
