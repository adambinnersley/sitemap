<?php

namespace Sitemap\Tests;

use PHPUnit\Framework\TestCase;
use Sitemap\Sitemap;
use KubAT\PhpSimple\HtmlDomParser;

/**
 * Testable subclass that exposes protected methods
 */
class TestableSitemap extends Sitemap
{
    public function testIsValidScheme($linkInfo)
    {
        return $this->isValidScheme($linkInfo);
    }

    public function testNormalizePath($path)
    {
        return $this->normalizePath($path);
    }

    public function testCheckForIgnoredStrings($link)
    {
        return $this->checkForIgnoredStrings($link);
    }

    public function testBuildLink($linkInfo, $src)
    {
        return $this->buildLink($linkInfo, $src);
    }

    public function testLinkPath($linkInfo, $path)
    {
        return $this->linkPath($linkInfo, $path);
    }

    public function testAddLink($linkInfo, $link, $level = 1)
    {
        return $this->addLink($linkInfo, $link, $level);
    }

    public function testAddLinktoArray($linkInfo, $link, $level = 1)
    {
        return $this->addLinktoArray($linkInfo, $link, $level);
    }

    public function testGetLayoutFile($file)
    {
        return $this->getLayoutFile($file);
    }

    public function testUrlXML($url, $priority = '0.8', $freq = 'monthly', $modified = '', $additional = '')
    {
        $method = new \ReflectionMethod($this, 'urlXML');
        $method->setAccessible(true);
        return $method->invoke($this, $url, $priority, $freq, $modified, $additional);
    }

    public function testImageXML($images)
    {
        $method = new \ReflectionMethod($this, 'imageXML');
        $method->setAccessible(true);
        return $method->invoke($this, $images);
    }

    public function testVideoXML($videos)
    {
        $method = new \ReflectionMethod($this, 'videoXML');
        $method->setAccessible(true);
        return $method->invoke($this, $videos);
    }

    public function testGetImages()
    {
        return $this->getImages();
    }

    public function testGetVideos()
    {
        return $this->getVideos();
    }

    public function testGetAssets($tag = 'img', $global = 'images')
    {
        return $this->getAssets($tag, $global);
    }

    public function testGetLinks($level = 1)
    {
        return $this->getLinks($level);
    }

    public function testCopyXMLStyle()
    {
        return $this->copyXMLStyle();
    }

    public function testEscapeXml($string)
    {
        $method = new \ReflectionMethod($this, 'escapeXml');
        $method->setAccessible(true);
        return $method->invoke($this, $string);
    }

    public function testSanitizeFilename($filename)
    {
        $method = new \ReflectionMethod($this, 'sanitizeFilename');
        $method->setAccessible(true);
        return $method->invoke($this, $filename);
    }

    // Expose properties for testing
    public function setHost($host)
    {
        $this->host = $host;
    }

    public function setHtml($html)
    {
        $this->html = $html;
    }

    public function setMarkup($markup)
    {
        $this->markup = $markup;
    }

    public function getLinksArray()
    {
        return $this->links;
    }

    public function getPathsArray()
    {
        return $this->paths;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
}

class SitemapTest extends TestCase
{
    public ?Sitemap $sitemap = null;
    public ?TestableSitemap $testableSitemap = null;
    private string $testDir;

    protected function setUp(): void
    {
        $this->sitemap = new Sitemap();
        $this->testableSitemap = new TestableSitemap();
        $this->testDir = dirname(__FILE__);
    }

