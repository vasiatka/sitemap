<?php
class SitemapBaseChild extends SitemapBase
{
  protected $root_tag = 'h';

  function addUrl($url)
  {

  }

  function setSize($size)
  {
    $this->size = $size;
  }

  function setUrlsCount($count)
  {
    $this->urls_count = $count;
  }

}


class SitemapBaseTest extends UnitTestCase
{
  function setUp() {}

  function tearDown()
  {
    $files = glob(VAR_DIR.'/*');
    foreach($files as $file)
      unlink($file);
  }
 
 
  function testInitAndSetters()
  {
    $map = $this->createSitemap();

    $this->assertEqual($map->getSize(), 0);
    $this->assertEqual($map->getUrlsCount(), 0);
    $this->assertEqual($map->getPath(), VAR_DIR);
    $this->assertEqual($map->getSitemapPath(), VAR_DIR."/sitemap.xml");
  }

  function testWrap()
  {
    $map = $this->createSitemap();
    $this->assertEqual($map->wrap('somedata', 'some_tag'), '<some_tag>somedata</some_tag>');
  }

  function testIsAllowedAddingUrls()
  {
    $map = $this->createSitemap();
    $map->setUrlsCount(100);
    $this->assertTrue($map->isAllowedAddingUrls());
    $map->setUrlsCount(49999);
    $this->assertTrue($map->isAllowedAddingUrls());
    $map->setUrlsCount(50000);
    $this->assertFalse($map->isAllowedAddingUrls());
    $map->setUrlsCount(50001);
    $this->assertFalse($map->isAllowedAddingUrls());
  }

  function testIsAllowedAddingStrings()
  {
    $map = $this->createSitemap();
    $map->setSize(100);
    $this->assertTrue($map->isAllowedAddingString("string"));
    $map->setSize(1024*1024*10-20);
    $this->assertTrue($map->isAllowedAddingString("string"));
    $map->setSize(1024*1024*10-10);
    $this->assertTrue($map->isAllowedAddingString("string"));
    $map->setSize(1024*1024*10-9);
    $this->assertFalse($map->isAllowedAddingString("string"));
    $map->setSize(1024*1024*10);
    $this->assertFalse($map->isAllowedAddingString("string"));
  }
 
  function testStartCommit()
  {
    $map = $this->createSitemap();
    $map->start();
    $map->commit();
    $content = file_get_contents($map->getSitemapPath());
    $this->assertEqual($content, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL."<h xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"></h>");
  }

  function testGzipFileCreation()
  {
    $config = array('path' => VAR_DIR, 'tmp_dir'=>VAR_DIR,'base_url'=>'http://sample.org/','gzip'=>true);
    $map = new SitemapBaseChild($config);
    $map->start();
    $map->commit();
    $this->assertTrue(file_exists($map->getSitemapPath().".gz"));
  }

  function createSitemap()
  {
    $config = array('path' => VAR_DIR, 'tmp_dir'=>VAR_DIR,'base_url'=>'http://sample.org/');
    $map = new SitemapBaseChild($config);
    return $map;
  }


}
