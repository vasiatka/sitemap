<?php
class Sitemap extends SitemapBase
{
  protected $root_tag = 'urlset';
  protected $item_tag = 'url';
  protected $optional_item_parts = array('lastmod', 'changefreq','priority');
}
