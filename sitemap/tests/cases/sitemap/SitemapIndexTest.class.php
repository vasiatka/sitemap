<?php

class SitemapIndexTest extends UnitTestCase
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

    $url = 'http://sample.com/index.php';
    $lastmod = time();
    $date = date(DATE_RFC3339,$lastmod);

    $map->start();
    $map->addUrl(array('loc'=>$url,'lastmod'=>$lastmod));
    $map->commit();
    $this->assertEqual($map->getUrlsCount(), 1);
    $content = file_get_contents($map->getSitemapPath());
    $sample = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>$url</loc><lastmod>$date</lastmod></sitemap></sitemapindex>
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
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>
EOD;
    $this->assertEqual($content, $sample);
  } 
   
  function createSitemap()
  {
    $config = array('path' => VAR_DIR, 'tmp_dir'=>VAR_DIR,'base_url'=>'http://sample.org/');
    $map = new SitemapIndex($config);
    return $map;
  }


}
