<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
@isset($url['date'])
        <lastmod>{{ $url['date'] }}</lastmod>
@endisset
        <priority>{{ $url['priorite'] }}</priority>
    </url>
@endforeach
</urlset>
