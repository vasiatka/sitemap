<?php

class SitemapBuilderTest extends UnitTestCase
{
   function setUp() {}

  function tearDown()
  {
    $files = glob(VAR_DIR.'/*');
    foreach($files as $file)
      unlink($file);
  }
 
  function testAddOneUrl()
  {
    $builder = $this->createBuilder();

    $url = 'http://sample.org/index.php';
    $lastmod = time();
    $date = date(DATE_RFC3339,$lastmod);
    $priority = 1;
    $changefreq = 'weekly';
    $item = array('loc'=>$url,'lastmod'=>$lastmod,'priority'=>$priority,'changefreq'=>$changefreq);

    $builder->start();
    $builder->addUrl($item);
    $builder->commit();
    $content = file_get_contents(VAR_DIR."/sitemap.xml");
    $sample = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>$url</loc><lastmod>$date</lastmod><priority>1</priority><changefreq>weekly</changefreq></url></urlset>
EOD;
    $this->assertEqual($content, $sample);
  }
      
  function testStartCommit()
  {
    $builder = $this->createBuilder();
    $builder->start();
    $builder->commit();
    $content = file_get_contents(VAR_DIR."/sitemap.xml");
    $sample = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
EOD;
    $this->assertEqual($content, $sample);
  } 
  
  function testFilesCreation()
  {
    $time = time();
    $date = date(DATE_RFC3339,$time);
    $builder = $this->createBuilder();
    $sample_url = "http://sample.org/";
    $builder->start();
    for($i=0;$i<100001; $i++)
      $builder->addUrl(array('loc'=>$sample_url.$i,'lastmod'=>$time,'priority'=>0.8,'changefreq'=>'weekly'));
    $builder->commit();
    $path = VAR_DIR."/sitemap.xml";
    $path0 = VAR_DIR."/sitemap0.xml";
    $path1 = VAR_DIR."/sitemap1.xml";
    $path2 = VAR_DIR."/sitemap2.xml";

    $this->assertTrue(file_exists($path));
    $this->assertTrue(file_exists($path0));
    $this->assertTrue(file_exists($path1));
    $this->assertTrue(file_exists($path2));
    
    $url0 = 'http://sample.org/sitemap0.xml';
    $url1 = 'http://sample.org/sitemap1.xml';
    $url2 = 'http://sample.org/sitemap2.xml';
   
    $creation_time = date(DATE_RFC3339,$builder->getCreationTime());
    $content = file_get_contents($path);
    $sample = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>$url0</loc><lastmod>$creation_time</lastmod></sitemap><sitemap><loc>$url1</loc><lastmod>$creation_time</lastmod></sitemap><sitemap><loc>$url2</loc><lastmod>$creation_time</lastmod></sitemap></sitemapindex>
EOD;
    $this->assertEqual($content, $sample);
  
    $content = file_get_contents($path2);
    $sample = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>http://sample.org/100000</loc><lastmod>$date</lastmod><priority>0.8</priority><changefreq>weekly</changefreq></url></urlset>
EOD;
    $this->assertEqual($content, $sample);
  }

  function testBigFilesCreation()
  {
    $time = time();
    $date = date(DATE_RFC3339,$time);
    $builder = $this->createBuilder(array('sitemapindex_creation_time'=>$time));
    $sample_url = "http://sample.org/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/big/";
    $builder->start();
    for($i=0;$i<20000; $i++)
      $builder->addUrl(array('loc'=>$sample_url.$i,'lastmod'=>$time,'priority'=>0.8,'changefreq'=>'weekly'));
    $builder->commit();
    $path0 = VAR_DIR."/sitemap0.xml";
    $path1 = VAR_DIR."/sitemap1.xml";
    $path2 = VAR_DIR."/sitemap2.xml";
    $this->assertTrue(filesize($path0)<=10*1024*1024);
    $this->assertTrue(filesize($path1)<=10*1024*1024);
    $this->assertTrue(filesize($path2)<=10*1024*1024);
    
  }
 

  function createBuilder()
  {
    $config = array('path' => VAR_DIR, 'tmp_dir'=>VAR_DIR,'base_url'=>'http://sample.org/');
    $map = new SitemapBuilder($config);
    return $map;
  }


}