    protected function tearDown(): void
    {
        $this->sitemap = null;
        $this->testableSitemap = null;

        // Clean up any generated files
        $files = ['sitemap.xml', 'style.xsl'];
        foreach ($files as $file) {
            $path = $this->testDir . '/' . $file;
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setDomain
     * @covers Sitemap\Sitemap::getDomain
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testSetDomain()
    {
        $this->assertInstanceOf(Sitemap::class, $this->sitemap->setDomain('https://www.google.co.uk/'));
        $this->assertEquals('https://www.google.co.uk/', $this->sitemap->getDomain());
        $this->assertInstanceOf(Sitemap::class, $this->sitemap->setDomain('http://www.example.com/'));
        $this->assertEquals('http://www.example.com/', $this->sitemap->getDomain());
    }

    /**
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::getFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testSetFilePath()
    {
        $this->assertInstanceOf(Sitemap::class, $this->sitemap->setFilePath($this->testDir));
        $this->assertEquals($this->testDir, $this->sitemap->getFilePath());
        // Invalid path should not change the file path
        $this->assertInstanceOf(Sitemap::class, $this->sitemap->setFilePath(158774));
        $this->assertEquals($this->testDir, $this->sitemap->getFilePath());
        // Non-existent directory should not change the path
        $this->sitemap->setFilePath('/non/existent/path');
        $this->assertEquals($this->testDir, $this->sitemap->getFilePath());
    }

    /**
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     * @covers Sitemap\Sitemap::getXMLLayoutPath
     * @covers Sitemap\Sitemap::setFilePath
     */
    public function testSetXMLLayoutPath()
    {
        $sitemap = new Sitemap();
        // Default path should be set
        $this->assertNotNull($sitemap->getXMLLayoutPath());
        $this->assertStringContainsString('types', $sitemap->getXMLLayoutPath());

        // Test setting valid path
        $sitemap->setXMLLayoutPath($this->testDir);
        $this->assertEquals($this->testDir, $sitemap->getXMLLayoutPath());

        // Test setting invalid path (non-string)
        $originalPath = $sitemap->getXMLLayoutPath();
        $sitemap->setXMLLayoutPath(12345);
        $this->assertEquals($originalPath, $sitemap->getXMLLayoutPath());
    }

    /**
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::addURLItemstoIgnore
     * @covers Sitemap\Sitemap::getURLItemsToIgnore
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testAddURLItemsToIgnore()
    {
        // Test adding single string
        $this->sitemap->addURLItemstoIgnore('admin');
        $this->assertContains('admin', $this->sitemap->getURLItemsToIgnore());

        // Test adding array
        $this->sitemap->addURLItemstoIgnore(['login', 'logout']);
        $items = $this->sitemap->getURLItemsToIgnore();
        $this->assertContains('admin', $items);
        $this->assertContains('login', $items);
        $this->assertContains('logout', $items);

        // Test no duplicates
        $this->sitemap->addURLItemstoIgnore('admin');
        $this->assertEquals(3, count($this->sitemap->getURLItemsToIgnore()));

        // Test method chaining
        $result = $this->sitemap->addURLItemstoIgnore('test');
        $this->assertInstanceOf(Sitemap::class, $result);
    }

    /**
     * @covers Sitemap\Sitemap::checkForIgnoredStrings
     * @covers Sitemap\Sitemap::getURLItemsToIgnore
     * @covers Sitemap\Sitemap::addURLItemstoIgnore
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testCheckForIgnoredStrings()
    {
        $this->testableSitemap->addURLItemstoIgnore(['admin', 'login', 'private']);

        // Should return true for ignored strings
        $this->assertTrue($this->testableSitemap->testCheckForIgnoredStrings('/admin/dashboard'));
        $this->assertTrue($this->testableSitemap->testCheckForIgnoredStrings('/user/login'));
        $this->assertTrue($this->testableSitemap->testCheckForIgnoredStrings('/private/data'));

        // Should return false for non-ignored strings
        $this->assertFalse($this->testableSitemap->testCheckForIgnoredStrings('/public/page'));
        $this->assertFalse($this->testableSitemap->testCheckForIgnoredStrings('/about'));

        // Test with empty ignore list
        $sitemap = new TestableSitemap();
        $this->assertFalse($sitemap->testCheckForIgnoredStrings('/admin'));
    }

    /**
     * @covers Sitemap\Sitemap::isValidScheme
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testIsValidScheme()
    {
        // Valid schemes
        $this->assertTrue($this->testableSitemap->testIsValidScheme(['scheme' => 'http', 'path' => '/page']));
        $this->assertTrue($this->testableSitemap->testIsValidScheme(['scheme' => 'https', 'path' => '/page']));
        $this->assertTrue($this->testableSitemap->testIsValidScheme(['scheme' => 'HTTP', 'path' => '/page']));
        $this->assertTrue($this->testableSitemap->testIsValidScheme(['scheme' => 'HTTPS', 'path' => '/page']));

        // No scheme (relative URLs) should be valid
        $this->assertTrue($this->testableSitemap->testIsValidScheme(['path' => '/page']));

        // Invalid schemes should be rejected
        $this->assertFalse($this->testableSitemap->testIsValidScheme(['scheme' => 'tel', 'path' => '0800400777']));
        $this->assertFalse($this->testableSitemap->testIsValidScheme(['scheme' => 'mailto', 'path' => 'test@example.com']));
        $this->assertFalse($this->testableSitemap->testIsValidScheme(['scheme' => 'javascript', 'path' => 'void(0)']));
        $this->assertFalse($this->testableSitemap->testIsValidScheme(['scheme' => 'ftp', 'path' => '/files']));
        $this->assertFalse($this->testableSitemap->testIsValidScheme(['scheme' => 'data', 'path' => 'text/html']));
        $this->assertFalse($this->testableSitemap->testIsValidScheme(['scheme' => 'file', 'path' => '/etc/passwd']));
    }

    /**
     * @covers Sitemap\Sitemap::normalizePath
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testNormalizePath()
    {
        // Basic paths should remain unchanged
        $this->assertEquals('/page/', $this->testableSitemap->testNormalizePath('/page/'));
        $this->assertEquals('/path/to/page', $this->testableSitemap->testNormalizePath('/path/to/page'));

        // Paths with .. should be normalized
        $this->assertEquals('/ld-system/', $this->testableSitemap->testNormalizePath('/../ld-system/'));
        $this->assertEquals('/page/', $this->testableSitemap->testNormalizePath('/folder/../page/'));
        $this->assertEquals('/page', $this->testableSitemap->testNormalizePath('/folder/../page'));
        $this->assertEquals('/', $this->testableSitemap->testNormalizePath('/folder/..'));
        $this->assertEquals('/c/', $this->testableSitemap->testNormalizePath('/a/b/../../c/'));

        // Multiple .. sequences
        $this->assertEquals('/a/page/', $this->testableSitemap->testNormalizePath('/a/b/c/../../d/../page/'));

        // Paths with . should be normalized
        $this->assertEquals('/page/', $this->testableSitemap->testNormalizePath('/./page/'));
        $this->assertEquals('/path/page/', $this->testableSitemap->testNormalizePath('/path/./page/'));

        // Empty path should return /
        $this->assertEquals('/', $this->testableSitemap->testNormalizePath(''));
    }

    /**
     * @covers Sitemap\Sitemap::buildLink
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testBuildLink()
    {
        // Set up host info
        $this->testableSitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com'
        ]);

        // Test relative path (no scheme or host)
        $linkInfo = ['path' => '/images/logo.png'];
        $result = $this->testableSitemap->testBuildLink($linkInfo, '/images/logo.png');
        $this->assertEquals('https://www.example.com/images/logo.png', $result);

        // Test same host
        $linkInfo = ['scheme' => 'https', 'host' => 'www.example.com', 'path' => '/images/logo.png'];
        $result = $this->testableSitemap->testBuildLink($linkInfo, '/images/logo.png');
        $this->assertStringContainsString('/images/logo.png', $result);

        // Test different host (should return empty)
        $linkInfo = ['scheme' => 'https', 'host' => 'www.other.com', 'path' => '/images/logo.png'];
        $result = $this->testableSitemap->testBuildLink($linkInfo, '/images/logo.png');
        $this->assertEquals('', $result);
    }

    /**
     * @covers Sitemap\Sitemap::linkPath
     * @covers Sitemap\Sitemap::normalizePath
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testLinkPath()
    {
        // Set up host info
        $this->testableSitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com',
            'path' => '/current/page'
        ]);

        // Test relative path without leading slash
        $linkInfo = ['path' => 'subpage'];
        $result = $this->testableSitemap->testLinkPath($linkInfo, 'subpage');
        $this->assertEquals('https://www.example.com/subpage', $result);

        // Test absolute path
        $linkInfo = ['path' => '/about'];
        $result = $this->testableSitemap->testLinkPath($linkInfo, '/about');
        $this->assertEquals('https://www.example.com/about', $result);

        // Test with query only
        $linkInfo = ['query' => 'page=2'];
        $result = $this->testableSitemap->testLinkPath($linkInfo, '?page=2');
        $this->assertStringContainsString('page=2', $result);

        // Test path with .. sequences gets normalized
        $linkInfo = ['path' => '/../admin/'];
        $result = $this->testableSitemap->testLinkPath($linkInfo, '/../admin/');
        $this->assertEquals('https://www.example.com/admin/', $result);
    }

    /**
     * @covers Sitemap\Sitemap::addLink
     * @covers Sitemap\Sitemap::linkPath
     * @covers Sitemap\Sitemap::normalizePath
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testAddLink()
    {
        $this->testableSitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com'
        ]);
        $this->testableSitemap->setUrl('https://www.example.com/');

        // Add a link
        $linkInfo = ['path' => '/about'];
        $this->testableSitemap->testAddLink($linkInfo, '/about', 1);

        $links = $this->testableSitemap->getLinksArray();
        $this->assertArrayHasKey('https://www.example.com/about', $links);
        $this->assertEquals(1, $links['https://www.example.com/about']['level']);
        $this->assertEquals(0, $links['https://www.example.com/about']['visited']);

        // Add link with fragment (should be stripped)
        $linkInfo = ['path' => '/contact', 'fragment' => 'section1'];
        $this->testableSitemap->testAddLink($linkInfo, '/contact#section1', 2);

        $links = $this->testableSitemap->getLinksArray();
        $this->assertArrayHasKey('https://www.example.com/contact', $links);

        // Level capped at 5
        $linkInfo = ['path' => '/deep'];
        $this->testableSitemap->testAddLink($linkInfo, '/deep', 10);
        $links = $this->testableSitemap->getLinksArray();
        $this->assertEquals(5, $links['https://www.example.com/deep']['level']);
    }

    /**
     * @covers Sitemap\Sitemap::addLinktoArray
     * @covers Sitemap\Sitemap::isValidScheme
     * @covers Sitemap\Sitemap::addLink
     * @covers Sitemap\Sitemap::linkPath
     * @covers Sitemap\Sitemap::normalizePath
     * @covers Sitemap\Sitemap::checkForIgnoredStrings
     * @covers Sitemap\Sitemap::getURLItemsToIgnore
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testAddLinktoArray()
    {
        $this->testableSitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com'
        ]);
        $this->testableSitemap->setUrl('https://www.example.com/');

        // Valid link should be added
        $linkInfo = parse_url('/page');
        $this->testableSitemap->testAddLinktoArray($linkInfo, '/page', 1);
        $this->assertArrayHasKey('/page', $this->testableSitemap->getPathsArray());

        // Tel link should be rejected
        $linkInfo = parse_url('tel:0800400777');
        $this->testableSitemap->testAddLinktoArray($linkInfo, 'tel:0800400777', 1);
        $this->assertArrayNotHasKey('0800400777', $this->testableSitemap->getPathsArray());

        // Mailto link should be rejected
        $linkInfo = parse_url('mailto:test@example.com');
        $this->testableSitemap->testAddLinktoArray($linkInfo, 'mailto:test@example.com', 1);

        // JavaScript link should be rejected
        $linkInfo = parse_url('javascript:void(0)');
        $this->testableSitemap->testAddLinktoArray($linkInfo, 'javascript:void(0)', 1);

        // External link should be rejected
        $linkInfo = parse_url('https://www.google.com/external-page');
        $this->testableSitemap->testAddLinktoArray($linkInfo, 'https://www.google.com/external-page', 1);
        $this->assertArrayNotHasKey('/external-page', $this->testableSitemap->getPathsArray());

        // Image files should be tracked in paths but NOT added to links
        $linkInfo = parse_url('/images/logo.jpg');
        $this->testableSitemap->testAddLinktoArray($linkInfo, '/images/logo.jpg', 1);
        // Path is tracked to avoid duplicates
        $this->assertArrayHasKey('/images/logo.jpg', $this->testableSitemap->getPathsArray());
        // But not added to sitemap links
        $this->assertArrayNotHasKey('https://www.example.com/images/logo.jpg', $this->testableSitemap->getLinksArray());

        // PNG should be rejected from links
        $linkInfo = parse_url('/images/logo.png');
        $this->testableSitemap->testAddLinktoArray($linkInfo, '/images/logo.png', 1);
        $this->assertArrayNotHasKey('https://www.example.com/images/logo.png', $this->testableSitemap->getLinksArray());

        // GIF should be rejected from links
        $linkInfo = parse_url('/images/logo.gif');
        $this->testableSitemap->testAddLinktoArray($linkInfo, '/images/logo.gif', 1);
        $this->assertArrayNotHasKey('https://www.example.com/images/logo.gif', $this->testableSitemap->getLinksArray());
    }

    /**
     * @covers Sitemap\Sitemap::getLayoutFile
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     * @covers Sitemap\Sitemap::getXMLLayoutPath
     */
    public function testGetLayoutFile()
    {
        // Valid layout file should return content
        $content = $this->testableSitemap->testGetLayoutFile('urlXML');
        $this->assertIsString($content);
        $this->assertStringContainsString('url', $content);

        $content = $this->testableSitemap->testGetLayoutFile('sitemapXML');
        $this->assertIsString($content);
        $this->assertStringContainsString('urlset', $content);

        $content = $this->testableSitemap->testGetLayoutFile('imageXML');
        $this->assertIsString($content);
        $this->assertStringContainsString('image', $content);

        // Non-existent file should return false
        $content = $this->testableSitemap->testGetLayoutFile('nonexistent');
        $this->assertFalse($content);
    }

    /**
     * @covers Sitemap\Sitemap::urlXML
     * @covers Sitemap\Sitemap::getLayoutFile
     * @covers Sitemap\Sitemap::getXMLLayoutPath
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testUrlXML()
    {
        $result = $this->testableSitemap->testUrlXML(
            'https://www.example.com/',
            '1.0',
            'daily',
            '2024-01-01T00:00:00+00:00',
            ''
        );

        $this->assertStringContainsString('<loc>https://www.example.com/</loc>', $result);
        $this->assertStringContainsString('<priority>1.0</priority>', $result);
        $this->assertStringContainsString('<changefreq>daily</changefreq>', $result);
        $this->assertStringContainsString('2024-01-01', $result);
    }

    /**
     * @covers Sitemap\Sitemap::imageXML
     * @covers Sitemap\Sitemap::getLayoutFile
     * @covers Sitemap\Sitemap::getXMLLayoutPath
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testImageXML()
    {
        // Test with valid images
        $images = [
            ['src' => 'https://example.com/img1.jpg', 'alt' => 'Image 1'],
            ['src' => 'https://example.com/img2.png', 'alt' => 'Image 2']
        ];

        $result = $this->testableSitemap->testImageXML($images);
        $this->assertStringContainsString('img1.jpg', $result);
        $this->assertStringContainsString('img2.png', $result);
        $this->assertStringContainsString('Image 1', $result);
        $this->assertStringContainsString('Image 2', $result);

        // Test with empty array
        $result = $this->testableSitemap->testImageXML([]);
        $this->assertFalse($result);

        // Test with false
        $result = $this->testableSitemap->testImageXML(false);
        $this->assertFalse($result);

        // Test HTML entities are encoded
        $images = [['src' => 'https://example.com/img.jpg', 'alt' => 'Test & "quotes"']];
        $result = $this->testableSitemap->testImageXML($images);
        $this->assertStringContainsString('&amp;', $result);
    }

    /**
     * @covers Sitemap\Sitemap::videoXML
     * @covers Sitemap\Sitemap::getLayoutFile
     * @covers Sitemap\Sitemap::getXMLLayoutPath
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testVideoXML()
    {
        // Test with valid videos
        $videos = [
            [
                'thumbnail' => 'https://example.com/thumb1.jpg',
                'title' => 'Video 1',
                'description' => 'Description 1',
                'src' => 'https://example.com/video1.mp4'
            ]
        ];

        $result = $this->testableSitemap->testVideoXML($videos);
        $this->assertStringContainsString('thumb1.jpg', $result);
        $this->assertStringContainsString('Video 1', $result);
        $this->assertStringContainsString('video1.mp4', $result);

        // Test with empty array
        $result = $this->testableSitemap->testVideoXML([]);
        $this->assertFalse($result);

        // Test with false
        $result = $this->testableSitemap->testVideoXML(false);
        $this->assertFalse($result);
    }

    /**
     * @covers Sitemap\Sitemap::getAssets
     * @covers Sitemap\Sitemap::buildLink
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testGetAssets()
    {
        $this->testableSitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com'
        ]);

        // Test with HTML containing images
        $html = '<html><body><img src="/images/logo.png" alt="Logo"><img src="/images/photo.jpg" alt="Photo"></body></html>';
        $dom = HtmlDomParser::str_get_html($html);
        $this->testableSitemap->setHtml($dom);

        $result = $this->testableSitemap->testGetAssets('img', 'images');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertStringContainsString('logo.png', $result[0]['src']);
        $this->assertEquals('Logo', $result[0]['alt']);

        // Test with no images
        $html = '<html><body><p>No images here</p></body></html>';
        $dom = HtmlDomParser::str_get_html($html);
        $this->testableSitemap->setHtml($dom);
        $result = $this->testableSitemap->testGetAssets('img', 'images');
        $this->assertFalse($result);

        // Test with null html
        $this->testableSitemap->setHtml(null);
        $result = $this->testableSitemap->testGetAssets('img', 'images');
        $this->assertEmpty($result);
    }

    /**
     * @covers Sitemap\Sitemap::getImages
     * @covers Sitemap\Sitemap::getAssets
     * @covers Sitemap\Sitemap::buildLink
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testGetImages()
    {
        $this->testableSitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com'
        ]);

        $html = '<html><body><img src="/images/test.png" alt="Test"></body></html>';
        $dom = HtmlDomParser::str_get_html($html);
        $this->testableSitemap->setHtml($dom);

        $result = $this->testableSitemap->testGetImages();
        $this->assertIsArray($result);
        $this->assertStringContainsString('test.png', $result[0]['src']);
    }

    /**
     * @covers Sitemap\Sitemap::getVideos
     * @covers Sitemap\Sitemap::getAssets
     * @covers Sitemap\Sitemap::buildLink
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testGetVideos()
    {
        $this->testableSitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com'
        ]);

        $html = '<html><body><video src="/videos/test.mp4"></video></body></html>';
        $dom = HtmlDomParser::str_get_html($html);
        $this->testableSitemap->setHtml($dom);

        $result = $this->testableSitemap->testGetVideos();
        $this->assertIsArray($result);
        $this->assertStringContainsString('test.mp4', $result[0]['src']);
    }

    /**
     * @covers Sitemap\Sitemap::getLinks
     * @covers Sitemap\Sitemap::addLinktoArray
     * @covers Sitemap\Sitemap::isValidScheme
     * @covers Sitemap\Sitemap::addLink
     * @covers Sitemap\Sitemap::linkPath
     * @covers Sitemap\Sitemap::normalizePath
     * @covers Sitemap\Sitemap::checkForIgnoredStrings
     * @covers Sitemap\Sitemap::getURLItemsToIgnore
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testGetLinks()
    {
        $this->testableSitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com'
        ]);
        $this->testableSitemap->setUrl('https://www.example.com/');

        // Test with HTML containing links
        $html = '<html><body>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
            <a href="tel:123456">Phone</a>
            <a href="mailto:test@test.com">Email</a>
            <a href="javascript:void(0)">JS Link</a>
            <a href="/page" rel="nofollow">Nofollow</a>
        </body></html>';

        $dom = HtmlDomParser::str_get_html($html);
        $this->testableSitemap->setHtml($dom);
        $this->testableSitemap->setMarkup($html);

        $this->testableSitemap->testGetLinks(1);

        $paths = $this->testableSitemap->getPathsArray();

        // Valid links should be added
        $this->assertArrayHasKey('/about', $paths);
        $this->assertArrayHasKey('/contact', $paths);

        // Invalid links should not be added
        $this->assertArrayNotHasKey('123456', $paths);
        $this->assertArrayNotHasKey('test@test.com', $paths);
        $this->assertArrayNotHasKey('void(0)', $paths);

        // Nofollow links should not be added
        $this->assertArrayNotHasKey('/page', $paths);
    }

    /**
     * @covers Sitemap\Sitemap::copyXMLStyle
     * @covers Sitemap\Sitemap::getFilePath
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testCopyXMLStyle()
    {
        $this->testableSitemap->setFilePath($this->testDir . '/');

        $result = $this->testableSitemap->testCopyXMLStyle();
        $this->assertTrue($result);

        $stylePath = $this->testDir . '/style.xsl';
        $this->assertFileExists($stylePath);

        $content = file_get_contents($stylePath);
        $this->assertStringContainsString('xsl:stylesheet', $content);
    }

    /**
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     * @covers Sitemap\Sitemap::setDomain
     */
    public function testConstructorWithUri()
    {
        $sitemap = new Sitemap('https://www.example.com/');
        $this->assertEquals('https://www.example.com/', $sitemap->getDomain());
    }

    /**
     * @covers Sitemap\Sitemap::escapeXml
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testEscapeXml()
    {
        // Test basic escaping
        $this->assertEquals(
            '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
            $this->testableSitemap->testEscapeXml('<script>alert("xss")</script>')
        );

        // Test ampersand escaping
        $this->assertEquals(
            'foo &amp; bar',
            $this->testableSitemap->testEscapeXml('foo & bar')
        );

        // Test quote escaping
        $this->assertEquals(
            '&quot;quoted&quot; &amp; &apos;apostrophe&apos;',
            $this->testableSitemap->testEscapeXml('"quoted" & \'apostrophe\'')
        );

        // Test URL with special characters
        $this->assertEquals(
            'https://example.com/page?foo=1&amp;bar=2',
            $this->testableSitemap->testEscapeXml('https://example.com/page?foo=1&bar=2')
        );

        // Test normal string passes through
        $this->assertEquals(
            'https://example.com/normal-page',
            $this->testableSitemap->testEscapeXml('https://example.com/normal-page')
        );
    }

    /**
     * @covers Sitemap\Sitemap::sanitizeFilename
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testSanitizeFilename()
    {
        // Test path traversal attack prevention
        $this->assertEquals(
            'malicious',
            $this->testableSitemap->testSanitizeFilename('../../etc/malicious')
        );

        // Test another path traversal
        $this->assertEquals(
            'passwd',
            $this->testableSitemap->testSanitizeFilename('../../../etc/passwd')
        );

        // Test removal of special characters (keeps alphanumeric)
        $this->assertEquals(
            'sitemapscript',
            $this->testableSitemap->testSanitizeFilename('sitemap<script>')
        );

        // Test valid filename passes through
        $this->assertEquals(
            'my-sitemap_2024',
            $this->testableSitemap->testSanitizeFilename('my-sitemap_2024')
        );

        // Test empty string returns default
        $this->assertEquals(
            'sitemap',
            $this->testableSitemap->testSanitizeFilename('')
        );

        // Test only special characters returns default
        $this->assertEquals(
            'sitemap',
            $this->testableSitemap->testSanitizeFilename('!@#$%^&*()')
        );

        // Test Windows-style path traversal
        $this->assertEquals(
            'evil',
            $this->testableSitemap->testSanitizeFilename('..\\..\\windows\\evil')
        );
    }

    /**
     * @covers Sitemap\Sitemap::addLinktoArray
     * @covers Sitemap\Sitemap::isValidScheme
     * @covers Sitemap\Sitemap::addLink
     * @covers Sitemap\Sitemap::linkPath
     * @covers Sitemap\Sitemap::normalizePath
     * @covers Sitemap\Sitemap::checkForIgnoredStrings
     * @covers Sitemap\Sitemap::getURLItemsToIgnore
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     */
    public function testFileExtensionFiltering()
    {
        $sitemap = new TestableSitemap();
        $sitemap->setHost([
            'scheme' => 'https',
            'host' => 'www.example.com'
        ]);
        $sitemap->setUrl('https://www.example.com/');

        // Test that file with double extension is properly filtered
        // e.g., photo.name.jpg should be rejected (extension is jpg)
        $linkInfo = parse_url('/images/photo.name.jpg');
        $sitemap->testAddLinktoArray($linkInfo, '/images/photo.name.jpg', 1);
        $this->assertArrayNotHasKey(
            'https://www.example.com/images/photo.name.jpg',
            $sitemap->getLinksArray()
        );

        // Test SVG is also rejected
        $linkInfo = parse_url('/images/icon.svg');
        $sitemap->testAddLinktoArray($linkInfo, '/images/icon.svg', 1);
        $this->assertArrayNotHasKey(
            'https://www.example.com/images/icon.svg',
            $sitemap->getLinksArray()
        );

        // Test WebP is also rejected
        $linkInfo = parse_url('/images/photo.webp');
        $sitemap->testAddLinktoArray($linkInfo, '/images/photo.webp', 1);
        $this->assertArrayNotHasKey(
            'https://www.example.com/images/photo.webp',
            $sitemap->getLinksArray()
        );

        // Test valid page is accepted
        $linkInfo = parse_url('/about-us');
        $sitemap->testAddLinktoArray($linkInfo, '/about-us', 1);
        $this->assertArrayHasKey(
            'https://www.example.com/about-us',
            $sitemap->getLinksArray()
        );
    }

    /**
     * @covers Sitemap\Sitemap::__construct
     * @covers Sitemap\Sitemap::createSitemap
     * @covers Sitemap\Sitemap::getDomain
     * @covers Sitemap\Sitemap::setDomain
     * @covers Sitemap\Sitemap::getMarkup
     * @covers Sitemap\Sitemap::getImages
     * @covers Sitemap\Sitemap::getLinks
     * @covers Sitemap\Sitemap::addLinktoArray
     * @covers Sitemap\Sitemap::getAssets
     * @covers Sitemap\Sitemap::setFilePath
     * @covers Sitemap\Sitemap::buildLink
     * @covers Sitemap\Sitemap::addLink
     * @covers Sitemap\Sitemap::linkPath
     * @covers Sitemap\Sitemap::parseSite
     * @covers Sitemap\Sitemap::imageXML
     * @covers Sitemap\Sitemap::videoXML
     * @covers Sitemap\Sitemap::urlXML
     * @covers Sitemap\Sitemap::copyXMLStyle
     * @covers Sitemap\Sitemap::getFilePath
     * @covers Sitemap\Sitemap::getLayoutFile
     * @covers Sitemap\Sitemap::getXMLLayoutPath
     * @covers Sitemap\Sitemap::setXMLLayoutPath
     * @covers Sitemap\Sitemap::checkForIgnoredStrings
     * @covers Sitemap\Sitemap::getURLItemsToIgnore
     * @covers Sitemap\Sitemap::addURLItemstoIgnore
     * @covers Sitemap\Sitemap::isValidScheme
     * @covers Sitemap\Sitemap::normalizePath
     * @group integration
     * @group network
     */
    public function testCreateSitemap()
    {
        // Skip if no network available
        if (!@fsockopen('www.google.com', 443, $errno, $errstr, 5)) {
            $this->markTestSkipped('Network not available');
        }

        $this->sitemap->setDomain('https://www.netflix.com/')->setFilePath($this->testDir . '/');
        $this->assertTrue($this->sitemap->createSitemap(true, 1));
        $this->assertStringContainsString('<loc>https://www.netflix.com/</loc>', file_get_contents($this->testDir . '/sitemap.xml'));

        // Test with setting domain in constructor
        $newSitemap = new Sitemap('https://www.adambinnersley.co.uk/');
        $newSitemap->setFilePath($this->testDir . '/')->addURLItemstoIgnore('about-me');
        $this->assertTrue($newSitemap->createSitemap(false));
        $this->assertStringContainsString('<loc>https://www.adambinnersley.co.uk/</loc>', file_get_contents($this->testDir . '/sitemap.xml'));
        $this->assertStringNotContainsString('about-me', file_get_contents($this->testDir . '/sitemap.xml'));
    }
}
