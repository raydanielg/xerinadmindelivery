<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($urls as $item)
    <url>
        <loc>{{ htmlspecialchars($item['url'], ENT_XML1) }}</loc>
        <changefreq>{{ $item['changefreq'] }}</changefreq>
        <priority>{{ $item['priority'] }}</priority>
        @if(!empty($item['lastmod']))
        <lastmod>{{ $item['lastmod'] }}</lastmod>
        @endif
    </url>
@endforeach
</urlset>
